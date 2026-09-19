---
id: primo_graphic_designer_architecture
importance: high
tags: architecture, codeigniter, scaffold
title: Primo Graphic Designer Architecture
---

This repository is a reusable CodeIgniter 4 application for independent graphic designers and very small design businesses. Preserve configurability so East Point Software can deploy it for multiple designers without hard-coding one business's identity, services, questions, pricing, or portfolio.

Current baseline:

- PHP `^8.2`.
- CodeIgniter `^4.7`.
- `public/` is the only web root.
- `app/` contains application code.
- `writable/` contains runtime output.
- `tests/` contains PHPUnit and CodeIgniter tests.
- `docs/developer_handoff.md` is the source product brief.
- The first Phase 1 slice uses Shield session auth, a single-row business settings model, and a public home view backed by that row.
- The framework database default is SQLite; the local ignored `.env` currently overrides it to MySQL.

Architectural defaults:

- Use explicit routes in `app/Config/Routes.php`.
- Keep controllers thin.
- Introduce services/domain classes only when logic is shared, complex, or independently testable.
- Introduce migrations, seeders, and models when persistence is needed.
- Keep framework config changes documented in `ARCHITECTURE.md`.
- Model the application around domain objects rather than hard-coded CMS pages.
- Product areas are public marketing site, admin portal, and later customer portal.
- Do not build the customer portal before the public site, quote system, and admin portal are stable.
- Keep deployment-specific business settings, website content, services, portfolio, quote questions, quote terms, policies, and notifications configurable.
- Resolve quote intake through active reusable groups: unassigned groups are general, while service assignments make groups conditional on the selected quote services.
- Keep the pre-submission cart transient in the visitor session; durable quote-request records begin only when the visitor explicitly submits.

Core product workflow:

Visitor -> service/portfolio browsing -> add services to quote -> answer general and service-specific questions -> upload supporting files -> submit quote request -> designer reviews and qualifies -> designer builds actual quote -> designer sends quote -> customer can later accept and become a project.
