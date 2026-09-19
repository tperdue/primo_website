# Design

This project uses a Penji-inspired visual direction adapted for Primo's purple-led palette. Treat this as a starter design system for a reusable CodeIgniter application serving independent graphic designers, not a pixel-perfect clone of Penji or a source of protected Penji content/assets.

## Overview

Name: Primo Graphic Designer Business Platform

Description: A reusable public website, quote-request flow, admin portal, and future customer portal for solo graphic designers and very small design businesses.

The public site should feel contemporary, creative, image-forward, and sales-capable. The admin portal should feel quieter and more operational: dense enough for repeated work, but still polished enough for a solo creative business owner.

The first public homepage now adopts the reference's white editorial canvas, bold dark type, outlined actions, and prominent creative imagery. The visuals in `public/assets/images/` are original generated concept studies, labeled as illustrative rather than presented as client work. The shipped stylesheet is `public/assets/css/app.css`, which imports a public copy of the saved Primo token file at `public/assets/css/tokens.css`. Keep that copy synchronized with `design-system/tokens.css` when tokens change.

The public header uses the black accent as a sticky navigation surface. At phone widths, desktop links collapse into a native `details` menu with a visible menu icon and touch-sized links; section anchors account for the sticky header height.

The service catalog uses a compact editorial list and a text-led detail layout until deployment-specific service imagery is available. Do not imply that the illustrative homepage concept studies are real service or client work.

The admin area is an operational workspace, distinct from the public marketing layout: persistent section navigation, a compact toolbar, a data-led overview, scannable service table, and grouped settings forms. On narrow screens, navigation becomes a horizontal section bar and data rows stack with visible labels.

Portfolio pages are image-forward only when real deployment-specific project images are uploaded. The public Work index uses large image previews and the detail page shows the full featured image without cropping, followed by challenge and solution. Featured published projects may appear on the homepage. Admin portfolio management follows the same dense workspace patterns as Services.

The media library uses a dense thumbnail browser with visible descriptions, type/size metadata, usage counts, and a focused edit view. Service detail pages may lead with an uncropped featured image and related work; project pages may add a two-column gallery that becomes a single column on mobile. Relationship pickers use stable thumbnail tiles and explicit numeric ordering rather than a decorative gallery editor.

Managed About and legal pages use a restrained editorial reading layout. Contact uses a two-column introduction and form on desktop and a single-column flow on smaller screens. Contact submissions and page editing live in the operational admin shell as tables and grouped forms, not promotional cards.

Quote-question administration follows the same operations pattern: a compact group table communicates status, question count, scope, and order; group forms separate applicability from content; and question forms keep answer type, choices, and behavior in distinct sections. On mobile, rows become labeled records while the admin section navigation remains horizontally scrollable.

The public quote builder uses a visible navigation count, compact selected-service summary, and numbered question bands rather than a checkout-style card stack. The service summary is sticky on desktop and returns to normal flow on smaller screens; question fields collapse from two columns to one, and all cart mutations remain usable without JavaScript.

```yaml
version: alpha
name: Primo Graphic Designer Business Platform
description: Penji-inspired design primitives with Primo palette
source:
  referenceSite: "https://penji.co/"
  extractionDate: "2026-09-18"
  extractedFont: "Zalando Sans"
colors:
  primary: "#562C82"
  secondary: "#68418F"
  accent: "#000000"
  surface: "#D2C7DC"
  light-neutral: "#FBF9FB"
  text: "#000000"
  text-muted: "#3B3044"
  border: "#BDAFCB"
  focus: "#562C82"
  success: "#188038"
  warning: "#B06000"
  danger: "#C5221F"
typography:
  body:
    fontFamily: "Zalando Sans, system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif"
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: 0
  heading:
    fontFamily: "Zalando Sans, system-ui, -apple-system, BlinkMacSystemFont, Segoe UI, sans-serif"
    fontSize: 32px
    fontWeight: 700
    lineHeight: 1.12
    letterSpacing: 0
rounded:
  xs: 4px
  sm: 8px
  md: 12px
  lg: 20px
  pill: 999px
spacing:
  1: 2px
  2: 4px
  3: 5px
  4: 6px
  5: 8px
  6: 10px
  7: 12px
  8: 14px
  9: 16px
  10: 20px
  11: 24px
  12: 28px
  13: 32px
  14: 40px
  15: 48px
  16: 64px
  17: 80px
  18: 96px
  19: 112px
  20: 128px
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.light-neutral}"
    rounded: "{rounded.pill}"
    padding: 12px 20px
  button-secondary:
    backgroundColor: "{colors.light-neutral}"
    textColor: "{colors.accent}"
    borderColor: "{colors.border}"
    rounded: "{rounded.pill}"
    padding: 12px 20px
  input:
    backgroundColor: "{colors.light-neutral}"
    textColor: "{colors.text}"
    borderColor: "{colors.border}"
    rounded: "{rounded.sm}"
    padding: 12px 14px
  card:
    backgroundColor: "{colors.light-neutral}"
    textColor: "{colors.text}"
    rounded: "{rounded.md}"
    padding: 24px
```

