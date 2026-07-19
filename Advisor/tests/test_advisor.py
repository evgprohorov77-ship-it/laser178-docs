"""Unit tests for Advisor sanitization and fallback behavior.

Run with:
    python -m pytest Advisor/tests/ -v

These tests do NOT require a real OPENAI_API_KEY.
"""

import json
import os
from unittest.mock import MagicMock

import pytest

from Advisor.advisor import (
    Advisor,
    AdvisorConfig,
    AdvisorSanitizer,
    ReviewType,
    RiskLevel,
    SanitizationError,
)


# ---------------------------------------------------------------------------
# Sanitization tests
# ---------------------------------------------------------------------------

def test_sanitize_api_key():
    dirty = "api_key = sk-proj-123456789012345678901234567890"
    clean = AdvisorSanitizer.sanitize(dirty)
    assert "sk-proj-" not in clean
    assert "[REDACTED" in clean


def test_sanitize_email_and_phone():
    dirty = "Contact Evgprohorov77@gmail.com or call +7 999 123-45-67."
    clean = AdvisorSanitizer.sanitize(dirty)
    assert "Evgprohorov77@gmail.com" not in clean
    assert "+7 999 123-45-67" not in clean


def test_sanitize_env_file():
    dirty = "Environment content:\n.env local:\nDB_PASSWORD=secret123"
    clean = AdvisorSanitizer.sanitize(dirty)
    assert "DB_PASSWORD" not in clean or "secret123" not in clean


def test_sanitize_url_with_secret():
    dirty = "Webhook URL: https://example.com/callback?token=abc123secret"
    clean = AdvisorSanitizer.sanitize(dirty)
    assert "token=abc123secret" not in clean
    assert "https://example.com/callback" in clean


def test_sanitize_truncate():
    dirty = "word " * 1000
    clean = AdvisorSanitizer.sanitize(dirty, max_chars=100)
    assert len(clean) <= 120
    assert "[TRUNCATED]" in clean


def test_validate_safety_detects_password():
    dirty = "password = supersecret"
    with pytest.raises(SanitizationError):
        AdvisorSanitizer.validate_safety(dirty)


def test_validate_safety_detects_secret_prefix():
    dirty = "ghp_123456789012345678901234567890123456"
    with pytest.raises(SanitizationError):
        AdvisorSanitizer.validate_safety(dirty)


# ---------------------------------------------------------------------------
# Advisor fallback tests (no real API calls)
# ---------------------------------------------------------------------------

@pytest.fixture
def config(tmp_path):
    cfg_path = tmp_path / "config.yaml"
    cfg_path.write_text(
        "advisor:\n"
        "  enabled: true\n"
        "  model: gpt-4.1-nano\n"
        "  max_context_chars: 1000\n"
        "  max_output_tokens: 512\n"
        "  daily_request_limit: 5\n"
        "  timeout: 10\n"
    )
    return AdvisorConfig(str(cfg_path))


def test_advisor_disabled_returns_fallback():
    cfg = AdvisorConfig()
    assert cfg.enabled is False
    advisor = Advisor(cfg)
    result = advisor.consult(
        task_id="task-001",
        review_type="ux_review",
        question="Should we change the homepage?",
        proposed_solution="Use a new hero section.",
        sanitized_context="Current homepage has a dark theme.",
    )
    assert result["verdict"] == "advisor_unavailable"
    assert result["requires_owner_decision"] is False


def test_unknown_review_type(config):
    advisor = Advisor(config)
    result = advisor.consult(
        task_id="task-002",
        review_type="not_a_review",
        question="?",
        proposed_solution="?",
        sanitized_context="?",
    )
    assert result["verdict"] == "advisor_unavailable"
    assert "unknown_review_type" in result["issues"][0]


def test_daily_limit(config):
    advisor = Advisor(config)
    # Exhaust the daily limit with unknown review types (no API calls).
    for _ in range(5):
        advisor.consult(
            task_id="task-limit",
            review_type="not_a_review",
            question="?",
            proposed_solution="?",
            sanitized_context="?",
        )
    result = advisor.consult(
        task_id="task-limit-6",
        review_type="ux_review",
        question="?",
        proposed_solution="?",
        sanitized_context="?",
    )
    assert result["verdict"] == "advisor_unavailable"
    assert result["issues"][0] == "daily_limit_exceeded"


