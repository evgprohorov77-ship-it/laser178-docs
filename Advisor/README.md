# External Advisor Mode v1

Безопасный режим консультации Hermes Agent с внешней LLM (OpenAI) через Responses API.

## Назначение

Advisor выступает как **внешний рецензент** для SEO, UX, контента, кода и стратегических решений. Он:

- не имеет доступа к инструментам, серверам, файлам или WordPress;
- получает только текст задачи и санитизированный контекст;
- возвращает структурированную рекомендацию в JSON;
- не может одобрить Production Action — только посоветовать.

Окончательное решение принимает Hermes в рамках Policy Layer и Owner Approval.

## Структура

```
Advisor/
├── advisor.py                    # Основной модуль
├── config.example.yaml           # Пример конфигурации
├── requirements.txt              # Зависимости Python
├── README.md                     # Этот файл
├── Dry-Run-and-Security-Review.md # Отчёты
├── examples/
│   └── UX-Review-laser178-mobile-bar.md  # Пример UX-ревью для laser178.ru
├── prompts/
│   ├── seo_review.md
│   ├── ux_review.md
│   ├── content_review.md
│   ├── code_review.md
│   └── decision_review.md
├── schemas/
│   └── advisor_response.schema.json
└── tests/
    ├── conftest.py
    ├── test_advisor.py
    └── test_sanitization.py
```

## Установка

```bash
cd Advisor
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

## Конфигурация

Скопируйте `config.example.yaml` в корень проекта и переименуйте в `config.yaml` (или укажите свой путь):

```bash
cp config.example.yaml ../../config.yaml
```

По умолчанию Advisor **выключен**:

```yaml
advisor:
  enabled: false
  model: "gpt-4.1-nano"
  max_context_chars: 8000
  max_output_tokens: 2048
  daily_request_limit: 50
  timeout: 30
```

## API-ключ

Ключ берётся **только из переменной окружения**:

```bash
export OPENAI_API_KEY="sk-..."
```

**Запрещено:**

- хранить ключ в коде;
- хранить ключ в YAML;
- коммитить `.env`;
- выводить ключ в логи;
- передавать ключ через Telegram или другие мессенджеры.

## Использование

```python
from Advisor.advisor import Advisor, AdvisorConfig

config = AdvisorConfig("/path/to/config.yaml")
advisor = Advisor(config)

result = advisor.consult(
    task_id="LAOS-001",
    review_type="ux_review",
    question="Стоит ли добавить фиксированную нижнюю панель на мобильной версии?",
    proposed_solution="Добавить панель с кнопками Позвонить, Telegram, MAX.",
    sanitized_context="Сайт laser178.ru, тёмная тема, мобильный трафик.",
    risk_level="MEDIUM",
)

print(result["verdict"])  # approve | revise | reject | insufficient_context
print(result["recommendations"])
```

## Режимы review

| Режим | Назначение |
|-------|------------|
| `seo_review` | SEO-структура, мета-теги, заголовки, переспам |
| `ux_review` | UX, дизайн, accessibility, мобильная версия |
| `content_review` | Публичные тексты, кейсы, блог-посты |
| `code_review` | Код, конфигурации, безопасность |
| `decision_review` | Стратегические решения, приоритеты, планы |

## Когда вызывать Advisor

Автоматически предлагать консультацию для:

- изменений главной страницы;
- SEO-структуры;
- публичных текстов;
- UX и дизайна;
- новых WordPress-плагинов;
- задач с `risk_level: HIGH`;
- решений, где уверенность Hermes ниже 0.75.

Для LOW-risk технических операций Advisor не вызывать.

## Защита данных

Перед отправкой контекста выполняется sanitization:

- удаляются API-ключи, пароли, токены, cookie;
- удаляются email и телефоны;
- удаляются env-файлы и URL с секретами;
- контекст обрезается до `max_context_chars`.

В логи записываются только:

- task_id;
- review_type;
- model;
- verdict;
- confidence;
- tokens (input/output) и ошибка (если есть).

Полный приватный контекст в логи не попадает.

## Отказоустойчивость

При любой ошибке API (timeout, rate limit, authentication, invalid JSON) Advisor возвращает:

```json
{
  "verdict": "advisor_unavailable",
  "summary": "Advisor unavailable: <reason>. Hermes must proceed according to Policy Layer.",
  "strengths": [],
  "issues": ["<reason>"],
  "recommendations": ["Request owner decision if risk is HIGH."],
  "risks": ["No external review was obtained."],
  "confidence": 0.0,
  "requires_owner_decision": true
}
```

Для HIGH-risk задач `requires_owner_decision` всегда `true` при недоступности Advisor.

## Тестирование

```bash
cd Advisor
source .venv/bin/activate
python3 -m pytest tests/ -v
```

Все тесты проходят без реального `OPENAI_API_KEY` (используются моки).

## Пример

См. `examples/UX-Review-laser178-mobile-bar.md`.

## Ограничения

- Advisor только рекомендует. Он не может изменить сайт, сделать коммит, отправить письмо или установить плагин.
- Advisor не заменяет Owner Approval для Production Actions.
- Advisor не подключён к production-процессу автоматически — включение требует явного решения Owner.
