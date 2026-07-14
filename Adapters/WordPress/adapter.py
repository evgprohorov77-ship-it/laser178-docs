"""WordPress Adapter v1 for LAOS.

Safe, auditable operations via WordPress REST API.

Allowed: GET, UPDATE (content, title, meta, excerpt, featured image),
         CREATE draft, PUBLISH approved draft, FAQ/internal links updates.
Forbidden: DELETE, plugins, themes, menus, users, roles, robots.txt, PHP,
           wp-config, site options, settings.

Every UPDATE:
1. Backup
2. Dry Run
3. Verification
4. Clear Cache
5. HTML Check
6. HTTP Check
7. SEO Check
8. Rollback on failure

All operations are logged to Audit/WordPress/operations.log.
"""

from __future__ import annotations

__version__ = "1.0.0"

import json
import os
import re
import time
import uuid
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import requests
import yaml


class WordPressAdapterError(Exception):
    """Base exception for adapter errors."""


class ForbiddenOperationError(WordPressAdapterError):
    """Raised when an operation is not in the allowed list."""


class ProductionLockError(WordPressAdapterError):
    """Raised when production lock is active and approval is missing."""


class WordPressAdapter:
    """LAOS WordPress Adapter v1."""

    ALLOWED_UPDATE_FIELDS = {
        "content",
        "title",
        "excerpt",
        "featured_media",
        "meta",
    }

    def __init__(self, config_path: str | None = None):
        self.project_root = Path(__file__).resolve().parent.parent.parent
        self.config_path = Path(config_path) if config_path else self.project_root / "Adapters" / "WordPress" / "config.yaml"
        self.config = self._load_config()
        self._validate_config()
        self._session = requests.Session()
        self._session.headers.update({
            "User-Agent": self.config["wordpress"]["user_agent"],
            "Accept": "application/json",
        })
        self._auth = self._load_credentials()
        self._allowed_ops = self._load_allowed_operations()
        self._denied_ops = self._load_denied_operations()

    # ------------------------------------------------------------------
    # Config / Credentials
    # ------------------------------------------------------------------

    def _load_config(self) -> dict[str, Any]:
        if not self.config_path.exists():
            raise WordPressAdapterError(f"Config not found: {self.config_path}")
        with open(self.config_path, "r", encoding="utf-8") as f:
            return yaml.safe_load(f)

    def _validate_config(self) -> None:
        wp = self.config.get("wordpress", {})
        if not wp.get("base_url"):
            raise WordPressAdapterError("wordpress.base_url is required")
        if not wp.get("api_namespace"):
            raise WordPressAdapterError("wordpress.api_namespace is required")

    def _load_credentials(self) -> tuple[str, str]:
        username = os.environ.get("WP_USERNAME")
        password = os.environ.get("WP_APP_PASSWORD")
        if not username or not password:
            raise WordPressAdapterError(
                "WP_USERNAME and WP_APP_PASSWORD environment variables are required"
            )
        return username, password

    def _load_allowed_operations(self) -> set[str]:
        perms_path = self.project_root / "Security" / "WordPress" / "permissions.yaml"
        with open(perms_path, "r", encoding="utf-8") as f:
            perms = yaml.safe_load(f)
        return {op["name"] for op in perms.get("allowed_operations", [])}

    def _load_denied_operations(self) -> set[str]:
        perms_path = self.project_root / "Security" / "WordPress" / "permissions.yaml"
        with open(perms_path, "r", encoding="utf-8") as f:
            perms = yaml.safe_load(f)
        return set(perms.get("denied_operations", []))

    # ------------------------------------------------------------------
    # Helpers
    # ------------------------------------------------------------------

    def _api_url(self, endpoint: str) -> str:
        base = self.config["wordpress"]["base_url"].rstrip("/")
        ns = self.config["wordpress"]["api_namespace"].strip("/")
        return f"{base}/{ns}/{endpoint.lstrip('/')}"

    def _now(self) -> str:
        return datetime.now(timezone.utc).isoformat()

    def _now_file(self) -> str:
        return datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")

    def _backup_path(self, task_id: str, page_id: int) -> Path:
        backup_dir = self.project_root / self.config["logging"]["backup_dir"] / task_id
        backup_dir.mkdir(parents=True, exist_ok=True)
        return backup_dir / f"page_{page_id}_{self._now_file()}.json"

    def _log_operation(self, record: dict[str, Any]) -> None:
        log_path = self.project_root / self.config["logging"]["operations_log"]
        log_path.parent.mkdir(parents=True, exist_ok=True)
        with open(log_path, "a", encoding="utf-8") as f:
            f.write(json.dumps(record, ensure_ascii=False) + "\n")

    def _check_operation(self, operation: str) -> None:
        if operation in self._denied_ops:
            raise ForbiddenOperationError(f"Operation {operation} is forbidden")
        if operation not in self._allowed_ops:
            raise ForbiddenOperationError(f"Operation {operation} is not allowed")

    def _check_production_lock(self, approved: bool) -> None:
        if self.config["modes"].get("production_lock", True) and not approved:
            raise ProductionLockError(
                "Production lock is active. Set approved=True for mutations."
            )

    # ------------------------------------------------------------------
    # HTTP primitives
    # ------------------------------------------------------------------

    def _get(self, endpoint: str, params: dict[str, Any] | None = None) -> dict[str, Any]:
        url = self._api_url(endpoint)
        resp = self._session.get(
            url,
            params=params,
            auth=self._auth,
            timeout=self.config["wordpress"]["timeout"],
            verify=self.config["wordpress"].get("verify_ssl", True),
        )
        resp.raise_for_status()
        return resp.json()

    def _post(self, endpoint: str, payload: dict[str, Any]) -> dict[str, Any]:
        url = self._api_url(endpoint)
        resp = self._session.post(
            url,
            json=payload,
            auth=self._auth,
            timeout=self.config["wordpress"]["timeout"],
            verify=self.config["wordpress"].get("verify_ssl", True),
        )
        resp.raise_for_status()
        return resp.json()

    # ------------------------------------------------------------------
    # Allowed operations
    # ------------------------------------------------------------------

    def get_page(self, page_id: int) -> dict[str, Any]:
        """GET /pages/{id}."""
        self._check_operation("GET_PAGE")
        return self._get(f"pages/{page_id}")

    def get_pages(self, per_page: int = 100) -> list[dict[str, Any]]:
        """GET /pages."""
        self._check_operation("GET_PAGES")
        data = self._get("pages", params={"per_page": per_page})
        if isinstance(data, list):
            return data
        return []

    def update_page(
        self,
        page_id: int,
        updates: dict[str, Any],
        task_id: str | None = None,
        approved: bool = False,
        dry_run: bool = False,
        operator: str = "Hermes",
    ) -> dict[str, Any]:
        """UPDATE page fields safely."""
        operation = self._infer_operation(updates)
        self._check_operation(operation)
        self._check_production_lock(approved)

        task_id = task_id or f"LAOS-WP-{uuid.uuid4().hex[:8]}"
        start = time.monotonic()

        current: dict[str, Any] = {}
        backup_id: str | None = None
        rollback_status = "none"
        status = "success"
        error: str | None = None
        verification_result = False

        result: dict[str, Any] = {}
        try:
            # 1. Backup current page
            current = self.get_page(page_id)
            backup_path = self._backup_path(task_id, page_id)
            with open(backup_path, "w", encoding="utf-8") as f:
                json.dump(current, f, ensure_ascii=False, indent=2)
            backup_id = str(backup_path.relative_to(self.project_root))

            # 2. Dry run
            if dry_run or self.config["modes"].get("dry_run", True):
                diff = self._diff_payload(current, updates)
                # If global dry_run is true, we stop here unless dry_run=False explicitly requested.
                if dry_run or self.config["modes"].get("dry_run", True):
                    return {
                        "task_id": task_id,
                        "page_id": page_id,
                        "operation": operation,
                        "dry_run": True,
                        "diff": diff,
                        "status": "dry_run",
                    }

            # 3. Send update
            payload = self._build_payload(updates)
            result = self._post(f"pages/{page_id}", payload)

            # 4. Verification
            verification_result = self._verify_update(page_id, updates, result)

            # 5. Clear cache
            self._clear_cache(page_id)

            if not verification_result:
                raise WordPressAdapterError("Verification failed")

        except Exception as exc:
            status = "error"
            error = str(exc)
            # 6. Rollback
            if backup_id and self.config["modes"].get("auto_rollback", True):
                try:
                    self._rollback(page_id, backup_id)
                    rollback_status = "success"
                    status = "rolled_back"
                except Exception as rollback_exc:
                    rollback_status = "failed"
                    error += f" | Rollback failed: {rollback_exc}"

        finally:
            duration_ms = int((time.monotonic() - start) * 1000)
            record = {
                "task_id": task_id,
                "page_id": page_id,
                "operation": operation,
                "backup_id": backup_id,
                "dry_run": dry_run or self.config["modes"].get("dry_run", True),
                "verification_result": verification_result,
                "rollback_status": rollback_status,
                "duration_ms": duration_ms,
                "operator": operator,
                "timestamp": self._now(),
                "status": status,
                "error": error,
                "diff_summary": self._diff_payload(current, updates),
            }
            self._log_operation(record)

        if status == "error":
            raise WordPressAdapterError(error or "Unknown error")
        if status == "rolled_back":
            raise WordPressAdapterError(f"Update failed and rolled back: {error}")

        return result

    def create_draft(
        self,
        title: str,
        content: str,
        excerpt: str = "",
        task_id: str | None = None,
        approved: bool = False,
        dry_run: bool = False,
        operator: str = "Hermes",
    ) -> dict[str, Any]:
        """CREATE draft page."""
        self._check_operation("CREATE_DRAFT")
        self._check_production_lock(approved)

        task_id = task_id or f"LAOS-WP-{uuid.uuid4().hex[:8]}"
        start = time.monotonic()
        status = "success"
        error: str | None = None

        payload = {
            "title": title,
            "content": content,
            "excerpt": excerpt,
            "status": "draft",
        }

        if dry_run or self.config["modes"].get("dry_run", True):
            return {
                "task_id": task_id,
                "page_id": None,
                "operation": "CREATE_DRAFT",
                "dry_run": True,
                "payload": payload,
                "status": "dry_run",
            }

        try:
            result = self._post("pages", payload)
        except Exception as exc:
            status = "error"
            error = str(exc)
            raise
        finally:
            duration_ms = int((time.monotonic() - start) * 1000)
            record = {
                "task_id": task_id,
                "page_id": None,
                "operation": "CREATE_DRAFT",
                "backup_id": None,
                "dry_run": dry_run,
                "verification_result": status == "success",
                "rollback_status": "none",
                "duration_ms": duration_ms,
                "operator": operator,
                "timestamp": self._now(),
                "status": status,
                "error": error,
                "diff_summary": {"title": title, "excerpt": excerpt},
            }
            self._log_operation(record)

        return result

    def publish_approved_draft(
        self,
        page_id: int,
        task_id: str | None = None,
        approved: bool = False,
        operator: str = "Hermes",
    ) -> dict[str, Any]:
        """PUBLISH approved draft."""
        self._check_operation("PUBLISH_APPROVED_DRAFT")
        self._check_production_lock(approved)

        task_id = task_id or f"LAOS-WP-{uuid.uuid4().hex[:8]}"
        start = time.monotonic()
        status = "success"
        error: str | None = None

        # Verify draft exists
        current = self.get_page(page_id)
        if current.get("status") != "draft":
            raise WordPressAdapterError(
                f"Page {page_id} is not a draft (status={current.get('status')})"
            )

        try:
            result = self._post(f"pages/{page_id}", {"status": "publish"})
        except Exception as exc:
            status = "error"
            error = str(exc)
            raise
        finally:
            duration_ms = int((time.monotonic() - start) * 1000)
            record = {
                "task_id": task_id,
                "page_id": page_id,
                "operation": "PUBLISH_APPROVED_DRAFT",
                "backup_id": None,
                "dry_run": False,
                "verification_result": status == "success",
                "rollback_status": "none",
                "duration_ms": duration_ms,
                "operator": operator,
                "timestamp": self._now(),
                "status": status,
                "error": error,
                "diff_summary": {"status": "draft → publish"},
            }
            self._log_operation(record)

        return result

    def update_faq_block(
        self,
        page_id: int,
        faq_html: str,
        task_id: str | None = None,
        approved: bool = False,
        dry_run: bool = False,
        operator: str = "Hermes",
    ) -> dict[str, Any]:
        """UPDATE FAQ block inside page content."""
        self._check_operation("UPDATE_FAQ_BLOCK")
        current = self.get_page(page_id)
        content = current.get("content", {}).get("raw", "")
        new_content = self._replace_faq_block(content, faq_html)
        return self.update_page(
            page_id=page_id,
            updates={"content": new_content},
            task_id=task_id,
            approved=approved,
            dry_run=dry_run,
            operator=operator,
        )

    def update_internal_links(
        self,
        page_id: int,
        link_map: dict[str, str],
        task_id: str | None = None,
        approved: bool = False,
        dry_run: bool = False,
        operator: str = "Hermes",
    ) -> dict[str, Any]:
        """UPDATE internal links inside page content."""
        self._check_operation("UPDATE_INTERNAL_LINKS")
        current = self.get_page(page_id)
        content = current.get("content", {}).get("raw", "")
        new_content = self._replace_links(content, link_map)
        return self.update_page(
            page_id=page_id,
            updates={"content": new_content},
            task_id=task_id,
            approved=approved,
            dry_run=dry_run,
            operator=operator,
        )

    # ------------------------------------------------------------------
    # Payload / Diff / Verification
    # ------------------------------------------------------------------

    def _infer_operation(self, updates: dict[str, Any]) -> str:
        if "content" in updates and "faq" in str(updates.get("content", "")).lower():
            return "UPDATE_FAQ_BLOCK"
        if set(updates.keys()) == {"content"}:
            return "UPDATE_PAGE_CONTENT"
        if set(updates.keys()) == {"title"}:
            return "UPDATE_TITLE"
        if set(updates.keys()) == {"excerpt"}:
            return "UPDATE_EXCERPT"
        if set(updates.keys()) == {"featured_media"}:
            return "UPDATE_FEATURED_IMAGE"
        if "meta" in updates or "yoast_meta_description" in updates:
            return "UPDATE_META_DESCRIPTION"
        return "UPDATE_PAGE_CONTENT"

    def _build_payload(self, updates: dict[str, Any]) -> dict[str, Any]:
        payload: dict[str, Any] = {}
        for key, value in updates.items():
            if key not in self.ALLOWED_UPDATE_FIELDS and key not in {
                "yoast_meta_description", "yoast_title"
            }:
                raise ForbiddenOperationError(f"Field {key} is not allowed to update")
            if key in ("yoast_meta_description", "yoast_title"):
                # Map to Yoast meta field if configured
                payload.setdefault("meta", {})[key] = value
            else:
                payload[key] = value
        return payload

    def _diff_payload(self, current: dict[str, Any], updates: dict[str, Any]) -> dict[str, Any]:
        diff: dict[str, Any] = {}
        for key, value in updates.items():
            old = current.get(key)
            if isinstance(old, dict):
                old = old.get("raw", old.get("rendered", old))
            diff[key] = {"old": old, "new": value}
        return diff

    def _replace_faq_block(self, content: str, faq_html: str) -> str:
        marker_start = "<!-- LAOS-FAQ-START -->"
        marker_end = "<!-- LAOS-FAQ-END -->"
        if marker_start in content and marker_end in content:
            pattern = re.escape(marker_start) + ".*?" + re.escape(marker_end)
            return re.sub(pattern, f"{marker_start}\n{faq_html}\n{marker_end}", content, flags=re.DOTALL)
        return f"{content}\n{marker_start}\n{faq_html}\n{marker_end}"

    def _replace_links(self, content: str, link_map: dict[str, str]) -> str:
        new_content = content
        for old_url, new_url in link_map.items():
            new_content = new_content.replace(old_url, new_url)
        return new_content

    def _verify_update(
        self,
        page_id: int,
        updates: dict[str, Any],
        result: dict[str, Any],
    ) -> bool:
        verification = self.config.get("verification", {})

        # HTML check
        if verification.get("html_check", True):
            rendered = result.get("content", {}).get("rendered", "")
            if not rendered.strip():
                return False
            if "title" in updates and "<h1" not in rendered:
                # Title update doesn't require h1 in content, skip
                pass
            if "content" in updates and not re.search(r"<[^>]+>", rendered):
                return False

        # HTTP check
        if verification.get("http_check", True):
            try:
                url = result.get("link", "")
                resp = self._session.head(
                    url,
                    timeout=self.config["wordpress"]["timeout"],
                    allow_redirects=True,
                )
                if resp.status_code != self.config["verification"].get("expected_http_status", 200):
                    return False
            except Exception:
                return False

        # SEO check
        if verification.get("seo_check", True):
            if "title" in updates:
                expected_title = updates["title"]
                actual_title = result.get("title", {}).get("rendered", "")
                if expected_title != actual_title:
                    return False
            if "content" in updates:
                if not result.get("content", {}).get("rendered", "").strip():
                    return False

        return True

    def _clear_cache(self, page_id: int) -> None:
        """Clear cache if endpoint is configured."""
        cache_url = self.config.get("verification", {}).get("cache_clear_url")
        if not cache_url:
            return
        try:
            self._session.get(cache_url, timeout=10)
        except Exception:
            # Cache clearing is best-effort; do not fail operation
            pass

    def _rollback(self, page_id: int, backup_id: str) -> dict[str, Any]:
        """Restore page from backup."""
        backup_path = self.project_root / backup_id
        with open(backup_path, "r", encoding="utf-8") as f:
            backup = json.load(f)
        payload = {
            "title": backup.get("title", {}).get("raw", ""),
            "content": backup.get("content", {}).get("raw", ""),
            "excerpt": backup.get("excerpt", {}).get("raw", ""),
            "status": backup.get("status", "publish"),
        }
        if backup.get("featured_media"):
            payload["featured_media"] = backup["featured_media"]
        return self._post(f"pages/{page_id}", payload)


if __name__ == "__main__":
    # Example usage (dry run, requires env vars and config)
    adapter = WordPressAdapter()
    print("Pages:", len(adapter.get_pages()))
