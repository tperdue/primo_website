---
id: primo_graphic_designer_design_system_workflow
importance: high
tags: design-system, tokens, css, workflow
title: Design System Workflow
---

# Design System Workflow

Use this memory to track design-system source, token files, adoption status, and workflow decisions for generated CodeIgniter projects.

## Current Status

Penji has been used as the public visual reference for starter design-system extraction. The automated extractor detected typography and spacing but no palette colors, so the palette was intentionally replaced with the user-provided Primo colors.

## Source Of Truth

| Item | Current Value |
| --- | --- |
| Reference site | `https://penji.co/` |
| Extraction date | 2026-09-18 |
| Token source | Penji-inspired structure plus Primo palette |
| Generated token files | `.extract-design-system/normalized.json`, `design-system/tokens.json`, `design-system/tokens.css` |
| App stylesheet | TBD; no app stylesheet has imported the tokens yet |
| Frontend build tool | None |

## Extracted Signals

- Font family detected: `Zalando Sans` for heading and body.
- Spacing scale detected: `2px`, `4px`, `5px`, `6px`, `8px`, `10px`, `12px`, `14px`, `16px`, `20px`, `24px`, `28px`, `32px`, `40px`, `48px`, `64px`, `80px`, `96px`, `112px`, `128px`.
- Automated color extraction returned no palette entries.
- User-provided color roles now define the starter palette: primary `#562C82`, secondary `#68418F`, accent `#000000`, surface `#D2C7DC`, light neutral `#FBF9FB`.
- Radius and shadow scales were added as starter tokens for rounded CTAs/cards and restrained elevation.

## Reference Patterns

- Bold image-forward public hero with direct CTAs.
- Service category browsing.
- Portfolio/case-study cards with metrics and outcomes.
- Rounded CTA buttons and compact forms.
- Large content bands for services, proof, process, FAQ, and final CTA.
- Dense but readable admin screens when product workflows are implemented.

## Workflow

Future implementation sequence:

1. Re-read `DESIGN.md`, this memory, and `docs/developer_handoff.md`.
2. Import or copy from `design-system/tokens.css` into the app stylesheet only when implementation is requested.
3. Keep CI4 views server-rendered.
4. Build public and admin UI primitives from these tokens.
5. Verify rendered UI on mobile and desktop once app code changes.

## Adoption Rules

- Keep CodeIgniter views server-rendered.
- Prefer CSS custom properties from `design-system/tokens.css`.
- Do not add React or a frontend build pipeline for design-system adoption.
- Use Relume React components only as visual source material, converted through `relume-codeigniter-ui-converter`.
- Treat extracted values as starter tokens until reviewed in actual project screens.
- Do not copy Penji protected content, imagery, logos, mascot, customer proof, claims, class names, or source code.
- Keep business-specific brand assets, copy, services, portfolio, testimonials, and FAQs configurable per deployment.

## Open Questions

- Is `Zalando Sans` licensed/available for deployments, or should the system fallback become the production default?
- Should the app keep plain CSS, use utility classes, or adopt a CSS methodology?
- Which UI primitives should become reusable CI4 partials?
