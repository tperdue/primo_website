---
id: ci4_starter_design_system_workflow
importance: high
tags: design-system, tokens, css, workflow
title: Design System Workflow
---

# Design System Workflow

Use this memory to track design-system source, token files, adoption status, and workflow decisions for generated CodeIgniter projects.

## Current Status

No external design system has been extracted or adopted yet.

## Source Of Truth

| Item | Current Value |
| --- | --- |
| Reference site | TBD |
| Extraction date | TBD |
| Token source | `DESIGN.md` day-zero defaults |
| Generated token files | TBD |
| App stylesheet | TBD |
| Frontend build tool | None |

## Workflow

Default sequence:

1. Extract starter tokens from a public reference site with `extract-design-system`.
2. Review `.extract-design-system/normalized.json`.
3. Summarize confidence, gaps, and accessibility risks.
4. Generate or refresh `design-system/tokens.json` and `design-system/tokens.css`.
5. Update `DESIGN.md`.
6. Update this memory and `design-baseline.md`.
7. Apply tokens to app CSS/views only after confirmation.
8. Verify rendered UI when app code changes.

## Adoption Rules

- Keep CodeIgniter views server-rendered.
- Prefer CSS custom properties from `design-system/tokens.css`.
- Do not add React or a frontend build pipeline for design-system adoption.
- Use Relume React components only as visual source material, converted through `relume-codeigniter-ui-converter`.
- Treat extracted values as starter tokens until reviewed in actual project screens.

## Open Questions

- What public site or brand should seed the generated project's design system?
- Should generated projects keep plain CSS, use utility classes, or adopt a CSS methodology?
- Which UI primitives should become reusable CI4 partials?
