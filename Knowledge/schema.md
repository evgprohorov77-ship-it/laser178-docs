# Knowledge Schema v1.0.0

## Правила

1. Каждая сущность — YAML-файл с обязательными полями.
2. Любой факт без источника `website`, `calculator_js`, `direct`, `conversation`, `memory_user_preference` должен иметь `source: needs_verification` или `inferred` и `verified: false`.
3. Все поля `value: "needs_verification"` блокируют генерацию контента.
4. Публикация утверждений с `verified: false` без маркировки `[needs verification]` запрещена.

## Обязательные поля корня

| Поле | Тип | Описание |
|------|-----|----------|
| id | string | Идентификатор сущности. |
| version | string | Версия схемы. |
| entity_type | string | Тип сущности. |
| status | string | draft / approved / deprecated. |
| verification | string | pending / partial / complete. |

## Шаблон поля "утверждение"

```yaml
value: "строка или значение"
verified: true | false
source: "website | calculator_js | direct | conversation | memory_user_preference | inferred | needs_verification"
```

Для списков:

```yaml
- value: "..."
  verified: false
  source: "needs_verification"
```

## Статусы verification

- `pending` — сущность ещё не проверена.
- `partial` — часть полей проверена, часть нет.
- `complete` — все поля проверены.

## Маркировка неподтверждённого контента

В сгенерированном тексте любое неподтверждённое утверждение должно сопровождаться:

```
[needs verification: источник не подтверждён]
```

## Валидатор

См. `Scripts/validate_knowledge.py`.
