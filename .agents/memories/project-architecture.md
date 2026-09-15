---
id: ci4_starter_architecture
importance: high
tags: architecture, codeigniter, scaffold
title: CodeIgniter Starter Architecture
---

This repository is a reusable day-zero CodeIgniter 4 starter, not a finished application. Preserve its generic shape unless a generated project makes a specific product decision.

Current baseline:

- PHP `^8.2`.
- CodeIgniter `^4.7`.
- `public/` is the only web root.
- `app/` contains application code.
- `writable/` contains runtime output.
- `tests/` contains PHPUnit and CodeIgniter tests.

Architectural defaults:

- Use explicit routes in `app/Config/Routes.php`.
- Keep controllers thin.
- Introduce services/domain classes only when logic is shared, complex, or independently testable.
- Introduce migrations, seeders, and models when persistence is needed.
- Keep framework config changes documented in `ARCHITECTURE.md`.
