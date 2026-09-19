---
id: primo_graphic_designer_domain_model
importance: medium
tags: domain, model, vocabulary
title: Domain Model
---

# Domain Model

Use this memory to preserve business vocabulary and domain rules for the reusable graphic designer business platform.

## Domain Summary

The application is a lightweight business operating layer for independent graphic designers, with a public website attached. It should support the journey from visitor to quote request, qualified lead, quote, client, project, and eventual payment without becoming a full CRM, accounting suite, or advanced project-management platform.

## Core Concepts

| Concept | Meaning | Notes |
| --- | --- | --- |
| Business Settings | Deployment-specific identity, contact, website, brand, quote, file, and notification settings. | Must be configurable rather than hard-coded. |
| Service | A design service offered publicly and used in quote requests. | Examples in the handoff are illustrative only and must not be hard-coded. |
| Question Group | Reusable group of quote questions attached to one or more services. | Prevents recreating common questions for every service. |
| Quote Question | A configurable field asked during quote requests. | Supports field type, requirement, help text, placeholder, order, and active state. |
| Portfolio Project | Public work sample reusable across home, portfolio, and service detail pages. | Supports draft, published, and featured states. |
| Quote Request | Public, unauthenticated customer request for pricing and project fit. | Not final pricing or project acceptance. |
| Quote | Actual designer-created commercial quote sent after review/qualification. | Line items may differ from requested services. |
| Customer | Lightweight client/prospect record. | Simpler than a CRM; ties together requests, quotes, projects, and invoices. |
| Project | Later operational record created from accepted work. | Intentionally minimal. |
| Media Asset | Reusable uploaded image/file with metadata and alt text. | Shared by services, portfolio, blog, and content. |

## Entities

| Entity | Purpose | Owner | Persistence |
| --- | --- | --- | --- |
| BusinessSetting | Centralized deployment configuration. | Admin/designer | Required once persistence begins. |
| Service | Public service catalog and quote-selectable offering. | Admin/designer | Required. |
| QuestionGroup | Reusable quote question grouping. | Admin/designer | Required for quote system. |
| QuoteQuestion | Configurable quote-request field. | Admin/designer | Required for quote system. |
| PortfolioProject | Public case study/work sample. | Admin/designer | Required. |
| QuoteRequest | Submitted public quote request. | Prospect/customer | Required for quote system. |
| QuoteRequestService | Selected services attached to a request. | Prospect/customer | Required for quote system. |
| QuoteAnswer | Answers to configured questions. | Prospect/customer | Required for quote system. |
| Upload/File | Supporting material for quote requests and media. | Prospect/customer or admin | Required for uploads/media. |
| Quote | Actual quote prepared by designer. | Admin/designer | Phase 3. |
| QuoteLineItem | Flexible line items for actual quote pricing. | Admin/designer | Phase 3. |
| Customer | Lightweight client/prospect record. | Admin/designer | Phase 3. |
| Project | Minimal project record after quote acceptance. | Admin/designer and later customer | Later phase. |
| Invoice | Billing record for project/payment phase. | Admin/designer | Later phase. |

## Lifecycle States

Document state machines or status fields here:

| Entity | State | Meaning | Allowed Transitions |
| --- | --- | --- | --- |
| PortfolioProject | Draft | Not public. | Published, Featured, Archived/Removed |
| PortfolioProject | Published | Publicly visible. | Draft, Featured, Archived/Removed |
| PortfolioProject | Featured | Publicly visible and eligible for featured placements. | Published, Draft, Archived/Removed |
| QuoteRequest | New | Newly submitted. | Reviewing, Needs Information, Qualified, Not Qualified, Closed |
| QuoteRequest | Reviewing | Designer is evaluating fit/details. | Needs Information, Qualified, Not Qualified, Quote Preparing, Closed |
| QuoteRequest | Needs Information | Designer needs more from customer. | Reviewing, Qualified, Not Qualified, Closed |
| QuoteRequest | Qualified | Request is worth quoting. | Quote Preparing, Closed |
| QuoteRequest | Not Qualified | Request is not a fit. | Closed |
| QuoteRequest | Quote Preparing | Designer is building an actual quote. | Quote Sent, Closed |
| QuoteRequest | Quote Sent | Actual quote has been delivered. | Accepted, Declined, Closed |
| QuoteRequest | Accepted | Customer accepted the quote. | Closed, Project Created |
| QuoteRequest | Declined | Customer declined the quote. | Closed |
| Project | Awaiting Deposit | Accepted work is waiting on initial payment. | Scheduled, Canceled |
| Project | Scheduled | Work is planned. | In Progress, Canceled |
| Project | In Progress | Work is active. | Awaiting Client Feedback, Revisions, Awaiting Final Payment, Complete, Canceled |
| Project | Awaiting Client Feedback | Designer needs client input. | In Progress, Revisions, Canceled |
| Project | Revisions | Revision work is active. | Awaiting Client Feedback, Awaiting Final Payment, Complete, Canceled |
| Project | Awaiting Final Payment | Delivery/payment is pending. | Complete, Canceled |
| Project | Complete | Work is done. | N/A |
| Project | Canceled | Work stopped. | N/A |

## Invariants

- Public quote requests must not require customer accounts.
- Starting prices are informational and must not automatically become final quote prices.
- Business-specific values must be configurable for each deployment.
- Customer uploads must not automatically be publicly accessible.
- Actual quote line items may differ from the originally selected public services.
- The customer portal must wait until public site, quote system, and admin portal foundations are stable.
- A question group with no service assignments is general and appears once per request; an assigned group appears when any assigned service is selected.
- Choice-field options are normalized records. Inactive groups and questions are retained so future submitted answers can preserve historical context.
- Quote-cart selections and draft answers are transient session state, not quote requests. A durable QuoteRequest is created only at explicit submission.
- Submitted requests use an opaque human-readable reference and immutable service/question/answer snapshots. Live catalog foreign keys are nullable so catalog retirement cannot destroy request history.
- Quote-request receipt confirms delivery only; it is never a final price or project acceptance. Owner and customer notification outcomes are tracked independently.
- A qualified request converts into at most one actual quote and an associated lightweight customer. The quote snapshots customer contact details so later customer edits cannot rewrite the commercial record.
- Quote line items are independent of originally requested services. Subtotal, fixed discount, percentage tax, total, and percentage deposit are calculated from current line items by the server.
- Each quote save creates an immutable numbered revision. Status changes also append history; public quote visibility begins at Ready and email delivery promotes Ready to Sent only after successful sending.

## Open Questions

- Which database engine and ID strategy will be used?
- Which authentication provider will protect admin and later customer routes?
- Which content should be modeled first for Phase 1 versus deferred to later phases?
