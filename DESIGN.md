# Design

This day-zero design file follows the `DESIGN.md` convention so agents have a stable source of truth for UI work. Replace these defaults once the generated project has a real brand or product direction.

## Overview

Name: CodeIgniter Starter

Description: A quiet, practical full-stack application baseline for internal tools, dashboards, portals, and server-rendered web products.

The default visual direction is restrained and work-focused: readable forms, compact tables, clear navigation, accessible contrast, and predictable interaction states.

```yaml
version: alpha
name: CodeIgniter Starter
description: Day-zero design tokens for generated CodeIgniter applications
colors:
  background: "#F7F8FA"
  surface: "#FFFFFF"
  surface-muted: "#EEF2F6"
  text: "#17202A"
  text-muted: "#5D6B7A"
  primary: "#1F6FEB"
  primary-hover: "#195EC8"
  success: "#188038"
  warning: "#B06000"
  danger: "#C5221F"
  border: "#D7DEE8"
typography:
  body:
    fontFamily: "system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif"
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: 0
  heading:
    fontFamily: "system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif"
    fontSize: 28px
    fontWeight: 650
    lineHeight: 1.2
    letterSpacing: 0
rounded:
  xs: 2px
  sm: 4px
  md: 8px
spacing:
  xs: 4px
  sm: 8px
  md: 16px
  lg: 24px
  xl: 32px
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#FFFFFF"
    rounded: "{rounded.sm}"
    padding: 10px 14px
  input:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text}"
    rounded: "{rounded.sm}"
    padding: 10px 12px
  card:
    backgroundColor: "{colors.surface}"
    textColor: "{colors.text}"
    rounded: "{rounded.md}"
    padding: 16px
```

## Colors

Use neutral surfaces with one clear action color. Avoid one-note palettes dominated by a single hue. Reserve success, warning, and danger colors for semantic state.

## Typography

Use system fonts by default. Keep body copy at readable sizes and avoid viewport-scaled type. Use compact headings inside dashboards, forms, sidebars, and cards.

## Layout

Prioritize scannable layouts:

- Full-width page bands or simple constrained content regions.
- Forms with clear labels, help text, validation errors, and submit states.
- Tables that support repeated use: obvious columns, empty states, and responsive behavior.
- Stable dimensions for buttons, toolbars, pagination, counters, and tiles.

## Elevation & Depth

Use borders first, shadows sparingly. Do not nest cards inside cards.

## Shapes

Use 4px radius for buttons and inputs, 8px max for cards and panels unless a generated project defines a stronger design system.

## Components

Expected day-zero components:

- App shell with header/nav and main content region
- Button, icon button, link button
- Form inputs, selects, checkboxes, radios, toggles
- Validation summary and field-level errors
- Table/list view with empty and loading states
- Alert/flash messages
- Pagination
- Modal or confirmation dialog only when needed

## Do's and Don'ts

Do:

- Build the actual usable screen first.
- Keep workflows efficient for repeat use.
- Use accessible labels and visible focus states.
- Match UI density to the job the app performs.

Don't:

- Add marketing hero sections to internal/product tools.
- Use decorative blobs, vague gradients, or stock-like visuals by default.
- Put instructional copy in the UI to explain obvious controls.
- Let text overflow buttons, cards, tables, or navigation items.
