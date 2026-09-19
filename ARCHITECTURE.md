# Architecture Overview

This is a living architecture file for a reusable CodeIgniter 4 application serving independent graphic designers. The product direction is defined by `docs/developer_handoff.md`.

## 1. Project Structure

```text
[Project Root]/
├── app/
│   ├── Config/          # Framework and application configuration
│   ├── Controllers/     # HTTP controllers and BaseController
│   ├── Language/        # Translation and validation language files
│   └── Views/           # Server-rendered PHP views
├── public/              # Web root; point the web server here
├── tests/               # PHPUnit and CodeIgniter test suites
├── vendor/              # Composer dependencies
├── writable/            # Logs, cache, sessions, uploads, debugbar output
├── .agents/             # Portable agent config, memories, skills, sub-agents
├── composer.json        # PHP dependencies and scripts
├── env                  # Environment template; copy to .env locally
├── phpunit.dist.xml     # Test configuration
└── spark                # CodeIgniter CLI
```

## 2. High-Level System Diagram

```text
[Browser/User]
    |
    v
[public/index.php]
    |
    v
[CodeIgniter Router]
    |
    +--> [Filters: CSRF/Auth/CORS/etc.]
    |
    v
[Controllers] --> [Services/Domain Classes]* --> [Models/Query Builder] --> [Database]*
    |
    +--> [Views] --> [HTML Response]
    |
    +--> [JSON Response/API]*
```

`*` marks layers that should be introduced only when the relevant phase needs them.

## 3. Core Components

### CodeIgniter Application

Name: Primo Graphic Designer Business Platform

Description: A reusable website and lightweight client-management application for solo graphic designers and very small design businesses.

Technologies: PHP 8.2+, CodeIgniter 4.7+, Composer, PHPUnit.

Deployment: To be decided per generated project. The web server must point to `public/`.

Product areas:

- Public marketing site for services, portfolio, about, contact, quote requests, and legal pages.
- Admin portal for business settings, quote requests, quotes, customers, services, question groups/questions, portfolio, media, pages/content, blog, testimonials, FAQs, contact submissions, and lightweight projects.
- Customer portal later, after the public site, quote system, and admin portal are stable.

### Controllers

Responsibility: Accept HTTP requests, coordinate validation and domain/application work, and return responses.

Rules: Keep controllers thin. Move repeated business logic into services or domain classes under `app/` when it becomes shared or substantial.

### Models And Persistence

Responsibility: Encapsulate database table access through CodeIgniter models, entities, migrations, seeders, or query builders.

Rules: Use `$allowedFields`, validation rules, timestamps, and soft deletes intentionally. Never concatenate SQL with user input.

Durable domain objects should include business settings, services, quote question groups, quote questions, portfolio projects, customers, quote requests, quote answers, uploaded files, quotes, quote line items, projects, media assets, pages/content, testimonials, FAQs, contact submissions, and optional blog posts.

Services are stored in their own table with stable public slugs, draft/published/archived status, display order, and optional starting-price visibility. Only published services resolve on public routes. Admin service routes share the Shield session and admin-group filters.

Portfolio projects are separate records with stable slugs, draft/published/archived status, optional homepage featuring, and a featured media reference. Reusable public media records hold an image path and alt text. Only published portfolio records resolve on public routes; the admin portfolio routes share the Shield session and admin-group filters.

The public media library stores validated images once and relates them through explicit tables: one featured image per service, ordered gallery images per portfolio project, and service-to-project associations. Media deletion is blocked while any featured or gallery relationship exists. New library uploads use generated names under `public/uploads/media/`; existing `public/uploads/portfolio/` records remain valid.

Quote intake configuration uses reusable question groups, ordered questions, normalized choice options, and explicit group-to-service relationships. A group with no service relationships is general and applies once to every quote request; an assigned group applies when at least one of its services is selected. Inactive groups and questions remain stored for future quote-history integrity. The `QuoteQuestionCatalog` exposes only active, relevant configuration to the public builder.

The pre-submission quote cart is intentionally session-backed. It stores only published service IDs and bounded draft answers, revalidates service availability on every read, and does not create a durable quote request. Explicit submission creates an opaque-reference `quote_requests` record plus immutable service and answer snapshots in one database transaction. Attachments are stored under non-public `writable/uploads/quote_requests/` and are available only through an admin-authorized download route. The cart clears only after persistence succeeds; owner and customer email attempts happen afterward and record independent delivery states.

Qualified requests can be converted once into an actual commercial quote. Conversion finds or creates a lightweight customer by normalized email, links matching request history, and creates a customer/contact snapshot on the quote plus editable line items, calculated subtotal/discount/tax/total/deposit values, a status-history entry, and an immutable JSON revision snapshot. Customer administration provides protected search, contact/address editing, internal notes, and a chronological request/quote/status timeline. Profile edits do not rewrite quote snapshots, and customer deletion is intentionally unavailable while retention and archival policy remain undefined. Quote math is recalculated server-side on every save. Customer-facing proposals use a random 256-bit bearer token, are available only in public quote states, and return private no-store/noindex responses. Delivery uses the configured email service and records its outcome without falsely marking an undelivered quote as sent.

