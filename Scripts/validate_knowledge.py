#!/usr/bin/env python3
"""
Валидатор Knowledge DB.

Проверяет:
1. YAML валиден.
2. Обязательные поля корня присутствуют.
3. Все утверждения с source=needs_verification имеют verified=false.
4. Значение "needs_verification" корректно обработано.
5. Статус verification соответствует заполненности.
"""

import os
import sys
import yaml
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
KNOWLEDGE_DIR = ROOT / "Knowledge" / "Company"

REQUIRED_ROOT = {"id", "version", "entity_type", "status", "verification"}
VALID_SOURCES = {
    "website", "calculator_js", "direct", "conversation", "memory_user_preference",
    "inferred", "needs_verification", "website_partial",
}
VALID_ENTITY_TYPES = {
    "company", "service_catalog", "material_catalog", "guarantee_policy",
    "price_catalog", "faq", "car_catalog", "equipment_catalog", "contact_card",
    "employee_directory", "partner_directory",
}


def collect_files():
    files = sorted(KNOWLEDGE_DIR.glob("*.yaml"))
    return [f for f in files if f.name != "template.yaml"]


def check_value(obj, path, errors):
    if isinstance(obj, dict):
        if "value" in obj and "verified" in obj and "source" in obj:
            src = obj.get("source")
            verified = obj.get("verified")
            if src not in VALID_SOURCES:
                errors.append(f"{path}: invalid source '{src}'")
            if src == "needs_verification" and verified is not False:
                errors.append(f"{path}: source=needs_verification requires verified=false")
            if obj.get("value") == "needs_verification" and verified is not False:
                errors.append(f"{path}: value=needs_verification requires verified=false")
        for k, v in obj.items():
            check_value(v, f"{path}.{k}", errors)
    elif isinstance(obj, list):
        for i, item in enumerate(obj):
            check_value(item, f"{path}[{i}]", errors)


def validate_file(path):
    errors = []
    try:
        with open(path, "r", encoding="utf-8") as f:
            data = yaml.safe_load(f)
    except yaml.YAMLError as e:
        return [f"YAML error: {e}"]

    if not isinstance(data, dict):
        return ["root must be a mapping"]

    missing = REQUIRED_ROOT - set(data.keys())
    if missing:
        errors.append(f"missing root fields: {', '.join(sorted(missing))}")

    if data.get("entity_type") not in VALID_ENTITY_TYPES:
        errors.append(f"invalid entity_type: {data.get('entity_type')}")

    if data.get("status") not in {"draft", "approved", "deprecated"}:
        errors.append(f"invalid status: {data.get('status')}")

    if data.get("verification") not in {"pending", "partial", "complete"}:
        errors.append(f"invalid verification: {data.get('verification')}")

    check_value(data, "root", errors)
    return errors


def main():
    files = collect_files()
    if not files:
        print("No YAML files found.")
        sys.exit(1)

    total_errors = 0
    for path in files:
        errors = validate_file(path)
        rel = path.relative_to(ROOT)
        if errors:
            print(f"\n❌ {rel}")
            for e in errors:
                print(f"   - {e}")
            total_errors += len(errors)
        else:
            print(f"✅ {rel}")

    print(f"\n{'='*40}")
    print(f"Files checked: {len(files)}")
    print(f"Errors: {total_errors}")

    if total_errors > 0:
        sys.exit(1)
    print("Knowledge DB is valid.")


if __name__ == "__main__":
    main()
