---
id: ci4_starter_security_baseline
importance: high
tags: security, owasp, codeigniter
title: Security Baseline
---

Security defaults for generated CodeIgniter projects:

- Never expose the project root as the web root; serve only `public/`.
- Never commit `.env`, credentials, private keys, API tokens, or production dumps.
- Read input via CodeIgniter request APIs, not raw superglobals.
- Validate input before use and keep validation close to the boundary.
- Use CSRF protection for browser forms.
- Escape view output with `esc()` and choose the correct context for HTML, attributes, JavaScript, and URLs.
- Use CodeIgniter models/query builder or bound parameters for database access.
- Define `$allowedFields` on every model that accepts inserts or updates.
- Use filters for authentication, authorization, CORS, and other request gates.
- Validate uploads by size, MIME type, extension, storage location, and generated filename.

When a security decision changes, update this memory and `ARCHITECTURE.md`.
