# WordPress Adapter v1

## Версия

1.0.0

## Статус

Разработка (LAOS-009). Production access не активен.

## Назначение

Безопасное управление страницами WordPress через REST API без использования браузера и эмуляции пользователя.

## Возможности

- GET page / pages
- UPDATE page content
- UPDATE title
- UPDATE meta description
- UPDATE excerpt
- UPDATE featured image
- CREATE draft
- PUBLISH approved draft
- UPDATE FAQ block
- UPDATE internal links

## Ограничения

- DELETE запрещено.
- Изменение plugins, theme, menus, users, roles, robots.txt, PHP, wp-config запрещено.
- Любой UPDATE требует backup, dry-run, verification.
- Production Lock: обновление возможно только при `approved: true`.

## Установка

```bash
python3 -m venv .venv
source .venv/bin/activate
pip install -r Adapters/WordPress/requirements.txt
```

## Конфигурация

Копируй `Adapters/WordPress/config.example.yaml` в `Adapters/WordPress/config.yaml` и заполни.

Credentials передаются через environment variables:

```bash
export WP_USERNAME="your_wp_username"
export WP_APP_PASSWORD="your_application_password"
```

## Использование

```python
from Adapters.WordPress.adapter import WordPressAdapter

adapter = WordPressAdapter(config_path="Adapters/WordPress/config.yaml")

# Read
page = adapter.get_page(14)

# Update content (dry run)
adapter.update_page(
    page_id=14,
    updates={"content": "<p>New content</p>"},
    dry_run=True,
    task_id="LAOS-009-TEST-001"
)

# Update content (real)
adapter.update_page(
    page_id=14,
    updates={"content": "<p>New content</p>"},
    approved=True,
    task_id="LAOS-009-TEST-001"
)
```

## Архитектура

```
Adapters/WordPress/
├── adapter.py          # Основной класс
├── config.yaml         # Конфигурация (без credentials)
├── config.example.yaml # Пример конфигурации
├── requirements.txt    # Зависимости
├── README.md           # Этот файл
└── tests/              # Тесты
    ├── test_adapter.py
    └── test_rollback.py
```

## Безопасность

- Credentials не хранятся в репозитории.
- Application Passwords — лучшая практика для REST API.
- Минимальные права: `editor` или кастомная роль с правами `edit_pages`, `publish_pages`.
- Все операции логируются в `Audit/WordPress/operations.log`.
- Backup страниц хранятся в `Audit/WordPress/Backups/`.

## Лицензия

Использование только в рамках laser178.ru.
