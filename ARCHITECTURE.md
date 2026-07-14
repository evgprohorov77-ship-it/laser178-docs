# LAOS Architecture

## Версия

1.1.0

## Общее описание

LAOS состоит из трёх слоёв:

1. **Owner Portal** — интерфейс владельца для редактирования бизнес-данных.
2. **Knowledge Layer** — проверенная база знаний, производная от Owner Portal.
3. **Governance Layer** — процессы, роли, утверждения, релизы, аудит.

## Схема данных

```
Owner Portal (Owner редактирует MASTER_PROFILE.yaml)
       ↓
Scripts/sync_owner_to_knowledge.py (генерирует 5 YAML + синхронизирует + валидирует)
       ↓
Knowledge Layer (YAML DB)
       ↓
Scripts/generate_content.py + Content Templates
       ↓
Content Drafts (страницы, статьи, FAQ)
       ↓
Scripts/check_content.py (проверка на needs_verification/inferred)
       ↓
Owner Approval
       ↓
Production (WordPress, FTP) — только после Security Layer + test stand
```

## Компоненты

### Owner Portal

- `Owner/company_profile.yaml` → `Knowledge/Company/company.yaml`
- `Owner/contacts.yaml` → `Knowledge/Company/contacts.yaml`
- `Owner/services.yaml` → `Knowledge/Company/services.yaml`
- `Owner/guarantees.yaml` → `Knowledge/Company/guarantees.yaml`
- `Owner/pricing.yaml` → `Knowledge/Company/prices.yaml` + `materials.yaml`

### Knowledge Layer

- Единая схема: `Knowledge/schema.md`.
- Валидатор: `Scripts/validate_knowledge.py`.
- Каждый YAML-файл имеет `id`, `version`, `entity_type`, `status`, `verification`.
- Все факты имеют `value`, `verified`, `source`, `public` (для Owner-derived) или `value`, `verified`, `source` (для Knowledge).

### Governance Layer

См. `Governance/README.md`. Основные документы:
- `roles.md` — роли.
- `permissions.md` — права.
- `approval_matrix.md` — матрица утверждений.
- `change_management.md` — процесс изменений.
- `release_policy.md` — релизы и версии.
- `versioning_policy.md` — версионирование компонентов.
- `incident_response.md` — инциденты.
- `audit_policy.md` — аудит.

## Структура репозитория

```
laser178-ai-framework/
├── .hermes/
├── Archive/
├── Audit/
├── Changelog/
├── Content/
├── Content/                  # Content Layer
│   ├── README.md
│   ├── Templates/            # Шаблоны страниц и статей
│   ├── Drafts/               # Черновики, сгенерированные Hermes
│   ├── Published/            # Утверждённые и опубликованные материалы
│   └── Fragments/            # CTA, контакты, гарантийные вставки
├── Governance/           ← Governance Layer
├── Knowledge/              ← Knowledge Layer
│   ├── schema.md
│   └── Company/
├── Owner/                  ← Owner Portal
├── Research/
│   └── Competitors/
├── RFC/
├── Scripts/
│   ├── validate_knowledge.py
│   ├── check_content.py
│   └── sync_owner_to_knowledge.py
├── ARCHITECTURE.md
├── README.md
└── version.txt
```

## Change Management

```
Идея / Запрос
      ↓
   RFC
      ↓
   Review
      ↓
   Approval
      ↓
   Implementation
      ↓
   Verification
      ↓
   Release
      ↓
   Changelog
```

## Версионирование

- LAOS Core: `vMAJOR.MINOR.PATCH` в `version.txt`.
- Knowledge Schema: отдельная версия в `Knowledge/schema.md`.
- Owner Portal: `Owner/README.md` → версия.
- Governance: версия в каждом документе.
- Скрипты: `__version__` в каждом.

## Release Policy

- **v0.x** — эксперименты, breaking changes разрешены.
- **v1.x** — обратная совместимость обязательна. Текущий статус.
- **v2.x+** — только через RFC, breaking changes согласованы.

## Security Layer

- **Статус:** не реализован.
- **Зависимости:** WordPress Adapter, production access, secrets management.
- **Условие запуска:** завершение Security Layer и успешная эксплуатация на тестовом стенде.

## Запреты

- Не создавать новые Engines или Adapters в Feature Freeze без RFC и owner approval.
- Не подключать Production без owner approval.
- Не использовать WordPress REST API для production изменений до Security Layer и успешного тестового стенда.
- Не хранить credentials в репозитории.
- WordPress Adapter v1 не выполняет DELETE, не меняет plugins/theme/menus/users/roles/robots.txt/PHP/wp-config.

## Приоритеты

1. Knowledge — №1.
2. Governance — №2.
3. Практическое применение на laser178.ru — №3.
4. WordPress Adapter — после Security Layer + test stand.

## Безопасность

- Production Lock активен по умолчанию.
- Capability `security` отключена до завершения Security Layer.
- Все автоматические изменения в production требуют owner approval.
- Dry Run и review обязательны перед любым релизом.
- Hermes не хранит и не получает credentials, пароли, API-ключи.

## Текущий режим

- **LAOS Foundation v1.0 завершён.**
- **LAOS-008A — Site Launch Mode.**
- **LAOS-009 — WordPress Adapter v1 в разработке (RFC-0009 принят).**
- Feature freeze ядра: не создаём новые Engine, Adapter, Layer, глобальные абстракции без RFC и owner approval.
- Разрешено: исправлять ошибки, писать контент, править SEO, заполнять Knowledge, готовить сайт к запуску, разрабатывать LAOS-009.
- Идеи, не влияющие на запуск, откладываются в `Roadmap/PostLaunch/`.

## Роадмап

1. **LAOS-008A — Site Launch Mode:**
   - Запуск сайта `https://laser178.ru`.
   - Исправление критических ошибок (см. `Launch/PreLaunch_Report.md`).
   - Наполнение обязательных страниц.
   - SEO + аналитика.
2. **Post-launch:**
   - Регулярный контент, аудит, мониторинг.
   - Развёртывание WordPress Adapter v1 в production после Security Layer и аудита.
   - Другие интеграции — по мере необходимости (см. `Roadmap/PostLaunch/`).

## Архитектурные ограничения

- **Запрещено до запуска:**
  - новые Engine;
  - новые Adapter (WordPress, Telegram, Cloudflare, CLI);
  - новые Layer;
  - Memory / Cron / Cloudflare integration;
  - любые архитектурные изменения без RFC и бизнес-обоснования.