def test_mocked_successful_consult(config):
    """Advisor with a mocked OpenAI client returns a valid review."""
    advisor = Advisor(config)

    mock_client = MagicMock()
    mock_response = MagicMock()
    mock_response.output_text = json.dumps({
        "verdict": "revise",
        "summary": "Good idea but improve mobile layout.",
        "strengths": ["Clear value proposition"],
        "issues": ["CTA too small on mobile"],
        "recommendations": ["Increase tap target size"],
        "risks": ["Lower mobile conversion"],
        "confidence": 0.82,
        "requires_owner_decision": False,
    })
    mock_response.usage.input_tokens = 120
    mock_response.usage.output_tokens = 80
    mock_client.responses.create.return_value = mock_response

    advisor._client = mock_client

    result = advisor.consult(
        task_id="task-003",
        review_type="ux_review",
        question="Is the new mobile bottom bar good?",
        proposed_solution="Add fixed bottom bar with call, Telegram, MAX buttons.",
        sanitized_context="Site has dark theme. Bottom bar is fixed at z-index 9999.",
        risk_level="MEDIUM",
    )

    assert result["verdict"] == "revise"
    assert result["confidence"] == pytest.approx(0.82)
    assert result["requires_owner_decision"] is False
    mock_client.responses.create.assert_called_once()


def test_mocked_invalid_json(config):
    advisor = Advisor(config)
    mock_client = MagicMock()
    mock_response = MagicMock()
    mock_response.output_text = "This is not JSON."
    mock_client.responses.create.return_value = mock_response
    advisor._client = mock_client

    result = advisor.consult(
        task_id="task-004",
        review_type="content_review",
        question="?",
        proposed_solution="?",
        sanitized_context="?",
    )
    assert result["verdict"] == "advisor_unavailable"
    assert result["issues"][0] == "advisor_invalid_json"


def test_mocked_authentication_error(config):
    advisor = Advisor(config)
    mock_client = MagicMock()
    from openai import AuthenticationError
    mock_client.responses.create.side_effect = AuthenticationError(
        "Invalid API key",
        response=MagicMock(),
        body=None,
    )
    advisor._client = mock_client

    result = advisor.consult(
        task_id="task-005",
        review_type="seo_review",
        question="?",
        proposed_solution="?",
        sanitized_context="?",
    )
    assert result["verdict"] == "advisor_unavailable"
    assert result["issues"][0] == "advisor_authentication_error"


def test_high_risk_fallback_requires_owner_decision(config):
    advisor = Advisor(config)
    mock_client = MagicMock()
    from openai import APIConnectionError
    mock_client.responses.create.side_effect = APIConnectionError(
        message="Timeout",
        request=MagicMock(),
    )
    advisor._client = mock_client

    result = advisor.consult(
        task_id="task-006",
        review_type="code_review",
        question="?",
        proposed_solution="?",
        sanitized_context="?",
        risk_level="HIGH",
    )
    assert result["verdict"] == "advisor_unavailable"
    assert result["requires_owner_decision"] is True


def test_should_consult_enabled(config):
    advisor = Advisor(config)
    assert advisor.should_consult("homepage", RiskLevel.LOW, 0.9) is True
    assert advisor.should_consult("seo_structure", RiskLevel.LOW, 0.9) is True
    assert advisor.should_consult("backup", RiskLevel.LOW, 0.9) is False
    assert advisor.should_consult("backup", RiskLevel.HIGH, 0.9) is True
    assert advisor.should_consult("backup", RiskLevel.LOW, 0.7) is True


def test_should_consult_disabled():
    advisor = Advisor(AdvisorConfig())
    assert advisor.should_consult("homepage", RiskLevel.HIGH, 0.7) is False


if __name__ == "__main__":
    pytest.main([__file__, "-v"])
