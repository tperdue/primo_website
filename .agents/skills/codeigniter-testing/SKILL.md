---
kind: skill
id: codeigniter-testing
name: codeigniter-testing
description: Use when adding, updating, or debugging tests in this CodeIgniter 4 starter, including PHPUnit unit, feature, database, session, and regression tests.
license: MIT
source: local
---

# CodeIgniter Testing

Use this project-local skill for tests in this starter.

## Test Selection

- Unit test: pure services, value objects, helpers, and validation logic.
- Feature test: routes, controllers, responses, redirects, sessions, and views.
- Database test: models, query behavior, migrations, seeders, and persistence rules.
- Regression test: every fixed bug gets a focused failing-then-passing case where practical.

## Test Style

- Test behavior, not implementation details.
- Name tests after the behavior.
- Use Arrange, Act, Assert structure when the test is non-trivial.
- Prefer `assertSame()` over loose comparisons.
- Use named datasets for boundary cases.
- Keep tests isolated from order and shared mutable state.

## Commands

```bash
composer test
vendor/bin/phpunit
vendor/bin/phpunit tests/unit/HealthTest.php
```

## Completion Checklist

- [ ] The test would fail for the bug or missing behavior.
- [ ] The test is deterministic.
- [ ] Fixtures are local to the test or reset by the framework.
- [ ] The narrow test or full suite has been run, or the blocker is documented.
