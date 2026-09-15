---
kind: skill
id: codeigniter-feature-delivery
name: codeigniter-feature-delivery
description: Use when implementing a new feature in this CodeIgniter 4 starter, especially when touching routes, controllers, models, views, validation, migrations, services, or tests.
license: MIT
source: local
---

# CodeIgniter Feature Delivery

Use this project-local skill for end-to-end CI4 feature work.

## Workflow

1. Read `AGENTS.md`, `.agents/agents.md`, `ARCHITECTURE.md`, and relevant memories.
2. Identify the feature boundary:
   - Route: `app/Config/Routes.php`
   - Controller: `app/Controllers/`
   - Model: `app/Models/`
   - View: `app/Views/`
   - Migration/Seeder: `app/Database/`
   - Config/Filter: `app/Config/`
3. Prefer CodeIgniter generators when useful, then edit to match project conventions.
4. Keep controllers thin. Extract repeated or substantial logic into services/domain classes.
5. Add validation before persistence or side effects.
6. Add or update tests for the new behavior.
7. Run `composer test` or the narrowest relevant PHPUnit command.
8. Update memory files when the feature introduces a durable decision.

## CI4 Defaults

- Use explicit routes.
- Use `$this->request` for input.
- Use `return view(...)`, `return redirect()->to(...)`, or response helpers intentionally.
- Use model `$allowedFields` for mass assignment.
- Use query builder or bound parameters for custom SQL.
- Escape output in views with `esc()`.

## Completion Checklist

- [ ] Route/controller behavior is clear.
- [ ] Request input is validated.
- [ ] Output is escaped.
- [ ] Persistence uses models/query builder safely.
- [ ] Tests cover the behavior or a limitation is documented.
- [ ] `ARCHITECTURE.md` or memory files are updated if needed.
