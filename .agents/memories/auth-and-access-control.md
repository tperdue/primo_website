---
id: ci4_starter_auth_access_control
importance: high
tags: auth, authorization, access-control, security
title: Authentication And Access Control
---

# Authentication And Access Control

Use this memory to track identity, session, role, permission, and route-protection decisions. Update it whenever authentication or authorization behavior changes.

## Current Status

No authentication system is installed at scaffold time.

## Authentication Provider

| Setting | Value |
| --- | --- |
| Provider/package | TBD |
| Session-based auth | TBD |
| Token/API auth | TBD |
| Password policy | TBD |
| MFA/2FA | TBD |

## Roles And Permissions

No roles or permissions are defined yet.

| Role | Permissions | Notes |
| --- | --- | --- |
| TBD | TBD | TBD |

## Protected Routes

Document route groups or filters when introduced:

| Route/Group | Filter | Access Rule |
| --- | --- | --- |
| TBD | TBD | TBD |

## Authorization Rules

Default until changed:

- Authentication proves identity.
- Authorization must still check whether that identity can perform the action.
- Do not rely on hidden UI alone for access control.
- Enforce ownership checks for user-owned records.

## Session And Cookie Notes

- Use secure, HTTP-only, same-site cookie settings in production.
- Regenerate session IDs after privilege changes such as login.
- Keep session payloads minimal.

## Open Questions

- Will the generated project use CodeIgniter Shield, a custom auth layer, or an external provider?
- Which routes are public, authenticated, or admin-only?
- Does the project need API tokens or only browser sessions?
