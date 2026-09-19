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
- Public quote requests remain unauthenticated but use CSRF, the honeypot filter, strict input/catalog validation, and a three-request-per-IP limit over 15 minutes. Raw IP addresses are not stored.
- Customer uploads for quote requests must not be stored in publicly browsable locations by default.
- Enforce configurable allowed file extensions/types, maximum individual upload size, and maximum total upload size.
- Card/payment details must never be stored directly if invoices/payments are added later.
- Treat business settings, notification recipients, quote terms, and pricing visibility as administrative data.
- Shield session login protects the admin settings routes with `session` and `group:admin`; login is rate-limited.
- Browser forms use session-backed CSRF tokens; session cookies are Secure in production, HTTP-only, and SameSite Lax.
- Admin media uploads accept only JPEG, PNG, and WebP up to 5 MB, verify real MIME and image dimensions, require alt text, and store generated filenames under intentionally public `public/uploads/media/`. Deletion checks every known reference and verifies the resolved file remains under `public/uploads/`. Quote/customer uploads remain private and must not reuse this path.
- Public contact forms use CSRF, CodeIgniter's injected honeypot, strict length/email validation, and a five-request-per-IP limit over 15 minutes. Inquiries are escaped in admin views, raw IP addresses are not stored, and notification failures do not discard submissions.
- Quote-question configuration is admin-only. Group assignments, supported field types, lengths, ordering, and choice counts are validated server-side; choice labels are normalized into related records and all admin output is escaped.
- Public quote-cart changes are POST-only and CSRF-protected. Session state is capped at 25 services and 200 answers; services are rechecked for published status, answer keys and option IDs are catalog-whitelisted, text is length-bounded, and draft values are escaped on output.
- Quote attachments accept PDF, JPG, PNG, DOC, and DOCX only, with server-side extension/MIME checks, generated names, an 8 MB individual limit, a 20 MB aggregate limit, and at most five files. They live outside `public/`; downloads verify request ownership and the resolved storage-root prefix before an admin-authorized response is returned.
- Submitted services, question labels, group labels, answers, option labels, and visible starting prices are snapshotted so later catalog edits cannot rewrite historical requests. The cart clears only after the transaction commits, and notification failures never discard persisted requests.
- Quote totals are server-authoritative. Admin input for dates, statuses, line items, quantities, prices, discounts, tax, deposits, terms, and notes is bounded and validated before a transaction replaces lines and records a revision.
- Customer proposal links use 256-bit random bearer tokens and expose only ready/sent/accepted/declined/expired quotes. Responses set no-store, no-cache, no-referrer, and noindex directives. Anyone holding a link can view that quote, so tokens must not be logged, indexed, or exposed in unrelated navigation.
- Email delivery failure or missing configuration never marks a quote sent. Quote/customer contact snapshots preserve the exact recipient and commercial record at creation time.

When a security decision changes, update this memory and `ARCHITECTURE.md`.
