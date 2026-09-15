---
id: ci4_starter_data_persistence
importance: high
tags: data, database, migrations, models
title: Data Persistence
---

# Data Persistence

Use this memory to track database and persistence decisions. Update it when the project chooses a database, adds migrations, changes model conventions, or introduces retention/backfill rules.

## Current Status

No application database has been selected at scaffold time.

## Database Choice

| Setting | Value |
| --- | --- |
| Engine | TBD |
| Driver | TBD |
| Local database name | TBD |
| Test database strategy | TBD |

## Model Conventions

Default until changed:

- Use CodeIgniter models for table-backed persistence.
- Define narrow `$allowedFields` for every model accepting inserts or updates.
- Use model validation rules when they are reusable and table-specific.
- Use query builder or bound parameters for custom queries.
- Prefer timestamps when records need auditability.
- Prefer soft deletes only when restore/audit behavior is required.

## Migration Conventions

- Add migrations under `app/Database/Migrations/`.
- Keep migrations reversible when practical.
- Name migrations after the user-visible/data concept being introduced.
- Add seeders under `app/Database/Seeds/` for deterministic development/test fixtures.

## ID Strategy

TBD. Decide per generated project.

Options to document when chosen:

- Auto-increment integer IDs.
- UUID/ULID public identifiers.
- Separate internal and public identifiers.

## Seed Data

No seed data is defined yet.

## Retention And Deletion

No retention or deletion policy is defined yet.

## Open Questions

- Which database engine will the generated project use?
- Should public routes expose sequential IDs, slugs, UUIDs, or opaque identifiers?
- Which records need soft deletes, audit trails, or immutable history?
