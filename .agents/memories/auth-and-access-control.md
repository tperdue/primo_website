---
id: primo_graphic_designer_auth_access_control
importance: high
tags: auth, authorization, access-control, security
title: Authentication And Access Control
---

# Authentication And Access Control

Use this memory to track identity, session, role, permission, and route-protection decisions. Update it whenever authentication or authorization behavior changes.

## Current Status

CodeIgniter Shield session authentication is installed for the admin area. Public registration and magic-link login are disabled. Create the first owner through Shield's interactive CLI command; credentials are never committed.

## Authentication Provider

| Setting | Value |
| --- | --- |
| Provider/package | `codeigniter4/shield` `^1.4` |
| Session-based auth | Yes |
| Token/API auth | TBD |
| Password policy | TBD |
| MFA/2FA | TBD |

## Roles And Permissions

No roles or permissions are implemented yet. Product planning expects at least an admin/designer owner for the admin portal, and later customer identities for the customer portal.

| Role | Permissions | Notes |
| --- | --- | --- |
| Admin/Designer Owner | Manage business settings, services, quote questions, quote requests, quotes, customers, portfolio, content, media, contact submissions, and later projects/invoices. | Required for admin portal. |
| Customer | View profile, requests, quotes, accepted work, files, messages, invoices, and payments. | Later customer portal phase only. |

## Protected Routes

Document route groups or filters when introduced:

| Route/Group | Filter | Access Rule |
| --- | --- | --- |
| Public marketing site | None by default | Visitors may browse public pages and submit contact/quote forms. |
| Public quote request | None by default | Must remain accountless for initial friction reduction. |
| Admin portal | `session` and `group:admin` filters | Designer owner only. |
| Project administration | `session` and `group:admin` filters | Designer owner only; customers have no project portal access yet. |
| Customer portal | Customer auth filter TBD | Later phase after public/admin workflows are stable. |

## Authorization Rules

Default until changed:

- Authentication proves identity.
- Authorization must still check whether that identity can perform the action.
- Do not rely on hidden UI alone for access control.
- Enforce ownership checks for user-owned records.
- Public quote-request submission does not prove customer identity.
- Later customer portal access must not expose quote/project records across customers.

## Session And Cookie Notes

- Use secure, HTTP-only, same-site cookie settings in production.
- Regenerate session IDs after privilege changes such as login.
- Keep session payloads minimal.
- CSRF uses session protection; admin and login forms include CSRF tokens. Login routes use Shield's auth rate-limit filter.

## Open Questions

- Will the generated project use CodeIgniter Shield, a custom auth layer, or an external provider?
- Which routes are public, authenticated, or admin-only?
- Does the project need API tokens or only browser sessions?
- Will deployments have only one designer owner or support multiple admin users?
