"""Standalone tests for AdvisorSanitizer that do not require the full advisor module."""

import pytest

from Advisor.advisor import AdvisorSanitizer, SanitizationError


def test_password_redacted():
    text = "DB_PASSWORD=supersecret123"
    result = AdvisorSanitizer.sanitize(text)
    assert "supersecret123" not in result


def test_email_redacted():
    text = "Contact owner@laser178.ru for details."
    result = AdvisorSanitizer.sanitize(text)
    assert "owner@laser178.ru" not in result


def test_phone_redacted():
    text = "Call +7 999 123-45-67"
    result = AdvisorSanitizer.sanitize(text)
    assert "+7 999 123-45-67" not in result


def test_url_secret_redacted_but_url_kept():
    text = "https://laser178.ru/webhook?token=abc123secret"
    result = AdvisorSanitizer.sanitize(text)
    assert "token=abc123secret" not in result
    assert "https://laser178.ru/webhook" in result


def test_api_key_redacted():
    text = "OPENAI_API_KEY = sk-proj-abc123def456"
    result = AdvisorSanitizer.sanitize(text)
    assert "sk-proj-abc123def456" not in result


def test_validate_safety_raises_on_password():
    text = "password=abc123"
    with pytest.raises(SanitizationError):
        AdvisorSanitizer.validate_safety(text)


def test_validate_safety_raises_on_api_key():
    text = "API key is sk-proj-abc123def456"
    with pytest.raises(SanitizationError):
        AdvisorSanitizer.validate_safety(text)


def test_validate_safety_passes_clean_text():
    text = "The site uses a dark theme and a fixed bottom navigation bar."
    AdvisorSanitizer.validate_safety(text)  # should not raise


def test_truncate():
    text = "word " * 1000
    result = AdvisorSanitizer.sanitize(text, max_chars=50)
    assert "[TRUNCATED]" in result


if __name__ == "__main__":
    pytest.main([__file__, "-v"])
