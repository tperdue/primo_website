---
kind: skill
id: codeigniter-security-review
name: codeigniter-security-review
description: Use when reviewing or implementing security-sensitive CodeIgniter 4 code, including auth, forms, APIs, file uploads, sessions, cookies, database access, and deployment config.
license: MIT
source: local
---

# CodeIgniter Security Review

Use this project-local skill for security implementation and review.

## Review Areas

- Access control: every protected route has authentication and authorization.
- CSRF: browser form posts include CSRF protection unless intentionally stateless.
- Validation: all external input is validated before use.
- SQL safety: use models/query builder/bound parameters; no string-concatenated user input.
- Output escaping: use `esc()` with the correct context in views.
- Mass assignment: models define narrow `$allowedFields`.
- Uploads: validate MIME type, extension, size, filename, and storage location.
- Sessions/cookies: use secure, HTTP-only, same-site settings in production.
- Errors: no production stack traces or debug output.
- Secrets: `.env` and credentials are never committed.

## Report Format

When reviewing, lead with findings:

```text
file:line - [severity] Issue summary
Impact: What could go wrong.
Fix: Concrete change to make.
```

If there are no findings, say that clearly and name any residual risk or missing tests.
