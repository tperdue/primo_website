# Agent Instructions

This repository is becoming a reusable CodeIgniter 4 application for independent graphic designers. Keep changes conservative, framework-native, configurable by deployment, and aligned with the product brief in `docs/developer_handoff.md`.

## Primary Context

- Read `.agents/agents.md` first for the portable workspace agent profile.
- Read `ARCHITECTURE.md` before changing application structure or adding new layers.
- Read `DESIGN.md` before adding or changing user-facing views, CSS, or interaction patterns.
- Read `docs/developer_handoff.md` before planning product scope, domain modeling, admin features, quote workflows, customer portal work, or project phases.
- Treat `.agents/memories/*.md` as persistent project memory. Update the relevant memory file when an architectural, security, workflow, or design decision changes.
- Read `.agents/memories/development-progress.md` before starting meaningful work and update it at handoff with completed work, blockers, next actions, and verification.

## Memory Use

- Use topic-specific memories for durable facts and decisions.
- Use `development-progress.md` for current status and handoff notes.
- Do not store secrets, credentials, private data, or guesses as memory.
- Replace day-zero placeholders once the generated project makes a real choice.

## Product Shape

- Product: reusable PHP / CodeIgniter application for solo graphic designers and very small design businesses.
- Primary workflow: visitor browses services and portfolio, adds services to a quote request, answers configurable questions, uploads supporting material, submits a request, and the designer reviews, qualifies, builds, and sends an actual quote.
- Treat business settings, services, question groups, questions, quote requests, quotes, customers, portfolio entries, projects, media, pages, testimonials, FAQs, and blog posts as configurable domain objects rather than hard-coded page content.
- Public quote requests must not require a customer account.
- The customer portal is a later phase and should not be built before the public website, quote system, and admin portal are stable.
- Avoid growing the initial app into a full CRM, accounting platform, advanced project-management system, time tracker, email marketing system, or drag-and-drop page builder.

## Project Structure

- Framework: CodeIgniter 4 via `codeigniter4/framework`.
- Runtime baseline: PHP `^8.2` from `composer.json`.
- Web root: `public/`; never expose the project root as a document root.
- App code belongs under `app/`; generated runtime output belongs under `writable/`.
- Tests live under `tests/` and use the CodeIgniter/PHPUnit test harness.

## Build And Test

- Install dependencies with `composer install`.
- Run tests with `composer test` or `vendor/bin/phpunit`.
- Run the local app with `php spark serve`.
- Prefer CodeIgniter generators where they match the requested artifact, then adjust by hand.

## Coding Rules

- Follow existing CodeIgniter 4 conventions: namespaced controllers, explicit routes, framework services, filters, models, migrations, and validation.
- Use `$this->request`, validation rules, query builder/model methods, and bound parameters. Do not read raw `$_POST`, `$_GET`, or concatenate SQL.
- Escape output in views with `esc()` and use the correct context such as `esc($value, 'attr')` for attributes.
- CI4 models must define `$allowedFields` before accepting mass-assigned input.
- Keep scaffolding minimal. Add abstractions only when they clarify a real feature.
- Keep tenant/deployment-specific business identity, contact details, brand colors, services, quote questions, portfolio, pricing visibility, policies, notifications, and website content configurable.
- Store customer uploads outside publicly browsable paths unless a file is explicitly intended to be public.

## Agent Workflow

- Preserve user changes. Do not reset or discard unrelated work.
- Do not start implementation when the user asks only for planning, documentation, design-system extraction, or memory setup.
- Keep commits and changes atomic when asked to commit.
- When implementing a feature, update tests or explain why tests are not appropriate yet.
- For security-sensitive work, explicitly check validation, authorization, CSRF, session/cookie settings, output escaping, file uploads, and SQL construction.
