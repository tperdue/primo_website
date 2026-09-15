---
id: ci4_starter_delivery_workflow
importance: medium
tags: workflow, testing, git, delivery
title: Delivery Workflow
---

Default delivery workflow for this starter:

1. Read the relevant instructions and memory files.
2. Inspect current code before editing.
3. Make the smallest framework-native change that satisfies the request.
4. Add or update tests for user-observable behavior.
5. Run `composer test` or `vendor/bin/phpunit` when feasible.
6. Summarize changed files, verification, and any skipped checks.

Commit hygiene when asked to commit:

- Keep commits atomic.
- Separate formatting-only changes from behavior changes.
- Use conventional commit types such as `feat`, `fix`, `test`, `docs`, `refactor`, and `chore`.
- Check staged diffs for secrets before committing.
