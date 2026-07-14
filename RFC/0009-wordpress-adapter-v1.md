# RFC-0009: WordPress Adapter v1

## Статус

Accepted

## ID

LAOS-009

## Автор

Hermes

## Дата

2026-07-14

## Контекст

LAOS-008A Site Launch Mode завершается. В рамках подготовки к постоянному управлению контентом laser178.ru без ручного вмешательства в браузер или эмуляции действий пользователя требуется адаптер, работающий через WordPress REST API.

## Цель

Создать безопасный WordPress Adapter, который позволяет:

- Читать страницы (GET).
- Обновлять контент, title, meta description, excerpt, featured image (UPDATE).
- Создавать черновики (CREATE draft).
- Публиковать утверждённые черновики (PUBLISH approved draft).
- Обновлять FAQ-блоки.
- Обновлять внутренние ссылки.

## Запрещённые операции (hard deny)

- DELETE (любых сущностей).
- UPDATE plugins.
- UPDATE theme.
- UPDATE menus.
- UPDATE users.
- UPDATE roles.
- UPDATE robots.txt.
- UPDATE PHP / wp-config / `.htaccess`.
- UPDATE site options.
- UPDATE permalinks.
- UPDATE settings.
- CREATE users.
- CREATE roles.
- CREATE plugins/themes.
- DELETE media.

## Разрешённые операции (allow)

| Operation | Method | Endpoint | Notes |
|-----------|--------|----------|-------|
| GET page | GET | `/wp-json/wp/v2/pages/{id}` | Read-only |
| GET pages | GET | `/wp-json/wp/v2/pages` | Read-only |
| UPDATE page content | POST | `/wp-json/wp/v2/pages/{id}` | Только `content` |
| UPDATE title | POST | `/wp-json/wp/v2/pages/{id}` | Только `title` |
| UPDATE meta description | POST | `/wp-json/wp/v2/pages/{id}` | Только через Yoast/ACF/meta поля |
| UPDATE excerpt | POST | `/wp-json/wp/v2/pages/{id}` | Только `excerpt` |
| UPDATE featured image | POST | `/wp-json/wp/v2/pages/{id}` | Только `featured_media` |
| CREATE draft | POST | `/wp-json/wp/v2/pages` | `status: draft` |
| PUBLISH approved draft | POST | `/wp-json/wp/v2/pages/{id}` | `status: publish`, только если `approved: true` |
| UPDATE FAQ block | POST | `/wp-json/wp/v2/pages/{id}` | Только внутри `content` |
| UPDATE internal links | POST | `/wp-json/wp/v2/pages/{id}` | Только внутри `content` |

## Каждый UPDATE обязан

1. **Backup** — сохранить текущее состояние страницы в `Audit/WordPress/Backups/{task_id}/page_{id}_{timestamp}.json`.
2. **Dry Run** — сформировать payload и вывести diff, не отправляя в WP.
3. **Verification** — после обновления запросить страницу и сравнить с ожидаемым.
4. **Clear Cache** — вызвать сброс кэша (если настроен endpoint/purge plugin) или инструкцию.
5. **HTML Check** — проверить, что HTML содержит title, h1, не пуст.
6. **HTTP Check** — HEAD/GET страницы, статус 200.
7. **SEO Check** — проверить title, meta description, canonical.
8. **Rollback** — если любая проверка не прошла, восстановить страницу из backup.

## Логирование

Каждая операция журналируется в `Audit/WordPress/operations.log` (line-delimited JSON) со следующими полями:

- `task_id`
- `page_id`
- `operation`
- `backup_id`
- `dry_run` (bool)
- `verification_result` (bool)
- `rollback_status` (none/attempted/success/failed)
- `duration_ms`
- `operator` (who triggered)
- `timestamp` (ISO 8601)
- `status` (success/error/rolled_back)
- `error` (optional)
- `diff_summary` (optional)

## Безопасность

- Credentials (логин, пароль, application password) не хранятся в репозитории.
- Конфигурация подключения в `Adapters/WordPress/config.yaml` с плейсхолдерами.
- Credentials передаются через environment variables или secrets manager.
- Production Lock активен по умолчанию: любое обновление требует `approved: true`.
- Capability matrix в `Security/WordPress/permissions.yaml`.

## Критерии приёмки

- [ ] Adapter может получить страницу по ID.
- [ ] Adapter может обновить content/title/excerpt/featured_media с backup и rollback.
- [ ] Adapter может создать draft.
- [ ] Adapter может опубликовать draft, только если `approved: true`.
- [ ] При ошибке любой проверки происходит rollback.
- [ ] Все операции записываются в audit log.
- [ ] Unit-тесты на dry-run и rollback.
- [ ] Интеграционный тест на локальном WordPress (не production).

## Риски

- REST API может быть отключён или кэширован на уровне сервера.
- Yoast meta description может требовать отдельного endpoint/плагина.
- Elementor хранит контент в `post_meta`, а не в `content.raw`, что усложняет UPDATE.
- Неправильный payload может повредить страницу — поэтому backup и rollback обязательны.

## Зависимости

- Python 3.11+
- `requests`
- `pyyaml`
- `python-dotenv` (опционально)
- Тестовый стенд WordPress с включённым REST API и Application Passwords.

## Роадмап

- v1.0: MVP с разрешёнными операциями и rollback.
- v1.1: Пакетные операции (batch update).
- v1.2: Поддержка posts и custom post types.
- v1.3: Интеграция с Content Scheduler и Approval Workflow.
- v2.0: Production access после Security Layer и аудита.

## Связанные файлы

- `Adapters/WordPress/README.md`
- `Adapters/WordPress/config.yaml`
- `Adapters/WordPress/adapter.py`
- `Security/WordPress/permissions.yaml`
- `Audit/WordPress/operations.log`
- `Audit/WordPress/README.md`
