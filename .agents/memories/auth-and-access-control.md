---
id: primo_graphic_designer_auth_access_control
importance: high
tags: auth, authorization, access-control, security
title: Authentication And Access Control
---

# Authentication And Access Control

Use this memory to track identity, session, role, permission, and route-protection decisions. Update it whenever authentication or authorization behavior changes.

## Current Status

CodeIgniter Shield session authentication protects both workspaces. Public registration and magic-link login are disabled. Admins create customer access through 72-hour, one-time portal invitations; the shared login redirects admins to `/admin/` and customers to `/portal`.

## Authentication Provider

| Setting | Value |
| --- | --- |
| Provider/package | `codeigniter4/shield` `^1.4` |
| Session-based auth | Yes |
| Token/API auth | TBD |
| Password policy | Shield validators plus a 12-character minimum during customer activation |
| MFA/2FA | TBD |

## Roles And Permissions

Explicit `admin`, `customer`, and fallback `user` groups are configured. Route filters enforce workspace membership.

| Role | Permissions | Notes |
| --- | --- | --- |
| Admin/Designer Owner | Manage business settings, services, quote questions, quote requests, quotes, customers, portfolio, content, media, contact submissions, and later projects/invoices. | Required for admin portal. |
| Customer | Read-only profile, linked requests and private request files, sent/final quotes, and linked project status/update. | Account must be linked to exactly one customer record. |

## Protected Routes

Document route groups or filters when introduced:

| Route/Group | Filter | Access Rule |
| --- | --- | --- |
| Public marketing site | None by default | Visitors may browse public pages and submit contact/quote forms. |
| Public quote request | None by default | Must remain accountless for initial friction reduction. |
| Admin portal | `session` and `group:admin` filters | Designer owner only. |
| Project administration | `session` and `group:admin` filters | Designer owner only. |
| Customer portal | `session` and `group:customer` filters | Customer-owned records only; every detail query also enforces `customer_id`. |
| Portal activation | Public opaque-token route with CSRF on POST | Valid unused invitation, linked customer, and unclaimed email required. |

## Authorization Rules

Default until changed:

- Authentication proves identity.
- Authorization must still check whether that identity can perform the action.
- Do not rely on hidden UI alone for access control.
- Enforce ownership checks for user-owned records.
- Public quote-request submission does not prove customer identity.
- Portal access must not expose request, quote, project, or file records across customers; mismatches return 404.

## Session And Cookie Notes

- Use secure, HTTP-only, same-site cookie settings in production.
- Regenerate session IDs after privilege changes such as login.
- Keep session payloads minimal.
- CSRF uses session protection; admin and login forms include CSRF tokens. Login routes use Shield's auth rate-limit filter.

## Open Questions

- Should password recovery use Shield reset links or an admin-assisted flow?
- Does the project need API tokens or only browser sessions?
- Will deployments have only one designer owner or support multiple admin users?