About, contact, privacy, and terms content use fixed-slug page records with draft/published visibility and editable SEO fields. Fixed slugs keep navigation and sitemap URLs stable without introducing a general page builder. Contact inquiries are persisted before synchronous notification is attempted; delivery state is recorded on the submission, and failed or unconfigured email never discards the inquiry. Contact and page administration share the Shield session and admin-group filters.

### Views And Frontend

Responsibility: Render UI using CodeIgniter views, helpers, CSS, and progressive enhancement where useful.

Rules: Escape output with `esc()`. Keep the first generated interface usable rather than marketing-oriented.

Public pages may use a more expressive portfolio-forward design. Admin pages should remain work-focused and support repeated quote/client/content management.

## 4. Data Stores

Primary Database: SQLite for the initial single-business deployment, stored under `writable/primo.sqlite`. The framework database configuration can be changed for a future multi-user or hosted deployment.

An existing `.env` may override the SQLite default; the current local environment is configured for MySQL. Production database selection remains deployment-specific.

Schema Management: CodeIgniter migrations under `app/Database/Migrations/`; Shield and Settings package migrations are run with `php spark migrate --all`.

Seeds: Use `app/Database/Seeds/` for deterministic development fixtures.

## 5. External Integrations

No integrations are configured yet.

When adding integrations, document:

- Service name
- Purpose
- Authentication method
- Environment variables
- Retry/error behavior
- Local testing strategy

## 6. Deployment And Infrastructure

Cloud Provider: To be decided.

Runtime Requirements:

- PHP 8.2+
- Composer dependencies installed
- `intl` and `mbstring` PHP extensions
- Writable `writable/` directory
- Web server document root set to `public/`

CI/CD Pipeline: To be decided.

Monitoring And Logging: CodeIgniter logs write to `writable/logs/` by default. Production projects should configure centralized log collection.

## 7. Security Considerations

- Keep project root outside the public document root.
- Use `.env` for secrets and never commit it.
- Enable production environment settings before deployment.
- Use CSRF protection for browser forms.
- Admin settings routes require Shield session authentication and membership in the `admin` group. Public registration is disabled.
- Use filters for authentication, authorization, CORS, and rate limiting concerns.
- Escape view output with `esc()` using the correct output context.
- Validate all request input before use.
- Use CodeIgniter models/query builder or bound parameters for database access.
- Store uploads outside the public web root unless the file is intentionally public.
- Media-library images are intentionally public under `public/uploads/media/` (with legacy portfolio uploads still supported); they are admin-only uploads with size, type, extension, and image-content checks plus generated filenames. Private customer quote uploads must use a separate non-public store.
- Public contact submissions use CSRF, CodeIgniter's honeypot filter, server-side validation, output escaping, and a five-submission-per-IP rolling limit over 15 minutes. Raw visitor IP addresses are not persisted.
- Quote-cart mutations use POST with CSRF. Session data is bounded and treated as untrusted: selected services are revalidated as published, answer keys and choice IDs are restricted to the active catalog, and all rendered draft values are escaped.
- Public quote submission adds honeypot and per-IP throttling, validates customer and configured-question input, limits private attachments to five approved files with 8 MB individual and 20 MB aggregate caps, and does not persist raw IP addresses. Admin quote-request list, detail, status, notes, and attachment routes require Shield session authentication and `admin` membership.
- Quote construction and delivery routes are admin-only and CSRF-protected. Submitted totals are never trusted: line quantities, unit prices, discounts, tax, and deposits are range-validated and recalculated before persistence. Secure proposal responses suppress caching, referrers, and search indexing; their tokens must be treated as secrets.

## 8. Development And Testing Environment

Local Setup:

```bash
composer install
copy env .env
php spark serve
composer test
```

Testing Frameworks: PHPUnit with CodeIgniter test support.

Code Quality Tools: To be selected per generated project.

## 9. Future Considerations

- Decide whether the generated app is primarily server-rendered, API-first, or hybrid.
- Decide authentication strategy, such as CodeIgniter Shield or a project-specific provider.
- Add migrations and seeders when the first persisted feature is built.
- Add frontend asset tooling only when the project needs it.
- Add CI once a target repository is created.

## 10. Project Identification

Project Name: Primo Graphic Designer Business Platform

Repository URL: To be filled in by the generated project.

Primary Contact/Team: East Point Software

Date of Last Update: 2026-09-19

## 11. Glossary

CI4: CodeIgniter 4.

Spark: CodeIgniter's command-line tool.

Filter: CodeIgniter middleware-like request/response hook.

Migration: Version-controlled database schema change.
