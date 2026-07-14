# Audit Log — WordPress Adapter

## Назначение

Здесь хранятся:

- `operations.log` — журнал всех операций (line-delimited JSON).
- `Backups/` — резервные копии страниц перед UPDATE.

## Структура операции в логе

```json
{
  "task_id": "LAOS-009-001",
  "page_id": 14,
  "operation": "UPDATE_PAGE_CONTENT",
  "backup_id": "Audit/WordPress/Backups/LAOS-009-001/page_14_2026-07-14T12:00:00Z.json",
  "dry_run": false,
  "verification_result": true,
  "rollback_status": "none",
  "duration_ms": 1250,
  "operator": "Hermes",
  "timestamp": "2026-07-14T12:00:05Z",
  "status": "success",
  "error": null,
  "diff_summary": {
    "content_changed": true,
    "old_title": "Old Title",
    "new_title": "New Title"
  }
}
```

## Правила

- Не редактировать лог вручную.
- Файлы backup не удалять без owner approval.
- operations.log — append-only.
- Логи хранятся в git, но не содержат credentials.

## Backup ID

Формат: `{backup_dir}/{task_id}/page_{page_id}_{timestamp}.json`

Пример: `Audit/WordPress/Backups/LAOS-009-001/page_14_2026-07-14T12:00:00Z.json`
