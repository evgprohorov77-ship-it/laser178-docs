# Архитектура репозитория LAOS

## Версия

2.0 — LAOS Kernel

## Принцип

Репозиторий разделён на зоны по аналогии с операционной системой:

| Зона | Роль в ОС | Папка |
|------|-----------|-------|
| Decision Engine | Процессор | `AI/Decision/` |
| Policy | Оперативная память / правила | `Policies/` |
| Knowledge | Файловая система | `Knowledge/` |
| Authorization | Служба безопасности | `AI/Authorization/` |
| Action Engine | Драйверы | `AI/Engines/` |
| Adapters | Внешние устройства | `AI/Adapters/` (RFC) |
| Auditors | Сенсоры | `Auditors/` |
| Logs | Журнал событий | `Logs/` |
| Environment | Конфигурация среды | `Environment/` |
| Capabilities | Разрешения | `Capabilities/` |
| Approval | Change Management | `AI/Approval/` |
| RFC | Процесс изменений | `RFC/` |

## Структура

```
laser178-ai-framework/
├── AI/                             # Исполнительная система
│   ├── Authorization/              # Authorization Engine
│   │   ├── README.md
│   │   ├── __init__.py
│   │   └── authorization_engine.py
│   ├── Approval/                   # Owner Approval System
│   │   ├── README.md
│   │   ├── __init__.py
│   │   └── approval_manager.py
│   ├── Backup/                     # Backup Engine
│   │   ├── README.md
│   │   ├── __init__.py
│   │   └── backup_engine.py
│   ├── Decision/                   # Decision Engine
│   │   ├── README.md
│   │   ├── AI_DECISION_ENGINE.md
│   │   ├── __init__.py
│   │   └── decision_engine.py
│   ├── DryRun/                     # Dry Run Engine
│   │   ├── README.md
│   │   ├── __init__.py
│   │   └── dry_run_engine.py
│   ├── Engines/                    # Risk, Action, Verification, Logger
│   │   ├── README.md
│   │   ├── __init__.py
│   │   ├── action_engine.py
│   │   ├── risk_engine.py
│   │   ├── verification_engine.py
│   │   └── logger.py
│   └── Models/                     # Единые модели данных
│       ├── __init__.py
│       ├── finding.py
│       └── execution_context.py
│
├── Auditors/                       # Детекторы проблем
│   ├── README.md
│   ├── __init__.py
│   ├── base_auditor.py
│   ├── runner.py
│   ├── seo_auditor.py
│   ├── structure_auditor.py
│   ├── images_auditor.py
│   ├── performance_auditor.py
│   ├── security_auditor.py
│   └── wordpress_auditor.py
│
├── Capabilities/                   # Capability System
│   ├── README.md
│   ├── seo.json
│   ├── wordpress.json
│   ├── content.json
│   ├── security.json
│   └── analytics.json
│
├── Environment/                    # Среды выполнения
│   ├── README.md
│   ├── current.json
│   ├── development.json
│   ├── staging.json
│   └── production.json
│
├── Framework/                      # Статические правила и роли
│   ├── zones.md
│   ├── roles.md
│   └── quality-checklist.md
│
├── Knowledge/                      # База знаний
│   ├── README.md
│   ├── schema.md                   # Knowledge Schema
│   ├── Company/                    # Сущности компании
│   │   ├── template.yaml           # Единый шаблон
│   │   ├── company.yaml
│   │   ├── contacts.yaml
│   │   ├── employees.yaml
│   │   ├── services.yaml
│   │   ├── materials.yaml
│   │   ├── equipment.yaml
│   │   ├── prices.yaml
│   │   ├── faq.yaml
│   │   ├── guarantees.yaml
│   │   ├── partners.yaml
│   │   └── cars.yaml
│
├── Policies/                       # Policy Layer
│   ├── authorization_policy.md
│   ├── autofix_policy.md
│   ├── backup_policy.md
│   ├── verification_policy.md
│   ├── logging_policy.md
│   ├── production_policy.md
│   ├── dry_run_policy.md
│   └── human_approval_policy.md
│
├── Registry/                       # Реестр правил
│   └── rules.json
│
├── RFC/                            # Request for Comments
│   ├── README.md
│   ├── template.md
│   ├── 0001-laos-kernel-policy-layer.md
│   └── 0002-wordpress-adapter.md
│
├── Operations/                     # SOP и legacy скрипты
│   ├── backup-sop.md
│   └── audit.py                    # deprecated
│
├── Scripts/                        # Entrypoints
│   ├── __init__.py
│   └── run_laos.py
│
├── Tests/                          # Тесты
│
├── Logs/                           # Логи (в .gitignore)
│   └── .gitkeep
│
├── .github/                        # Шаблоны issue
│   └── ISSUE_TEMPLATE/
│
├── README.md
├── ARCHITECTURE.md
├── .gitignore
└── AI_DECISION_ENGINE.md
```

## Главный поток

```
AuditRunner
    ↓ List[Finding]
DecisionEngine
    ↓ Decision
AuthorizationEngine
    ↓ allowed / denied
DryRunEngine
    ↓ dry_run report
ApprovalManager (если нужно)
    ↓ approved / rejected
BackupEngine
    ↓ backup_uuid
ActionEngine
    ↓ action_result
VerificationEngine
    ↓ success / failed
Logger
    ↓ logs
Issue (только если не исправлено)
```

## Преимущества разделения

| Решение | Почему |
|---------|--------|
| `AI/Authorization/` отдельно | Безопасность — отдельная служба, не смешана с Action Engine. |
| `AI/Backup/` отдельно | Backup — это алгоритм, а не часть Action Engine. |
| `AI/DryRun/` отдельно | Dry Run должен быть видим и независим. |
| `AI/Approval/` отдельно | Change Management с UUID и статусами. |
| `Environment/` | Production Lock отдельно от кода. |
| `Capabilities/` | Можно отключить опасные возможности, не меняя код. |
| `Knowledge/Company/` | База знаний как файловая система, не один огромный файл. |
| `RFC/` | Архитектурные изменения проходят review. |

## WordPress Adapter

Создаётся последним. В `RFC/0002-wordpress-adapter.md` уже зафиксировано, что он:

- не использует WordPress REST API;
- является одним из адаптеров в `AI/Adapters/`;
- требует approval в production;
- работает только через Capability `wordpress`.

## Безопасность

- Production Lock активен по умолчанию.
- Capability `security` отключена до завершения Security Layer.
- Все автоматические изменения в production требуют owner approval.
- Dry Run и Backup обязательны перед любым действием.
- **LAOS Core заморожен.** Новые Engine создаются только через RFC.
- **Knowledge first.** Любой контент и статьи LAOS проверяются на соответствие Knowledge DB.

## Расширение

1. Добавить `AI/Adapters/WordPress/` после одобрения RFC-0002.
2. Добавить `AI/Adapters/GitHub/`, `AI/Adapters/Cloudflare/` и т.д.
3. Добавить `AI/Memory/` для долговременной памяти агента.
4. Добавить `AI/Models/agent_state.py`.
5. Расширить Knowledge DB: добавить `Company/legal.yaml`, `Company/clients.yaml`, `Company/competitors.yaml`.