## Reference Patterns

The Penji homepage reference establishes these reusable patterns:

- Bold editorial hero with short navigation, direct CTAs, social proof, and image-forward creative previews.
- Large alternating bands for capabilities, portfolio proof, testimonials, FAQ, and final CTA.
- Rounded buttons and input groups with simple, high-contrast CTAs.
- Portfolio/case-study modules that pair imagery, outcomes, metrics, and a concise story.
- Service category navigation that makes a broad creative offering feel browsable.
- Friendly but confident copy density, with large headings and compact supporting paragraphs.

Do not copy Penji logos, proprietary imagery, mascot/illustrations, customer names, testimonials, claims, pricing, or copy. Use the layout archetypes and interaction patterns only.

## Color Use

Use the Primo palette as the adopted source of truth:

- Primary: Deep Primo Purple `#562C82`
- Secondary: Soft Purple `#68418F`
- Accent: Black `#000000`
- Surface: Pale Lavender `#D2C7DC`
- Light Neutral: White `#FBF9FB`

Primary purple should anchor CTAs, focus states, active navigation, quote indicators, and important admin actions. Soft purple can support secondary bands, hover states, charts, and subtle emphasis. Black is the main text/accent color and should be used sparingly for strong contrast. Pale Lavender is a section surface and should not be the only background color on a page. White keeps portfolio imagery, forms, cards, and admin screens crisp.

## Typography

The extraction detected `Zalando Sans` for both headings and body. Use it only if it is licensed and available for the deployment; otherwise fall back to the system sans stack in the token files.

Headlines should be confident and spacious on public marketing pages, but compact in admin screens. Avoid viewport-scaled type and negative letter spacing.

## Layout

Public pages should be mobile-first and portfolio-forward:

- Home, services, service detail, portfolio, about, contact, quote request, legal pages.
- Hero sections may be expressive, but they should lead into usable service and quote paths quickly.
- Portfolio and service pages should support strong imagery, concise content, related items, and quote CTAs.
- Quote-request flows should feel lighter than e-commerce checkout but borrow cart-style clarity.

Admin screens should favor scanning and repeat use:

- Dashboard items requiring attention.
- Tables/lists for quote requests, customers, services, portfolio, media, and settings.
- Forms with clear labels, help text, validation errors, upload limits, and submit states.
- No decorative marketing composition inside operational admin screens.

## Components

Expected early primitives:

- Public app shell with header, service navigation, quote indicator, and footer.
- Admin app shell with sidebar/topbar, dashboard cards, tables, status chips, and actions.
- Buttons, icon buttons, link buttons, segmented controls, toggles, checkboxes, radios, selects, inputs, textareas, and file upload controls.
- Service cards, portfolio cards, testimonial/quote cards, metric cards, FAQ accordions, pricing/starting-price display, and quote item rows.
- Alerts, validation summaries, empty states, loading states, pagination, modals, and confirmation dialogs.

## Do's And Don'ts

Do:

- Build real product workflows before decorative pages.
- Keep business-specific brand details configurable.
- Use original placeholder copy and user-provided assets.
- Escape dynamic output in views with the correct `esc()` context.
- Keep layouts responsive for portfolio browsing, quote requests, file uploads, quote viewing, and admin quote management.

Don't:

- Build a one-off designer site that hard-codes the first deployment.
- Add a customer portal before the public site, quote system, and admin portal are stable.
- Introduce a page builder, complex CRM, accounting platform, or advanced project-management system in the initial phases.
- Copy Penji content, assets, class names, claims, or source code.
- Use decorative blobs, vague gradients, nested cards, or overflowing text.
