---
id: primo_graphic_designer_api_contracts
importance: medium
tags: api, contracts, json, errors
title: API Contracts
---

# API Contracts

Use this memory when the generated project exposes JSON APIs, webhooks, or machine-consumed endpoints. Update it whenever response shapes, error formats, pagination, authentication, or versioning conventions change.

## Current Status

No JSON API contract is defined yet. The initial product can be server-rendered CodeIgniter, with JSON endpoints added only when they support concrete UI workflows such as quote-builder interactions, media selection, uploads, or future portal behavior.

## API Style

| Setting | Value |
| --- | --- |
| Primary style | TBD |
| Versioning strategy | TBD |
| Authentication | TBD |
| Content type | `application/json` when JSON APIs are introduced |

## Response Envelope

TBD. Choose one convention and document it before adding multiple endpoints.

Example candidates:

```json
{
  "data": {},
  "meta": {}
}
```

or:

```json
{
  "success": true,
  "data": {},
  "errors": []
}
```

## Error Format

TBD. Define:

- Validation error shape.
- Authentication error shape.
- Authorization error shape.
- Not-found error shape.
- Unexpected error shape.

## Pagination

TBD. Define page/pageSize, offset/limit, or cursor pagination before the first list endpoint is finalized.

## Status Code Conventions

Default candidates:

- `200 OK` for successful reads and updates with a body.
- `201 Created` for successful creation.
- `204 No Content` for successful deletion without a body.
- `400 Bad Request` for malformed requests.
- `401 Unauthorized` for unauthenticated requests.
- `403 Forbidden` for authenticated but unauthorized requests.
- `404 Not Found` for missing resources.
- `422 Unprocessable Entity` for validation failures.

## Open Questions

- Is this project server-rendered only, API-first, or hybrid?
- Will APIs be public, internal, or consumed only by first-party UI?
- Does the API need OpenAPI documentation?
- Which quote-builder interactions need JSON endpoints versus standard form posts?
- Will future customer portal features require a documented API?
