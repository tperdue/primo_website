---
id: primo_graphic_designer_delivery_workflow
importance: medium
tags: workflow, testing, git, delivery
title: Delivery Workflow
---

Default delivery workflow for this product:

1. Read the relevant instructions and memory files.
2. Read `docs/developer_handoff.md` when planning product scope, domain objects, phases, or UI/admin behavior.
3. Inspect current code before editing.
4. Make the smallest framework-native change that satisfies the request.
5. Keep deployment-specific designer content configurable rather than hard-coded.
6. Add or update tests for user-observable behavior.
7. Run `composer test` or `vendor/bin/phpunit` when feasible.
8. Summarize changed files, verification, and any skipped checks.

Production delivery:

- Pushes to `main` run Composer validation and the no-coverage PHPUnit suite before deployment.
- GitHub Actions invokes a forced-command SSH key; server-side release logic remains in `deploy/deploy-production.sh`.
- Production releases must retain the shared environment, writable data, and public media uploads.
- Back up MySQL immediately before migrations and never reverse migrations automatically during code rollback.
- Confirm `/` and `/login` over HTTPS after switching the live symlink.

Phase discipline:

- Phase 1: application foundation, admin authentication, business settings, public site, responsive layout, SEO foundations.
- Phase 2: content management, service/portfolio management, media library, quote cart/builder, configurable questions, uploads, notifications, quote-request admin.
- Phase 3: customer records, quote construction, line items, pricing, quote terms/statuses, delivery, revisions if needed.
- Phase 4: lightweight customer portal.
- Phase 5: invoices and payments.

Do not expand early implementation into a full CRM, accounting system, advanced project-management application, time tracker, team resource planner, email marketing system, or drag-and-drop page builder.

Commit hygiene when asked to commit:

- Keep commits atomic.
- Separate formatting-only changes from behavior changes.
- Use conventional commit types such as `feat`, `fix`, `test`, `docs`, `refactor`, and `chore`.
- Check staged diffs for secrets before committing.
