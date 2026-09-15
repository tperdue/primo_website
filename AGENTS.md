# Agent Instructions

This repository is a day-zero starter scaffold for a CodeIgniter 4 full-stack PHP project. Keep changes conservative, framework-native, and easy for a new project team to override.

## Primary Context

- Read `.agents/agents.md` first for the portable workspace agent profile.
- Read `ARCHITECTURE.md` before changing application structure or adding new layers.
- Read `DESIGN.md` before adding or changing user-facing views, CSS, or interaction patterns.
- Treat `.agents/memories/*.md` as persistent project memory. Update the relevant memory file when an architectural, security, workflow, or design decision changes.
- Read `.agents/memories/development-progress.md` before starting meaningful work and update it at handoff with completed work, blockers, next actions, and verification.

## Memory Use

- Use topic-specific memories for durable facts and decisions.
- Use `development-progress.md` for current status and handoff notes.
- Do not store secrets, credentials, private data, or guesses as memory.
- Replace day-zero placeholders once the generated project makes a real choice.

## Project Shape

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

## Agent Workflow

- Preserve user changes. Do not reset or discard unrelated work.
- Keep commits and changes atomic when asked to commit.
- When implementing a feature, update tests or explain why tests are not appropriate yet.
- For security-sensitive work, explicitly check validation, authorization, CSRF, session/cookie settings, output escaping, file uploads, and SQL construction.
