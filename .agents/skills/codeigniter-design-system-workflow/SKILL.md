---
kind: skill
id: codeigniter-design-system-workflow
name: codeigniter-design-system-workflow
description: Use when extracting, reviewing, documenting, or applying a design system for this CodeIgniter 4 scaffold so agents follow the full workflow without the user remembering the exact order.
license: MIT
source: local
---

# CodeIgniter Design System Workflow

Use this skill when the user asks to extract a design system from a website, build a starter design system, apply tokens to this CodeIgniter project, or keep UI work consistent with an extracted design direction.

This is the orchestration skill. It coordinates these supporting skills when relevant:

- `extract-design-system` for public-site token extraction.
- `css-architecture` for CSS variables, naming, and stylesheet organization.
- `relume-codeigniter-ui-converter` when Relume React/TSX components are used as UI source material.
- `codeigniter-feature-delivery` when design-system work touches CI4 views, layouts, controllers, or routes.

## Default Outcome

The user should be able to ask a simple request like:

> Extract the design system from example.com and set this project up to use it.

The agent should then run the right sequence:

1. Confirm the target website URL and whether generated files may be created.
2. Extract starter tokens from the public website.
3. Review and summarize the extracted colors, typography, spacing, radius, and shadows.
4. Decide whether the extraction is usable before applying it.
5. Generate or refresh token files.
6. Update project design documentation and memories.
7. Apply tokens to CodeIgniter views/styles only after confirmation.
8. Verify the result and record handoff notes.

## Workflow

### 1. Intake

Collect only the missing inputs that materially affect the workflow:

- Target public website URL.
- Extraction-only, token generation, or apply-to-project.
- Whether the user wants a CI4 view/style implementation now or only design-system artifacts.

If the user already gave enough information, proceed with the safest interpretation:

- public URL present -> extract and summarize
- "generate tokens" or "set up design system" -> generate token files
- "apply" or "use it in the project" -> ask before modifying existing app styles/views if confirmation is not already clear

### 2. Extraction

Use `extract-design-system` for extraction commands and boundaries.

Expected outputs:

- `.extract-design-system/raw.json`
- `.extract-design-system/normalized.json`
- `design-system/tokens.json`
- `design-system/tokens.css`

Do not treat a single extracted page as a complete brand system. Summarize confidence and gaps.

### 3. Review Gate

Before applying extracted tokens to app code, inspect `.extract-design-system/normalized.json` and summarize:

- likely primary, secondary, accent, surface, text, and border colors
- font families, font sizes, weights, and line heights
- spacing scale
- radius scale
- shadows/elevation
- anything suspicious, overly page-specific, inaccessible, or noisy

Stop and ask before app-wide adoption when:

- extracted colors appear low contrast
- many colors look like screenshots/ads/third-party widgets
- fonts are missing or unavailable
- the target site is highly dynamic or incomplete
- existing app styling would be overwritten

### 4. Documentation And Memory Updates

When extraction or adoption produces a real project decision, update:

- `DESIGN.md` with the selected tokens and design direction.
- `.agents/memories/design-baseline.md` with the source site, adopted token files, and design constraints.
- `.agents/memories/development-progress.md` with completed work, verification, blockers, and next actions.

If a generated project chooses a frontend stack or CSS methodology, also update:

- `.agents/memories/project-architecture.md` for durable structure.
- `.agents/memories/quality-gates.md` if new visual checks or style checks become required.

### 5. CodeIgniter Adoption

When the user confirms applying the design system:

- Prefer a project-local stylesheet that imports or includes `design-system/tokens.css`.
- Keep CI4 views server-rendered PHP.
- Use CSS custom properties for colors, typography, spacing, radius, and shadows.
- Add reusable view partials only when they reduce repeated markup.
- Keep app UI practical and workflow-first per `DESIGN.md`.
- Do not add a frontend build tool unless the user requests one.

Suggested day-zero placement:

- Tokens: `design-system/tokens.json` and `design-system/tokens.css`
- App CSS: `public/assets/css/app.css`
- Layout: `app/Views/layouts/default.php`
- UI partials: `app/Views/components/ui/*.php`
- Section partials: `app/Views/components/sections/*.php`

Follow existing project structure if it differs.

### 6. Relume Component Integration

When using Relume MCP for UI elements:

- Use Relume to find a suitable component.
- Treat returned React/TSX as design source material.
- Use `relume-codeigniter-ui-converter` to convert it into CI4 PHP views/partials.
- Replace React state/effects with semantic HTML or minimal vanilla JavaScript.
- Style with adopted design tokens instead of blindly copying framework-specific utilities.

### 7. Verification

For extraction-only work:

- Confirm generated files exist.
- Confirm JSON files parse.
- Summarize extracted token quality.

For applied UI work:

- Confirm views render if a route/page is available.
- Check that dynamic text is escaped with `esc()`.
- Check responsive behavior for obvious layout issues.
- Check contrast risk for primary text/actions when feasible.
- Run project tests when PHP behavior changed.

## Guardrails

- Do not overwrite existing design-system files without noting the change and preserving user work.
- Do not claim the extracted tokens are authoritative without review.
- Do not copy proprietary content, logos, or protected assets from a reference site unless the user has rights to use them.
- Do not introduce React, Tailwind, Vite, or any frontend build step just because the reference tooling or Relume uses it.
- Do not convert third-party website content into product copy; extract visual primitives only.

## Response Shape

When finishing, include:

- Source website or Relume component used.
- Files generated or changed.
- Token quality summary.
- Whether tokens were only extracted or also applied.
- Verification performed.
- Any required next decision from the user.
