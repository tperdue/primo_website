---
id: primo_graphic_designer_data_persistence
importance: high
tags: data, database, migrations, models
title: Data Persistence
---

# Data Persistence

Use this memory to track database and persistence decisions. Update it when the project chooses a database, adds migrations, changes model conventions, or introduces retention/backfill rules.

## Current Status

SQLite is the default database. Business settings are stored as one row with ID 1. Services use auto-increment IDs internally and stable unique slugs publicly. Shield manages its own user tables; package migrations run alongside app migrations.

## Database Choice

| Setting | Value |
| --- | --- |
| Engine | SQLite default; existing local `.env` overrides to MySQL |
| Driver | `SQLite3` default, configurable through `.env` |
| Local database name | Default `writable/primo.sqlite` (ignored by Git) |
| Test database strategy | In-memory SQLite via CI4 `tests` group |

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

Business settings use the singleton ID 1. Services use integer IDs for admin edits and immutable slugs for public URLs; renaming a service does not break existing links.

Other domain objects can choose their ID strategy when introduced.

## Seed Data

No seed data is defined yet.

Expected future persisted concepts:

- Business settings for deployment-specific identity, website, brand, quote defaults, files, and notifications.
- Services with public visibility, starting price, pricing visibility, images, quote questions, related services, and related portfolio work.
- Question groups and quote questions with field type, requirement, ordering, help text, placeholder, active/inactive state, and reusable service attachment.
- Portfolio projects with draft/published/featured status, gallery, related services, SEO metadata, and reusable placements.
- Quote requests with reference number, selected services, general fields, answers, uploaded files, status, and internal notes.
- Customers/clients with lightweight contact and relationship history.
- Quotes with numbers, expiration, flexible line items, totals, terms, deposits, statuses, and optional revision history.
- Projects, project files/messages, invoices, and payments in later phases.
- Pages/content, media assets, contact submissions, testimonials, FAQs, and optional blog posts.

## Retention And Deletion

No retention or deletion policy is defined yet.

Future policies must address customer quote uploads, media library files, contact submissions, quote history, customer records, project files, invoices, and logs. Customer uploads should not automatically be publicly accessible.

## Open Questions

- Which database engine will the generated project use?
- Should public routes expose sequential IDs, slugs, UUIDs, or opaque identifiers?
- Which records need soft deletes, audit trails, or immutable history?
- Which file storage strategy will separate private uploads from public media?
- Which quote/project records require revision history?
