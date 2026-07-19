"""
External Advisor Mode v1 for Hermes Agent.

Provides a safe, read-only consultation layer with an external LLM (OpenAI)
for reviewing SEO, UX, content, code, and decisions before Hermes takes
Production Actions.

Security rules:
- OpenAI API key is read ONLY from the OPENAI_API_KEY environment variable.
- No secrets are ever persisted in code, YAML, .env, or logs.
- Advisor has no access to tools, servers, files, or execution.
- All outgoing context is sanitized to remove credentials, PII, tokens, etc.
- Full private context is never written to logs.
"""

from __future__ import annotations

import json
import logging
import os
import re
import time
from dataclasses import dataclass, field
from enum import Enum
from pathlib import Path
from typing import Any

import jsonschema
import yaml
from openai import APIConnectionError, APIError, AuthenticationError, OpenAI, RateLimitError


logger = logging.getLogger(__name__)

DEFAULT_MODEL = "gpt-4.1-nano"
DEFAULT_TIMEOUT = 30


class ReviewType(str, Enum):
    SEO = "seo_review"
    UX = "ux_review"
    CONTENT = "content_review"
    CODE = "code_review"
    DECISION = "decision_review"


class Verdict(str, Enum):
    APPROVE = "approve"
    REVISE = "revise"
    REJECT = "reject"
    INSUFFICIENT_CONTEXT = "insufficient_context"


class RiskLevel(str, Enum):
    LOW = "LOW"
    MEDIUM = "MEDIUM"
    HIGH = "HIGH"


@dataclass
class UsageRecord:
    task_id: str
    review_type: str
    model: str
    requested_at: float
    verdict: str
    confidence: float
    tokens_input: int | None = None
    tokens_output: int | None = None
    cost_usd: float | None = None
    error: str | None = None


class SanitizationError(Exception):
    """Raised when the provided context cannot be safely sanitized."""


class AdvisorConfig:
    """Lightweight configuration loader for the Advisor."""

    def __init__(self, config_path: str | None = None):
        self.enabled = False
        self.model = DEFAULT_MODEL
        self.max_context_chars = 8000
        self.max_output_tokens = 2048
        self.daily_request_limit = 50
        self.timeout = DEFAULT_TIMEOUT
        self.prompts_dir = Path(__file__).parent / "prompts"
        self.schema_path = Path(__file__).parent / "schemas" / "advisor_response.schema.json"
        self._load(config_path)

    def _load(self, config_path: str | None) -> None:
        if config_path and Path(config_path).exists():
            with open(config_path, "r", encoding="utf-8") as f:
                data = yaml.safe_load(f) or {}
        else:
            data = {}

        advisor_cfg = data.get("advisor", {})
        self.enabled = bool(advisor_cfg.get("enabled", False))
        self.model = advisor_cfg.get("model", DEFAULT_MODEL)
        self.max_context_chars = int(advisor_cfg.get("max_context_chars", 8000))
        self.max_output_tokens = int(advisor_cfg.get("max_output_tokens", 2048))
        self.daily_request_limit = int(advisor_cfg.get("daily_request_limit", 50))
        self.timeout = int(advisor_cfg.get("timeout", DEFAULT_TIMEOUT))
        if "prompts_dir" in advisor_cfg:
            self.prompts_dir = Path(advisor_cfg["prompts_dir"])


class AdvisorSanitizer:
    """Removes sensitive data from context before sending to external LLM."""

    # Regex patterns for common secrets and PII.
    PATTERNS = {
        "api_key": re.compile(r"(?i)(api[_-]?key|apikey)\s*[:=]\s*[\"']?[\w\-]{16,}[\"']?"),
        "token": re.compile(r"(?i)(token|access_token|refresh_token|bearer)\s*[:=]\s*[\"']?[\w\-]{16,}[\"']?"),
        "password": re.compile(r"(?i)(password|pass|passwd|pwd|secret)\s*[:=]\s*[\"']?[^\s\"']+[\"']?"),
        "cookie": re.compile(r"(?i)(cookie|session|set-cookie)\s*[:=]\s*[\"']?[^\n\"']+[\"']?"),
        "email": re.compile(r"[\w.+-]+@[\w-]+\.[\w.-]+"),
        "phone": re.compile(r"(?:\+7|8)[\s\-]?\(?\d{3}\)?[\s\-]?\d{3}[\s\-]?\d{2}[\s\-]?\d{2}"),
        "env_file": re.compile(r"(?i)(\.env|env\.local|\.env\.\w+)\s*content:?\s*\n?[\s\S]{0,500}"),
        "url_with_secret": re.compile(r"https?://[^\s]+\?(?:token|key|api_key|secret|auth)=[^\s&]+"),
    }

    @classmethod
    def sanitize(cls, text: str, max_chars: int | None = None) -> str:
        """Sanitize text and optionally truncate to max_chars."""
        if not isinstance(text, str):
            text = str(text)

        for name, pattern in cls.PATTERNS.items():
            if name == "env_file":
                text = pattern.sub("[REDACTED_ENV_FILE]", text)
            elif name == "url_with_secret":
                text = pattern.sub(lambda m: re.sub(r"\?(token|key|api_key|secret|auth)=[^\s&]+", "?[REDACTED]", m.group(0)), text)
            else:
                text = pattern.sub(f"[REDACTED_{name.upper()}]", text)

        # Secondary pass for JSON-like secret strings not caught above.
        text = re.sub(r"(?i)['\"]?(sk|sk-proj|sk-live|sk-test|pk|ghp|gho|ghu|ghs|ghr|glpat|xai-|dalle-|ft-|worfklow-)[\w\-]{20,}['\"]?", "[REDACTED_SECRET]", text)

        if max_chars and len(text) > max_chars:
            text = text[:max_chars] + "\n[TRUNCATED]"

        return text

    @classmethod
    def validate_safety(cls, text: str) -> None:
        """Raise SanitizationError if the text still looks unsafe."""
        if cls.PATTERNS["password"].search(text):
            raise SanitizationError("Context still contains password-like material after sanitization.")
        if cls.PATTERNS["api_key"].search(text):
            raise SanitizationError("Context still contains API-key-like material after sanitization.")
        # Basic checks for common secret prefixes.
        if re.search(r"(?i)(sk-proj|sk-live|ghp_|glpat-|xai-|AKIA|BEGIN\s+RSA|BEGIN\s+OPENSSH)", text):
            raise SanitizationError("Context still contains high-entropy secret material.")


