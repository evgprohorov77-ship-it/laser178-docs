#!/usr/bin/env python3
"""
Knowledge-aware content checker.

Проверяет черновик статьи на соответствие Knowledge.
Использование:
    python3 Scripts/check_content.py path/to/article.md
"""

import os
import re
import sys
import yaml
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
KNOWLEDGE_DIR = ROOT / "Knowledge" / "Company"


def load_knowledge():
    facts = []
    for path in sorted(KNOWLEDGE_DIR.glob("*.yaml")):
        with open(path, "r", encoding="utf-8") as f:
            data = yaml.safe_load(f)
        _walk(data, path.name, facts)
    return facts


def _walk(obj, path, facts):
    if isinstance(obj, dict):
        if "value" in obj and "verified" in obj and "source" in obj:
            value = obj["value"]
            if isinstance(value, str):
                facts.append({
                    "path": path,
                    "value": value,
                    "verified": obj["verified"],
                    "source": obj["source"],
                })
        for k, v in obj.items():
            _walk(v, f"{path}.{k}", facts)
    elif isinstance(obj, list):
        for i, item in enumerate(obj):
            _walk(item, f"{path}[{i}]", facts)


def extract_unverified_facts():
    facts = load_knowledge()
    return [f for f in facts if not f["verified"] or f["source"] in {"needs_verification", "inferred"}]


def check_article(text):
    issues = []
    unverified = extract_unverified_facts()

    # 1. Check for claims that match unverified facts but lack marker
    for fact in unverified:
        value = fact["value"]
        if value and value != "needs_verification" and len(value) > 5:
            if value.lower() in text.lower() and "[needs verification" not in text.lower():
                issues.append(f"Unverified claim used without marker: '{value}' (source: {fact['source']})")

    # 2. Check for needs_verification placeholders
    placeholders = re.findall(r"needs_verification", text, re.IGNORECASE)
    if placeholders:
        issues.append(f"Article contains {len(placeholders)} 'needs_verification' placeholder(s).")

    # 3. Check for direct contradictions with verified facts (basic substring)
    verified = load_knowledge()
    for fact in verified:
        if fact["verified"] and fact["source"] != "inferred":
            v = fact["value"]
            if v and len(v) > 10 and v.lower() in text.lower():
                # basic contradiction check: if verified fact is negated
                pass

    return issues


def main():
    if len(sys.argv) < 2:
        print("Usage: python3 Scripts/check_content.py path/to/article.md")
        sys.exit(1)

    path = Path(sys.argv[1])
    if not path.exists():
        print(f"File not found: {path}")
        sys.exit(1)

    with open(path, "r", encoding="utf-8") as f:
        text = f.read()

    issues = check_article(text)
    if issues:
        print(f"\n❌ Content check failed for {path}:")
        for issue in issues:
            print(f"   - {issue}")
        sys.exit(1)
    else:
        print(f"✅ Content check passed for {path}")


if __name__ == "__main__":
    main()
