# Knowledge

## Структура

База знаний разбита на YAML-файлы по доменам. Каждый файл валидируется через `Scripts/validate_knowledge.py`.

| Файл | Содержание |
|------|------------|
| `schema.md` | Knowledge Schema v1.0.0 |
| `Company/template.yaml` | Единый шаблон для новых сущностей |
| `Company/company.yaml` | Общие сведения о компании |
| `Company/contacts.yaml` | Контакты и адреса |
| `Company/employees.yaml` | Сотрудники и роли |
| `Company/services.yaml` | Услуги и пакеты |
| `Company/materials.yaml` | Материалы и ценообразование |
| `Company/equipment.yaml` | Оборудование |
| `Company/prices.yaml` | Прайсы и коэффициенты |
| `Company/faq.yaml` | FAQ и возражения |
| `Company/guarantees.yaml` | Гарантии |
| `Company/partners.yaml` | Партнёры и поставщики |
| `Company/cars.yaml` | Целевые модели автомобилей |

## Правила

1. Каждое утверждение — объект с `value`, `verified`, `source`.
2. `source` может быть: `website`, `calculator_js`, `direct`, `conversation`, `memory_user_preference`, `inferred`, `needs_verification`, `website_partial`.
3. Если `verified: false` и `source: needs_verification`, значение нельзя публиковать без одобрения.
4. Все YAML-файлы должны проходить валидацию.
5. Любой контент перед публикацией проверяется через `Scripts/check_content.py`.

## Валидация

```bash
python3 Scripts/validate_knowledge.py
```

## Проверка контента

```bash
python3 Scripts/check_content.py path/to/article.md
```

## Статус

LAOS Core заморожен. Текущий этап — наполнение и верификация Knowledge DB.

## Преимущества разделения

- Меньше конфликтов при редактировании.
- Проще искать.
- Можно обновлять отдельные разделы без затрагивания всего файла.
- LAOS может загружать только нужный контекст.
- Валидация гарантирует, что неподтверждённые утверждения не попадут в контент.
