# Laser178 AI Operating System (LAOS)

AI-агент для управления сайтом `laser178.ru`. Работает как команда специалистов: технический директор, SEO-специалист, редактор, контент-менеджер, WordPress-разработчик, QA-инженер, веб-мастер, аналитик.

## Архитектура: LAOS Kernel

LAOS теперь строится как операционная система:

| Компонент | Роль |
|-----------|------|
| Decision Engine | Процессор |
| Policy | Оперативная память / правила |
| Knowledge | Файловая система |
| Authorization | Служба безопасности |
| Action Engine | Драйверы |
| Adapters | Внешние устройства (WordPress — один из них) |
| Auditors | Сенсоры |
| Logs | Журнал событий |

Главный поток:

```
Audit → Decision → Authorization → Dry Run → Approval → Backup → Action → Verification → Logging → Issue (fallback)
```

Подробнее в [`ARCHITECTURE.md`](ARCHITECTURE.md) и [`AI/Decision/AI_DECISION_ENGINE.md`](AI/Decision/AI_DECISION_ENGINE.md).

## Быстрый старт

```bash
python3 Scripts/run_laos.py
```

## Структура

| Директория | Назначение |
|------------|------------|
| `AI/Authorization/` | Authorization Engine — можно ли действовать? |
| `AI/Approval/` | Owner Approval System с UUID и статусами. |
| `AI/Backup/` | Backup Engine — алгоритм резервного копирования. |
| `AI/Decision/` | Decision Engine — центр принятия решений. |
| `AI/DryRun/` | Dry Run Engine — имитация перед изменением. |
| `AI/Engines/` | Risk, Action, Verification, Logger. |
| `AI/Models/` | `Finding`, `Decision`, `ExecutionContext`. |
| `Auditors/` | SEO, структура, изображения, производительность, безопасность, WordPress. |
| `Capabilities/` | Capability System — разрешённые возможности агента. |
| `Environment/` | development / staging / production + current. |
| `Framework/` | Правила, роли, чек-листы. |
| `Knowledge/Company/` | База знаний, разбитая по доменам. |
| `Policies/` | Policy Layer: authorization, autofix, backup, verification, logging, production, dry run, human approval. |
| `Registry/` | Реестр правил. |
| `RFC/` | Request for Comments — любое архитектурное изменение. |
| `Scripts/` | Entrypoints. |
| `Logs/` | Логи выполнения. |

## Ключевые принципы

1. **WordPress REST API не используется.** См. `RFC/0002-wordpress-adapter.md`.
2. **Реальные изменения на сайт не выполняются.** В MVP все действия — план/имитация.
3. **Production Lock активен.** Автоматические изменения в production запрещены.
4. **Capability controls.** Агент может делать только то, что разрешено.
5. **Authorization first.** Любое действие проходит Authorization Engine.
6. **Dry Run mandatory.** Перед изменением показываем владельцу, что произойдёт.
7. **Backup mandatory.** Backup Engine перед любым действием.
8. **Owner Approval.** P0/P1 и production требуют explicit approval.
9. **Verification + rollback.** После действия проверяем, при сбое откатываем.
10. **RFC process.** Любой новый Engine или архитектурное изменение — через RFC.
11. **Knowledge first.** Любой контент ссылается на Knowledge. Неподтверждённые утверждения маркируются `[needs verification]`.
12. **LAOS Core frozen.** Новые Engine только через RFC. Текущий этап — наполнение Knowledge.

## Статус

LAOS Kernel v2.0. Все Policy Layer и Engine созданы, но WordPress Adapter и реальные изменения заблокированы до завершения Security Layer.

## Лицензия

Внутренний проект laser178.ru.
