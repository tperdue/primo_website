---
kind: agents
---

# CodeIgniter Starter Agent Guidelines

This workspace contains a portable `.agents/` setup for a day-zero CodeIgniter 4 full-stack PHP scaffold. Use it as the project-specific layer above global agent defaults.

## Read First

1. `AGENTS.md` for broad coding-agent compatibility.
2. `ARCHITECTURE.md` for the current system map.
3. `DESIGN.md` before user-facing UI work.
4. The relevant `.agents/memories/*.md` files for persistent project decisions, current progress, and handoff notes.

## Memory Files

Memory files are the durable context layer for future agents. Read them before making assumptions, and update them when your work changes a decision, workflow, current status, or handoff state.

Use memories this way:

- Read only the memories relevant to the task, plus `development-progress.md` for current state.
- Keep memory entries factual, concise, and date-aware when timing matters.
- Separate durable decisions from temporary progress. Architecture and policy memories should be stable; `development-progress.md` should change often.
- Do not record secrets, credentials, personal data, or speculative guesses as facts.
- When a generated project makes a real choice, replace the day-zero placeholder with the decision, rationale, and consequences.
- At the end of meaningful work, update `development-progress.md` with completed work, blockers, next actions, and verification.

Memory file ownership:

- `project-architecture.md`: stable system shape, framework boundaries, and architectural decisions.
- `security-baseline.md`: security defaults, changed threat assumptions, and required protections.
- `delivery-workflow.md`: how work is planned, implemented, verified, committed, and handed off.
- `design-baseline.md`: UI/design defaults and chosen frontend design constraints.
- `design-system-workflow.md`: design-system source, extraction/adoption status, token files, and workflow rules.
- `development-progress.md`: active phase, current work, completed items, blockers, next actions, and last verification.
- `domain-model.md`: business vocabulary, entities, lifecycle states, ownership rules, and invariants.
- `data-persistence.md`: database choice, migrations, model conventions, ID strategy, seed data, and retention rules.
- `auth-and-access-control.md`: authentication provider, roles, permissions, protected routes, and authorization patterns.
- `api-contracts.md`: JSON/API conventions, response envelopes, errors, pagination, and versioning.
- `deployment-runtime.md`: hosting target, runtime requirements, environment variables, jobs, logging, backups, and operational constraints.
- `quality-gates.md`: required checks before handoff, including tests, static analysis, formatting, security scans, and coverage expectations.

Update examples:

- Adding the first database table: update `data-persistence.md` and, if it changes structure, `project-architecture.md`.
- Adding login or protected routes: update `auth-and-access-control.md` and `security-baseline.md`.
- Adding an API endpoint: update `api-contracts.md`.
- Changing deployment assumptions: update `deployment-runtime.md`.
- Finishing a work session: update `development-progress.md`.

## Installed Skills

Use the existing installed skills when their trigger matches the task:

- `codeigniter` for CodeIgniter 4 controllers, routing, models, views, filters, migrations, services, events, CLI commands, and testing.
- `php-best-practices` and `php-pro` for modern PHP 8.2+ implementation.
- `phpunit` and `tdd` for test design and PHPUnit work.
- `php-security-patterns` and `owasp-security` for security-sensitive work.
- `architecture-patterns` and `improve-codebase-architecture` for structural changes.
- `htmx`, `alpine-js`, `css-architecture`, and `extract-design-system` when the generated project chooses those frontend paths.
- `context7-mcp` when current upstream framework documentation would materially improve accuracy.

Additional local project skills installed in this workspace:

- `codeigniter-feature-delivery`
- `codeigniter-security-review`
- `codeigniter-testing`
- `codeigniter-design-system-workflow` for extracting, reviewing, documenting, and applying design-system tokens in the right order.
- `relume-codeigniter-ui-converter` for converting Relume React/TSX components into CodeIgniter 4 PHP views and partials.
- `website-style-emulation-workflow` for emulating a public website's visual style and UI patterns while avoiding copied brand assets, content, or React implementation.

## Suggested Future Skills

Install or add these when a generated project needs them:

- A database-specific skill such as MySQL, PostgreSQL, or Supabase once persistence is selected.
- A deployment-specific skill such as Render, Docker, Nginx, or Linux administration once hosting is selected.
- Provider/tool-specific design-system skills once the project has a real brand or component library beyond the local workflow.
- An authentication-specific skill if the project adopts CodeIgniter Shield or an external identity provider.

## Default Working Agreement

- Identify whether a request affects framework behavior, security, persistence, UI, tests, or agent configuration.
- Keep scaffold changes generic and reusable unless the user says this is for a specific app.
- Prefer CI4-native mechanisms over custom framework wrappers.
- Update memory files when a decision becomes true for future agents.
- Do not introduce build tools, JavaScript frameworks, authentication, queues, or database dependencies until the generated project asks for them.

## Verification Defaults

- For PHP changes, run `composer test` or `vendor/bin/phpunit` when feasible.
- For routing/controller changes, include at least a feature test when behavior is user-observable.
- For security-sensitive changes, verify validation, authorization, CSRF, output escaping, and query construction.
- For UI changes, inspect the rendered page when a dev server/browser is available.
