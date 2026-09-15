---
kind: skill
id: relume-codeigniter-ui-converter
name: relume-codeigniter-ui-converter
description: Use when converting Relume Library React/TSX UI components into CodeIgniter 4 PHP views, partials, layouts, and framework-native markup for this non-React project.
license: MIT
source: local
---

# Relume To CodeIgniter UI Converter

Use this skill when a UI element comes from the Relume Library MCP as React/TSX, but the target project is a CodeIgniter 4 application that renders PHP views instead of React.

## Goal

Convert the Relume component's visual structure and content model into CodeIgniter-friendly markup:

- `.php` view or partial files under `app/Views/`
- Plain HTML with PHP control structures
- CodeIgniter escaping helpers such as `esc()`
- Existing project CSS conventions or utility classes when available
- Minimal, framework-native JavaScript only when interaction requires it

Do not introduce React, JSX, a React build step, or Relume runtime dependencies unless the user explicitly changes the frontend stack.

## Inputs To Expect

Relume MCP may return:

- React component source in TSX/JSX.
- Primitive components such as `Button`, `Card`, `Input`, or `Accordion`.
- Tailwind-style class names.
- Static placeholder content and arrays of data.
- Icons from packages such as `lucide-react`.
- React state/effects for mobile menus, accordions, tabs, carousels, or forms.

Use the Relume output as design source material, not as install-ready code.

## Conversion Rules

### JSX To PHP View Markup

- Convert `className` to `class`.
- Convert JSX comments to HTML or PHP comments only when the comment remains useful.
- Convert `{value}` to `<?= esc($value) ?>`.
- Convert attribute interpolation to the correct context: `<?= esc($value, 'attr') ?>`.
- Convert URLs with project helpers when applicable: `site_url()`, `base_url()`, or `url_to()`.
- Convert repeated JSX arrays/maps to PHP loops:

```php
<?php foreach ($items as $item): ?>
    <article>
        <h3><?= esc($item['title']) ?></h3>
    </article>
<?php endforeach; ?>
```

- Convert conditional rendering to PHP conditionals:

```php
<?php if (! empty($cta)): ?>
    <a href="<?= esc($cta['href'], 'attr') ?>"><?= esc($cta['label']) ?></a>
<?php endif; ?>
```

### Data Shape

For reusable sections, prefer a partial that receives an explicit data array:

```php
<?= view('components/sections/hero', [
    'eyebrow' => 'Starter',
    'title' => 'Build faster with CodeIgniter',
    'body' => '...',
    'actions' => [
        ['label' => 'Get started', 'href' => site_url('start'), 'variant' => 'primary'],
    ],
]) ?>
```

Inside the partial:

- Define safe defaults at the top for optional values.
- Escape all dynamic text.
- Validate expected array keys defensively when the partial might be reused.

### Relume Primitives

Translate imported primitives to native markup or local partials:

- `Button` -> `<a>`, `<button>`, or local button partial depending on behavior.
- `Card` -> `<article>`, `<section>`, or `<div>` only when it frames a repeated item.
- `Input`, `Textarea`, `Select` -> native form elements with labels and validation slots.
- `Accordion`, `Tabs`, `Dropdown`, `Navbar menu` -> semantic HTML plus small progressive-enhancement JavaScript only if needed.
- `cn(...)` or class merging helpers -> resolved class strings.

Do not vendor Relume React primitive files into this project unless the user explicitly requests a React frontend.

### Interactivity

Prefer no JavaScript for static sections.

When interaction is required:

- Use semantic HTML first, such as `<details>`/`<summary>` for simple accordions when it fits the design.
- Use a tiny local script or existing chosen frontend helper if the project has one.
- Keep behavior independent from React state.
- Preserve keyboard and screen-reader behavior.
- Avoid introducing Alpine, HTMX, Stimulus, or another helper unless the generated project has already chosen it or the user asks for it.

### Icons And Images

- Replace React icon components with inline SVG, existing icon partials, or simple text/icon markup.
- Keep SVGs decorative with `aria-hidden="true"` unless they convey meaning.
- For meaningful images, require useful `alt` text from the data array.
- Use `base_url()` for local static assets.

### Forms

For form elements:

- Include labels.
- Include validation error slots compatible with CI4 validation messages.
- Include `csrf_field()` for browser POST forms unless the route is intentionally stateless.
- Preserve name/id associations.
- Do not trust client-side validation alone.

### Security

- Escape dynamic text with `esc()`.
- Escape attributes with `esc($value, 'attr')`.
- Escape JavaScript contexts with `esc($value, 'js')` only when unavoidable.
- Do not output raw HTML unless the source is explicitly trusted and sanitized.
- Never paste secrets, tokens, or private URLs into view templates.

## File Placement

Use paths that match the local CI4 conventions:

- Page views: `app/Views/pages/<name>.php`
- Reusable sections: `app/Views/components/sections/<name>.php`
- Small primitives: `app/Views/components/ui/<name>.php`
- Layouts: `app/Views/layouts/<name>.php`
- View-specific scripts/styles only when necessary and consistent with the project.

If the project already has a different view organization, follow it.

## Workflow

1. Fetch or inspect the Relume component source.
2. Identify the semantic sections, repeated data, actions, images, forms, and interactive states.
3. Decide whether the result should be a page, section partial, layout, or small UI partial.
4. Convert JSX to PHP view markup using CI4 escaping and helpers.
5. Convert React primitives to native markup or local partials.
6. Replace React-only state/effects with semantic HTML or minimal vanilla JavaScript.
7. Check the result against `DESIGN.md` and `.agents/memories/design-baseline.md`.
8. Update `.agents/memories/development-progress.md` after meaningful implementation work.

## Output Expectations

When completing a conversion, summarize:

- Relume component slug/source used.
- Created or changed CI4 view files.
- Any data array expected by the partial.
- Any JavaScript or CSS assumptions.
- Security notes for escaping, CSRF, and form handling.
- Verification performed or skipped.
