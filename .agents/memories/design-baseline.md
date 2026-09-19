---
id: primo_graphic_designer_design_baseline
importance: medium
tags: design, frontend, views
title: Design Baseline
---

The design baseline is Penji-inspired but Primo-branded. Use Penji as a reference for visual posture and layout patterns only; do not copy protected content, assets, logos, customer proof, claims, source code, or class names.

Current design direction:

- Public site: contemporary, portfolio-focused, image-forward, creative, mobile-first, and quote-oriented.
- Admin portal: quiet, practical, and work-focused for one-person design businesses.
- Customer portal: later phase; keep it convenient and lightweight when introduced.
- Palette: Deep Primo Purple `#562C82`, Soft Purple `#68418F`, Black `#000000`, Pale Lavender `#D2C7DC`, White `#FBF9FB`.
- Reference patterns: bold editorial hero, service category browsing, case-study/portfolio cards, proof metrics, rounded CTAs/forms, FAQ, and final quote/demo-style CTA.

Agents should:

- Read `DESIGN.md` before UI work.
- Use `design-system/tokens.json` and `design-system/tokens.css` as starter token sources once app styling begins.
- The public homepage uses an original generated editorial design-work hero and concept-study image, with Penji-inspired hierarchy but no copied Penji assets or claims. The imagery is explicitly labeled illustrative, not client work.
- `public/assets/css/app.css` imports `public/assets/css/tokens.css`, a shipped copy of `design-system/tokens.css`.
- The public header is black and sticky. At 700px and below, navigation uses a native `details` menu with a 44px trigger; section anchors offset for the header.
- The service catalog is a compact editorial list, with a text-led detail page until real deployment-specific imagery is available.
- Admin screens use a shared operations shell with persistent section navigation, a compact toolbar, restrained data summary, dense tables, and grouped forms. On phones, navigation is horizontal and service rows stack; avoid marketing-style cards and oversized headings.
- Public Work pages use real uploaded project images with large previews and a full-image detail view; the homepage only features published projects explicitly marked featured.
- Managed About/legal pages use an editorial reading layout; Contact pairs concise studio context with a practical form and collapses to one column on mobile. Admin Pages and Contact submissions stay table/form oriented.
- Build actual usable application screens first.
- Keep forms, tables, navigation, empty states, loading states, and validation states complete.
- Use readable system typography and accessible contrast.
- Avoid generic marketing pages unless the generated project is explicitly a public landing page.
- Avoid nested cards, decorative blobs, vague gradients, and text overflow.
- Keep deployment-specific logos, imagery, business copy, services, portfolio, testimonials, FAQs, pricing visibility, and policies configurable.

The app currently uses server-rendered CI4 views and plain CSS with no build tool. If the project later introduces a CSS framework, component library, or asset pipeline, update `DESIGN.md` and this memory.