class AdvisorUsageLog:
    """In-memory daily usage tracker. Not persisted across process restarts."""

    def __init__(self, limit: int = 50):
        self.limit = limit
        self.records: list[UsageRecord] = []
        self._date = time.strftime("%Y-%m-%d")

    def can_request(self) -> bool:
        today = time.strftime("%Y-%m-%d")
        if today != self._date:
            self._date = today
            self.records.clear()
        return len(self.records) < self.limit

    def add(self, record: UsageRecord) -> None:
        self.records.append(record)

    def today_count(self) -> int:
        if time.strftime("%Y-%m-%d") != self._date:
            return 0
        return len(self.records)


class Advisor:
    """External LLM consultant. Read-only, no execution, no secrets."""

    def __init__(self, config: AdvisorConfig | None = None, client: OpenAI | None = None):
        self.config = config or AdvisorConfig()
        self._client = client
        self._usage = AdvisorUsageLog(limit=self.config.daily_request_limit)
        self._schema = self._load_schema()

    @property
    def _openai(self) -> OpenAI:
        if self._client is not None:
            return self._client
        api_key = os.environ.get("OPENAI_API_KEY")
        if not api_key:
            raise RuntimeError("OPENAI_API_KEY is not set in the environment.")
        self._client = OpenAI(api_key=api_key, timeout=self.config.timeout)
        return self._client

    def _load_schema(self) -> dict[str, Any]:
        path = self.config.schema_path
        if not path.exists():
            raise FileNotFoundError(f"Advisor response schema not found: {path}")
        with open(path, "r", encoding="utf-8") as f:
            return json.load(f)

    def _load_prompt(self, review_type: ReviewType) -> str:
        path = self.config.prompts_dir / f"{review_type.value}.md"
        if not path.exists():
            raise FileNotFoundError(f"Prompt not found: {path}")
        with open(path, "r", encoding="utf-8") as f:
            return f.read()

    def _build_input(
        self,
        task_id: str,
        review_type: ReviewType,
        question: str,
        proposed_solution: str,
        sanitized_context: str,
        risk_level: RiskLevel,
    ) -> str:
        context = AdvisorSanitizer.sanitize(sanitized_context, max_chars=self.config.max_context_chars)
        AdvisorSanitizer.validate_safety(context)

        return (
            f"Task ID: {task_id}\n"
            f"Review type: {review_type.value}\n"
            f"Risk level: {risk_level.value}\n\n"
            f"Question:\n{question}\n\n"
            f"Proposed solution:\n{proposed_solution}\n\n"
            f"Sanitized context:\n{context}\n\n"
            "Respond ONLY with the JSON object described in the system prompt."
        )

    def consult(
        self,
        task_id: str,
        review_type: str,
        question: str,
        proposed_solution: str,
        sanitized_context: str,
        risk_level: str = "MEDIUM",
    ) -> dict[str, Any]:
        """Request a review from the external advisor."""
        if not self.config.enabled:
            return self._fallback(
                task_id=task_id,
                review_type=review_type,
                error="advisor_disabled",
            )

        if not self._usage.can_request():
            return self._fallback(
                task_id=task_id,
                review_type=review_type,
                error="daily_limit_exceeded",
            )

        try:
            review_enum = ReviewType(review_type)
        except ValueError:
            return self._fallback(
                task_id=task_id,
                review_type=review_type,
                error=f"unknown_review_type: {review_type}",
            )

        try:
            risk_enum = RiskLevel(risk_level.upper())
        except ValueError:
            risk_enum = RiskLevel.MEDIUM

        instructions = self._load_prompt(review_enum)
        user_input = self._build_input(
            task_id=task_id,
            review_type=review_enum,
            question=question,
            proposed_solution=proposed_solution,
            sanitized_context=sanitized_context,
            risk_level=risk_enum,
        )

        requested_at = time.time()
        try:
            response = self._openai.responses.create(
                model=self.config.model,
                instructions=instructions,
                input=user_input,
                max_output_tokens=self.config.max_output_tokens,
            )
        except AuthenticationError as exc:
            logger.error("Advisor authentication failed: %s", exc)
            return self._fallback(task_id, review_type, "advisor_authentication_error", risk_enum)
        except RateLimitError as exc:
            logger.warning("Advisor rate limit: %s", exc)
            return self._fallback(task_id, review_type, "advisor_rate_limited", risk_enum)
        except APIConnectionError as exc:
            logger.warning("Advisor connection error: %s", exc)
            return self._fallback(task_id, review_type, "advisor_connection_error", risk_enum)
        except APIError as exc:
            logger.warning("Advisor API error: %s", exc)
            return self._fallback(task_id, review_type, "advisor_api_error", risk_enum)

        raw_text = response.output_text.strip()
        parsed = self._parse_json(raw_text)

        if parsed is None:
            return self._fallback(task_id, review_type, "advisor_invalid_json", risk_enum)

        try:
            jsonschema.validate(parsed, self._schema)
        except jsonschema.ValidationError as exc:
            logger.warning("Advisor response schema validation failed: %s", exc)
            return self._fallback(task_id, review_type, "advisor_schema_validation_failed", risk_enum)

        verdict = parsed.get("verdict", "insufficient_context")
        confidence = float(parsed.get("confidence", 0.0))

        record = UsageRecord(
            task_id=task_id,
            review_type=review_type,
            model=self.config.model,
            requested_at=requested_at,
            verdict=verdict,
            confidence=confidence,
            tokens_input=response.usage.input_tokens if response.usage else None,
            tokens_output=response.usage.output_tokens if response.usage else None,
        )
        self._usage.add(record)
        self._log_record(record)

        return parsed

    def _parse_json(self, text: str) -> dict[str, Any] | None:
        """Extract JSON from the model response, tolerating markdown fences."""
        # Try direct JSON first.
        try:
            return json.loads(text)
        except json.JSONDecodeError:
            pass

        # Try fenced code block.
        match = re.search(r"```(?:json)?\s*([\s\S]*?)\s*```", text)
        if match:
            try:
                return json.loads(match.group(1))
            except json.JSONDecodeError:
                pass

        return None

    def _fallback(
        self,
        task_id: str,
        review_type: str,
        error: str,
        risk_level: RiskLevel = RiskLevel.MEDIUM,
    ) -> dict[str, Any]:
        """Return a safe fallback when the advisor cannot provide a review."""
        record = UsageRecord(
            task_id=task_id,
            review_type=review_type,
            model=self.config.model,
            requested_at=time.time(),
            verdict="advisor_unavailable",
            confidence=0.0,
            error=error,
        )
        self._usage.add(record)
        self._log_record(record)

        return {
            "verdict": "advisor_unavailable",
            "summary": f"Advisor unavailable: {error}. Hermes must proceed according to Policy Layer.",
            "strengths": [],
            "issues": [error],
            "recommendations": ["Request owner decision if risk is HIGH."],
            "risks": ["No external review was obtained."],
            "confidence": 0.0,
            "requires_owner_decision": risk_level == RiskLevel.HIGH,
        }

    def _log_record(self, record: UsageRecord) -> None:
        """Log only non-sensitive metadata."""
        logger.info(
            "Advisor usage: task_id=%s review_type=%s model=%s verdict=%s confidence=%.2f "
            "tokens_input=%s tokens_output=%s error=%s",
            record.task_id,
            record.review_type,
            record.model,
            record.verdict,
            record.confidence,
            record.tokens_input,
            record.tokens_output,
            record.error,
        )

    def should_consult(self, change_category: str, risk_level: RiskLevel, hermes_confidence: float) -> bool:
        """Determine whether an advisor review is recommended."""
        if not self.config.enabled:
            return False
        if risk_level == RiskLevel.HIGH:
            return True
        if hermes_confidence < 0.75:
            return True
        if change_category in {
            "homepage",
            "seo_structure",
            "public_text",
            "ux_design",
            "wordpress_plugin",
        }:
            return True
        return False


if __name__ == "__main__":
    # Minimal CLI example for local dry runs. Does NOT require a real API key.
    logging.basicConfig(level=logging.INFO)
    advisor = Advisor()
    print(json.dumps(advisor.config.__dict__, indent=2, default=str))
