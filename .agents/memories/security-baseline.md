---
id: primo_graphic_designer_security_baseline
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
- Protect all admin portal routes behind authentication and authorization.
- Keep public quote requests unauthenticated, but validate them aggressively and add spam/rate-limit protections when implemented.
- Customer uploads for quote requests must not be stored in publicly browsable locations by default.
- Enforce configurable allowed file extensions/types, maximum individual upload size, and maximum total upload size.
- Card/payment details must never be stored directly if invoices/payments are added later.
- Treat business settings, notification recipients, quote terms, and pricing visibility as administrative data.
- Shield session login protects the admin settings routes with `session` and `group:admin`; login is rate-limited.
- Browser forms use session-backed CSRF tokens; session cookies are Secure in production, HTTP-only, and SameSite Lax.
- Admin portfolio uploads accept only JPEG, PNG, and WebP up to 5 MB, verify real MIME and image dimensions, require alt text, and store generated filenames under intentionally public `public/uploads/portfolio/`. Quote/customer uploads remain private and must not reuse this path.
- Public contact forms use CSRF, CodeIgniter's injected honeypot, strict length/email validation, and a five-request-per-IP limit over 15 minutes. Inquiries are escaped in admin views, raw IP addresses are not stored, and notification failures do not discard submissions.

When a security decision changes, update this memory and `ARCHITECTURE.md`.
