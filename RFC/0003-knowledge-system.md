# RFC-0003: Knowledge System

## Статус

Approved

## Автор

Hermes LAOS

## Дата

2026-07-13

## Краткое описание

Создать единую корпоративную базу знаний (Knowledge DB) на основе YAML-файлов с валидируемой структурой. Все сущности компании, услуг, материалов, цен, гарантий, FAQ, автомобилей и оборудования должны быть описаны в Knowledge. Любой контент и статьи LAOS должны ссылаться на Knowledge и проверяться на соответствие. Неподтверждённые утверждения помечаются `needs_verification`.

## Мотивация

LAOS Core заморожен. Следующий этап развития — не создание Engine, а накопление достоверных данных о компании. Без корректной базы знаний любой сгенерированный контент будет содержать ошибки, что недопустимо для коммерческого сайта.

## Предлагаемое решение

1. Использовать единый YAML-шаблон для всех сущностей.
2. Обязательные поля: `id`, `version`, `entity_type`, `status`, `verification`.
3. Каждое утверждение — объект с `value`, `verified`, `source`.
4. `source` может быть: `website`, `calculator_js`, `direct`, `conversation`, `memory_user_preference`, `inferred`, `needs_verification`.
5. Создать `Scripts/validate_knowledge.py` для проверки структуры.
6. Создать `Scripts/check_content.py` для проверки статей на соответствие Knowledge.
7. Все неподтверждённые утверждения в контенте маркировать `[needs verification]`.

## Затронутые компоненты

- `Knowledge/Company/`
- `Knowledge/schema.md`
- `Scripts/validate_knowledge.py`
- `Scripts/check_content.py`

## Что не делаем

- Не создаём новых Engine.
- Не меняем LAOS Core.
- Не подключаем WordPress Adapter.
- Не выполняем реальных изменений на сайте.

## Риски

- Замедление генерации контента из-за необходимости verify.
- Увеличение объёма репозитория.

## Митигация

- Валидатор запускается перед каждым коммитом.
- Владелец проверяет только `verified: false` поля.
- Можно утверждать сущности пакетно.

## Статус реализации

Implemented.

## Проверка

```bash
python3 Scripts/validate_knowledge.py
python3 Scripts/check_content.py path/to/article.md
```
