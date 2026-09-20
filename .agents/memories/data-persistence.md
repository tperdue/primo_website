---
id: primo_graphic_designer_data_persistence
importance: high
tags: data, database, migrations, models
title: Data Persistence
---

# Data Persistence

Use this memory to track database and persistence decisions. Update it when the project chooses a database, adds migrations, changes model conventions, or introduces retention/backfill rules.

## Current Status

SQLite is the default database. Business settings are stored as one row with ID 1. Services and portfolio projects use auto-increment IDs internally and stable unique slugs publicly. Reusable media records relate to service featured images, portfolio featured images, ordered project galleries, and project-service associations; deleting an in-use asset is restricted by both the application and database. Quote question groups own ordered questions and normalized choice options; explicit group-service rows make a group conditional, while a group with no service rows is general. The quote cart and draft answers remain transient session state. Explicit submission creates a durable quote request, immutable service and answer snapshots, and private file metadata under one transaction. Fixed-slug content pages store About, Contact, Privacy, and Terms copy plus SEO metadata. Contact submissions store inquiry and notification state but no raw IP address. Shield manages its own user tables; package migrations run alongside app migrations.

Qualified requests convert into one quote and one lightweight customer record. Customer identity matching uses trimmed, lowercased email stored on both customers and quote requests; conversion reuses an existing match and links previously unassigned requests with the same normalized email. Customer profiles hold current contact, optional address, and private notes, while quotes retain immutable customer contact snapshots, a unique human-readable quote number, a 256-bit public access token, line items, calculated totals, terms, delivery state, and version number. Every save replaces current line items transactionally and appends an immutable JSON revision; status changes also append history. Business settings provide default expiration days, terms, and deposit percentage. Customers may link to one Shield user through a nullable unique `user_id`; admin email edits synchronize the linked Shield email identity. `customer_portal_invitations` stores one replaceable hashed activation token per customer with expiry and use timestamps.

Quote responses store a response timestamp and optional bounded customer note. Status-history records identify admin, customer, or system actors. Accepted quotes convert once into projects through a unique quote relationship; projects have stable human-readable numbers, retain customer ownership, and store schedule, status, private notes, and customer-visible update text.

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

Business settings use the singleton ID 1. Services and portfolio projects use integer IDs for admin edits and immutable slugs for public URLs; renaming does not break existing links.

Other domain objects can choose their ID strategy when introduced.

## Seed Data

The content/contact migration inserts four deployment-neutral managed page records. About and Contact begin published; Privacy and Terms remain drafts until deployment-specific, reviewed legal language is entered.

Expected future persisted concepts:

- Business settings for deployment-specific identity, website, brand, quote defaults, files, and notifications.
- Services with public visibility, starting price, pricing visibility, images, quote questions, related services, and related portfolio work.
- Portfolio projects with draft/published/featured status, gallery, related services, SEO metadata, and reusable placements.
- Quote requests with reference number, selected services, general fields, answers, uploaded files, status, and internal notes.
- Customers/clients with lightweight contact and relationship history.
- Quotes with numbers, expiration, flexible line items, totals, terms, deposits, statuses, and optional revision history.
- Projects, project files/messages, invoices, and payments in later phases.
- Pages/content, media assets, contact submissions, testimonials, FAQs, and optional blog posts.

## Retention And Deletion

No retention or deletion policy is defined yet. Customer deletion is not exposed because requests and quotes form durable relationship and commercial history; define archival and retention behavior before adding destructive customer actions.

New media files are stored under ignored `public/uploads/media/` and are intentionally public; legacy `public/uploads/portfolio/` paths remain valid. Replacing a selected image does not delete its reusable media record or file. An asset can be deleted only after all service, featured-project, and gallery references are removed. Backups must include the database and both public upload directories when present.

Future policies must address contact-submission retention, customer quote uploads, media library files, quote history, customer records, project files, invoices, and logs. Customer uploads should not automatically be publicly accessible.

Unsubmitted quote-cart state follows the configured session lifetime and is not part of database backups. Submitted customer files live under `writable/uploads/quote_requests/{reference}/`; backups and retention procedures must cover this private directory together with the database.

## Open Questions

- Which database engine will the generated project use?
- Should public routes expose sequential IDs, slugs, UUIDs, or opaque identifiers?
- Which records need soft deletes, audit trails, or immutable history?
- Which file storage strategy will separate private uploads from public media?
- Which quote/project records require revision history?
- Define quote/customer retention before production launch. Proposal bearer tokens currently remain stable after a final response so customers can retain read-only access to the accepted or declined commercial record.
