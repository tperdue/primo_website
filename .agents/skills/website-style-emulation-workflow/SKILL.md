---
kind: skill
id: website-style-emulation-workflow
name: website-style-emulation-workflow
description: Use when the user wants to emulate the look, layout patterns, UI elements, and design system of an existing website in this CodeIgniter 4 scaffold without copying protected content or assets.
license: MIT
source: local
---

# Website Style Emulation Workflow

Use this skill when the user wants a CodeIgniter 4 project to be visually inspired by an existing public website, including both its design-system primitives and comparable UI sections.

This skill coordinates:

- `codeigniter-design-system-workflow` for extraction, token review, documentation, and adoption.
- `extract-design-system` for colors, typography, spacing, radius, and shadows.
- Relume MCP for finding generic component equivalents.
- `relume-codeigniter-ui-converter` for converting Relume React/TSX output into CI4 PHP views.
- `css-architecture` when CSS variables, utility classes, or component styles need structure.
- `codeigniter-feature-delivery` when views, layouts, routes, controllers, or assets are changed.

## Core Rule

Emulate style and interaction patterns, not protected identity.

Allowed:

- Visual mood and density.
- Layout archetypes.
- Generic component patterns.
- Extracted starter tokens after review.
- Original placeholder copy.
- Original or user-provided assets.

Not allowed unless the user has rights and explicitly asks:

- Logos, trademarks, branded marks, illustrations, icons, photos, videos, or proprietary assets.
- Exact marketing copy, product claims, pricing names, customer names, testimonials, or legal text.
- Pixel-perfect cloning intended to confuse users about source or affiliation.
- Proprietary source code, class names as a dependency, or copied implementation details.

## Default User Prompt

The user should be able to say:

> Emulate the visual style of https://example.com for this CodeIgniter starter. Extract the design system, find similar Relume sections, and build a CI4 homepage with original placeholder content.

The agent should infer the sequence below.

## Workflow

### 1. Intake

Proceed when the user provides:

- A public reference website URL.
- A target surface such as homepage, dashboard, pricing page, app shell, auth screen, or landing page.

Ask only if missing:

- Which page/surface to build when the request is ambiguous.
- Whether to implement now or only produce a plan/design-system summary.
- Whether the user owns any brand assets they want included.

### 2. Reference Analysis

Inspect the public site enough to identify reusable patterns:

- Page structure: navbar, hero, feature grid, stats, cards, forms, testimonials, pricing, footer, dashboard shell, etc.
- Visual language: density, spacing, borders, radii, elevation, typography, color roles, icon style.
- Interaction patterns: menus, tabs, accordions, filters, hover/focus states.
- Responsive behavior visible from the reference.

Do not copy content or assets. Record the pattern, not the proprietary details.

### 3. Design-System Extraction

Use `codeigniter-design-system-workflow` and `extract-design-system` to:

- Extract starter tokens from the reference site.
- Review `.extract-design-system/normalized.json`.
- Generate or refresh `design-system/tokens.json` and `design-system/tokens.css`.
- Update `DESIGN.md` and design memories when adoption becomes a project decision.

Stop before app-wide adoption if the extraction is noisy, low contrast, incomplete, or would overwrite user work without confirmation.

### 4. Component Matching With Relume

Use Relume MCP to find generic equivalents for the identified patterns.

Examples:

- Reference has a split hero with CTA buttons -> find a generic hero with image and buttons.
- Reference has logo cards/testimonials -> find generic testimonial or logo cloud sections.
- Reference has dashboard stats -> find generic stats or dashboard metric cards.
- Reference has pricing cards -> find generic pricing tables.

Choose comparable structure, not exact imitation.

### 5. Convert To CodeIgniter

Use `relume-codeigniter-ui-converter` to convert Relume React/TSX into CI4 views:

- Page views under `app/Views/pages/`.
- Layouts under `app/Views/layouts/`.
- Reusable sections under `app/Views/components/sections/`.
- Small UI primitives under `app/Views/components/ui/`.

Use original placeholder copy and safe generic data arrays. Escape dynamic output with `esc()` and attributes with `esc($value, 'attr')`.

### 6. Apply Styling

Use extracted tokens through CSS custom properties:

- Prefer `design-system/tokens.css` plus a project stylesheet such as `public/assets/css/app.css`.
- Use class names that belong to this project, not copied reference-site names.
- Preserve CodeIgniter's server-rendered model.
- Avoid introducing React, Tailwind, Vite, or another build step unless the user explicitly requests it.

### 7. Documentation And Memory

Update when implementation changes the project:

- `DESIGN.md`: adopted design direction and token summary.
- `.agents/memories/design-system-workflow.md`: reference source, token files, adoption state.
- `.agents/memories/design-baseline.md`: visual constraints and UI posture.
- `.agents/memories/development-progress.md`: completed work, blockers, verification, next actions.
- `.agents/memories/project-architecture.md`: only if frontend structure or asset pipeline changes.
- `.agents/memories/quality-gates.md`: only if new visual/style checks become required.

### 8. Verification

For extraction-only:

- Confirm generated token files exist.
- Confirm JSON parses.
- Summarize token quality and gaps.

For implemented UI:

- Confirm CI4 views render where possible.
- Check for copied brand/content/assets and remove them.
- Check escaping for dynamic text/attributes.
- Check mobile and desktop layout for obvious issues.
- Check visible focus and basic accessibility.
- Run PHP tests when behavior changed.

## Completion Summary

End with:

- Reference website used.
- Patterns emulated.
- Relume components or categories used.
- Files generated or changed.
- Whether design tokens were extracted only or adopted.
- Confirmation that protected content/assets were not copied.
- Verification performed and remaining risks.
