# LAOS — Laser178 AI Operating System

## Статус

- **LAOS Core:** Feature Freeze (LAOS-005)
- **Версия:** v1.0.0
- **Автор:** Евгений Прохоров + Hermes

## Описание

LAOS — цифровой инженер компании «Лазер Антикор».  
Система управляет знаниями, контентом и изменениями сайта laser178.ru через проверенную базу знаний и governance-процессы.

## Ключевые принципы

1. **LAOS Core завершён.** Новые Engines и Adapters создаются только через RFC и owner approval.
2. **WordPress REST Adapter (LAOS-009)** находится в разработке и недоступен для production до завершения Security Layer и тестового стенда.
3. **Production изменения только после owner approval.**
4. **Owner Portal — единственный источник правды для бизнес-данных.**
5. **Knowledge DB — производная от Owner Portal + валидация.**
6. **Любое изменение проходит через RFC → Review → Approval → Implementation → Verification → Release → Changelog.**
7. **Публичный текст сайта не является достаточным источником для цен, гарантий и юридических условий.**

## Режим работы

- **LAOS Foundation v1.0 завершён.**
- **LAOS-008A — Site Launch Mode** активен до запуска сайта.
- **LAOS-009 — WordPress Adapter v1** в разработке (RFC-0009 принят).
- Feature freeze ядра: не создаём новые Engine, Adapter, Layer, глобальные абстракции без RFC и approval.
- Разрешено: исправлять ошибки, писать контент, править SEO, готовить сайт к запуску, разрабатывать LAOS-009.
- Идеи, не влияющие на запуск, откладываются в `Roadmap/PostLaunch/`.

## Текущий приоритет

**Запуск сайта https://laser178.ru**

См. `Launch/MASTER_LAUNCH_CHECKLIST.md` и `Launch/PreLaunch_Report.md`.

## Быстрый старт

### Для Owner

1. Редактируй единый файл `Owner/MASTER_PROFILE.yaml`.
2. Скажи Hermes: **«синхронизируй мастер-профиль»**.
3. Hermes автоматически:
   - сгенерирует 5 файлов в `Owner/`;
   - обновит `Knowledge/`;
   - запустит `validate_knowledge.py`.
4. Скажи Hermes: **«сгенерируй контент»**.
5. Hermes создаст черновики в `Content/Drafts/`.
6. Review diff и дай approve.
7. Hermes не опубликует без approval.

### Для разработчика/Operator

```bash
python3 Scripts/validate_knowledge.py
python3 Scripts/check_content.py path/to/article.md
python3 Scripts/sync_owner_to_knowledge.py --dry-run
python3 Scripts/sync_owner_to_knowledge.py
python3 Scripts/generate_content.py --all
```

## Структура репозитория

```
laser178-ai-framework/
├── .hermes/                  # Конфигурация Hermes
├── Archive/                  # Архив legacy-файлов (только после owner approval)
├── Audit/                    # Аудиторские записи
├── Changelog/                # История изменений по версиям
├── Content/                  # Контент, статьи, FAQ (draft)
├── Governance/               # Governance Layer
│   ├── README.md
│   ├── roles.md
│   ├── permissions.md
│   ├── approval_matrix.md
│   ├── change_management.md
│   ├── release_policy.md
│   ├── versioning_policy.md
│   ├── incident_response.md
├── Content/                  # Content Layer (templates, drafts, published)
│   ├── README.md
│   ├── Templates/
│   ├── Drafts/
│   ├── Published/
│   └── Fragments/
├── Governance/               # Governance Layer
├── Knowledge/                # База знаний (производная от Owner Portal)
│   ├── README.md
│   ├── schema.md
│   └── Company/
│       ├── template.md
│       ├── company.yaml
│       ├── contacts.yaml
│       ├── services.yaml
│       ├── materials.yaml
│       ├── equipment.yaml
│       ├── prices.yaml
│       ├── faq.yaml
│       ├── guarantees.yaml
│       ├── partners.yaml
│       ├── cars.yaml
│       ├── employees.yaml
│       └── legal.yaml
├── Owner/                    # Owner Portal (editable)
├── Owner/                     # Owner Portal
│   ├── MASTER_PROFILE.yaml   ← Единый файл для редактирования
│   ├── MASTER_PROFILE.md
│   ├── README.md
│   ├── company_profile.yaml  ← Автогенерация из MASTER_PROFILE
│   ├── contacts.yaml         ← Автогенерация из MASTER_PROFILE
│   ├── services.yaml         ← Автогенерация из MASTER_PROFILE
│   ├── guarantees.yaml       ← Автогенерация из MASTER_PROFILE
│   └── pricing.yaml          ← Автогенерация из MASTER_PROFILE
├── Governance/               # Governance Layer
│   └── Competitors/
├── Launch/                    # Launch Mode — запуск сайта laser178.ru
│   ├── README.md
│   ├── MASTER_LAUNCH_CHECKLIST.md
│   ├── Content_Checklist.md
│   ├── SEO_Checklist.md
│   ├── Technical_Checklist.md
│   ├── UX_Checklist.md
│   ├── Legal_Checklist.md
│   ├── Analytics_Checklist.md
│   ├── PreLaunch_Report.md
│   └── Release_Report.md
├── Roadmap/PostLaunch/        # Идеи, отложенные до после запуска
├── Site_Launch/               # Аудиты и отчёты по сайту
├── Scripts/                   # Валидаторы, генераторы, синхронизация
└── Tests/                     # Тестовые артефакты
│   ├── check_content.py
│   └── sync_owner_to_knowledge.py
├── ARCHITECTURE.md
├── README.md
└── version.txt
```

## Приоритеты

1. **Knowledge — №1.** Наполнение и верификация данных.
2. **Governance — №2.** Процессы, роли, утверждения.
3. **Практическое применение на laser178.ru — №3.**
4. **WordPress Adapter (LAOS-009) — в разработке.** Production access только после Security Layer + test stand + owner approval.

## Безопасность

- Production Lock активен по умолчанию.
- Capability `security` отключена до завершения Security Layer.
- Все автоматические изменения в production требуют owner approval.
- Dry Run и review обязательны перед любым релизом.
- Hermes не хранит и не получает credentials, пароли, API-ключи.

## Лицензия

Собственность ИП Прохоров Е.А. Использование только в рамках laser178.ru.

## Контакты

- Сайт: https://laser178.ru
- Email: info@laser178.ru
- Telegram: DM с Евгением Прохоровым
