Yes. Your answers change the project in an important way: this is not really a one-off graphic designer website anymore. It is a **reusable East Point Software application for independent graphic designers**, with the first deployment serving as the initial implementation.

I would make that explicit in the brief so the developer does not accidentally build business-specific assumptions into the code. The attached EC International brief does something similar by separating the business goal from specific functional requirements and admin requirements.

Below is the version I would hand to the developer.

---

# Graphic Designer Website + Client Management Application

## Developer Brief

**East Point Software**

### Project Type

Reusable PHP / CodeIgniter application for independent graphic designers.

---

# 1. Project Goal

Build a reusable website and lightweight client-management application that East Point Software can deploy for multiple independent graphic designers.

The primary target customer is a **solo graphic designer or very small design business** that needs more than a basic portfolio website but does not need the complexity of a traditional CRM or project-management platform.

The application should combine:

- A professional public marketing website
- Service and portfolio management
- A configurable quote-request system
- Actual quote creation and delivery
- Lightweight lead/client management
- Content and media management
- A future customer portal for projects, communication, invoices, and payments

The first implementation should establish a reusable base application rather than a website containing assumptions specific to one designer.

The application will be built using:

- PHP
- CodeIgniter
- Relational database supported by CodeIgniter
- Standard HTML/CSS/JavaScript as appropriate

---

# 2. Product Philosophy

The application should remain intentionally lightweight.

It should help a solo graphic designer manage the journey:

**Visitor → Quote Request → Qualified Lead → Quote → Client → Project → Payment**

without attempting to become a full CRM, ERP, accounting application, or complex project-management system.

The interface should prioritize simplicity and clarity over the number of features available.

---

# 3. Reusable Application Requirement

This application is intended to be deployed for multiple graphic designers.

Business-specific information should therefore be configurable rather than hard-coded.

Examples include:

- Business name
- Logo
- Brand colors
- Contact information
- Social media links
- Services
- Pricing visibility
- Portfolio
- Quote questions
- Quote terms
- Business policies
- Email addresses
- Notification settings
- Website content

A new deployment should ideally require configuration and content entry rather than significant application-code changes.

---

# 4. Application Areas

The application should ultimately contain three primary areas:

### Public Marketing Site

Available to website visitors and prospective clients.

### Admin Portal

Used by the graphic designer to manage the business website, quote requests, clients, quotes, projects, and settings.

### Customer Portal

A later development phase allowing clients to view projects, communicate, upload files, view invoices, and make payments.

The customer portal should **not be developed before the marketing site and admin portal are complete**.

---

# 5. Public Marketing Site

The public site should function as a professional portfolio and sales website.

Primary pages should include:

- Home / Landing Page
- Services
- Individual Service Detail Pages
- Portfolio / Work
- Individual Portfolio Project Pages
- About
- Blog — optional
- Contact
- Quote Request
- Privacy Policy
- Terms & Conditions

The site architecture should support additional informational pages if needed.

---

# 6. Website Design

The developer is responsible for the visual design of the application.

The design should be:

- Professional
- Contemporary
- Portfolio-focused
- Image-forward
- Mobile-first
- Appropriate for a creative professional
- Easy to customize for different deployments

The design should avoid being so stylistically specific that the application cannot reasonably be adapted to different graphic designers.

The visual system should support configurable elements such as:

- Logo
- Primary color
- Secondary color
- Accent color
- Surface/background colors
- Typography where practical
- Business imagery

The application does not need to become a full visual page builder.

---

# 7. Home Page

The home page should function as the primary marketing landing page.

Possible sections include:

- Hero
- Featured services
- Featured portfolio work
- About / designer introduction
- Design process
- Testimonials
- Frequently asked questions
- Quote call to action
- Contact call to action

The owner should be able to edit the primary content through the admin portal.

---

# 8. Services

The application should contain a service catalog.

Examples may include:

- Logo Design
- Brand Identity
- Social Media Graphics
- Print Design
- Packaging Design
- Presentation Design
- Marketing Collateral
- Digital Design

These examples must not be hard-coded.

The owner should be able to:

- Add services
- Edit services
- Publish / unpublish services
- Reorder services
- Archive or remove services
- Add service images
- Associate portfolio work
- Configure quote questions
- Configure pricing visibility

---

# 9. Service Detail Pages

Each service should have a dedicated public page.

Possible fields include:

- Service name
- Short description
- Full description
- Featured image
- Image gallery
- What's included
- Typical deliverables
- Process
- Estimated turnaround
- Frequently asked questions
- Related portfolio projects
- Related services
- Pricing information
- Quote call to action

Primary CTA:

**Add to Quote**

or equivalent wording.

---

# 10. Service Pricing

Each service should support a **starting price**.

Example:

> Starting at $750

Pricing should also have a visibility setting.

The owner should be able to:

- Enter a starting price
- Show the starting price publicly
- Hide the price while retaining it internally
- Change pricing without modifying templates

A service should therefore be capable of displaying:

> Starting at $750

or no public pricing information at all.

The displayed starting price should not automatically become the final quoted price.

---

# 11. Portfolio

Portfolio functionality should be a first-class component of the application.

The owner should be able to create portfolio projects containing:

- Project title
- Client name, when appropriate
- Featured image
- Image gallery
- Project description
- Challenge / brief
- Solution / work performed
- Services provided
- Project date
- External project link, if applicable
- Related services
- SEO information

Portfolio projects should support:

- Draft
- Published
- Featured

Portfolio entries should be reusable throughout the public website.

For example:

- Home page featured work
- Portfolio listing
- Service detail page examples

---

# 12. Quote System

The public quote-request experience should behave similarly to an e-commerce shopping cart but should **not collect payment**.

Instead of adding products to a cart, visitors add services to a quote request.

Example:

```text
Logo Design
Starting at $750

[ Add to Quote ]
```

The customer should be able to continue browsing and add additional services.

A visible quote indicator should show how many services are currently included.

---

# 13. Quote Builder

The quote builder should allow visitors to:

- Review selected services
- Remove services
- Add additional services
- Complete required questions
- Upload files
- Provide customer information
- Provide general project details
- Submit the request

Public quote requests must **not require an account**.

This is important for minimizing friction for new prospective clients.

---

# 14. Quote Questions

The application should support two categories of configurable questions:

### General Question Groups

Reusable groups that can be attached to multiple services.

Examples:

**Business Information**

- Business name
- Industry
- Website
- Target audience

**Brand Information**

- Existing logo?
- Existing brand guidelines?
- Existing brand colors?
- Brand personality

**Project Timing**

- Desired completion date
- Hard deadline?
- Event or launch date?

### Service-Specific Questions

Questions unique to a particular service.

For example, Print Design might ask:

- Finished dimensions
- Number of pages
- Print quantity
- Printer selected?

The same question should not need to be recreated unnecessarily for every service.

---

# 15. Question Field Types

The question system should support common field types such as:

- Single-line text
- Multi-line text
- Number
- Email
- Phone
- Date
- Dropdown
- Radio options
- Checkboxes
- Yes / No
- File upload

Fields should support settings such as:

- Required / optional
- Display order
- Help text
- Placeholder text
- Active / inactive

---

# 16. File Uploads

Prospective customers should be able to attach supporting material to quote requests.

Examples include:

- Existing logos
- Brand guidelines
- Inspiration images
- PDFs
- Project briefs
- Existing marketing materials

The application must enforce:

- Allowed file extensions/types
- Maximum individual upload size
- Maximum total upload size where appropriate

Upload limits should preferably be configurable by the administrator or deployment settings.

Customer uploads should not automatically be publicly accessible.

---

# 17. General Quote Information

Every quote should collect basic customer information.

Examples:

- Name
- Company
- Email
- Phone
- Preferred contact method
- Project summary
- Desired completion date
- Budget or budget range
- Additional notes

Some general fields may be optional or configurable.

---

# 18. Quote Submission

When a quote request is submitted:

1. Store the request in the database.
2. Generate a unique request/reference number.
3. Store selected services.
4. Store answers to questions.
5. Store uploaded files.
6. Notify the business owner.
7. Send a confirmation email to the prospective customer.
8. Display the request in the admin portal.

The public website should make clear that submission represents a **request for a quote and not final pricing or project acceptance**.

---

# 19. Quote Request Management

The admin portal should provide a quote-request inbox.

Possible statuses:

- New
- Reviewing
- Needs Information
- Qualified
- Not Qualified
- Quote Preparing
- Quote Sent
- Accepted
- Declined
- Closed

The owner should be able to:

- View request details
- Review requested services
- Review question responses
- Download uploaded files
- Add internal notes
- Change status
- Request additional information
- Associate the request with a customer/client record
- Begin constructing an actual quote

---

# 20. Quote Creation

A qualified quote request should be convertible into an actual quote.

The quote should support:

- Quote number
- Customer
- Related quote request
- Quote date
- Expiration date
- Line items
- Description
- Quantity
- Unit price
- Line-item price
- Discount, if needed
- Subtotal
- Taxes, when applicable
- Total
- Terms
- Notes
- Deposit requirement
- Revision/version history where practical

Line items do not have to exactly match the public services selected.

For example, a customer may request:

**Brand Identity**

but the final quote might contain:

```text
Brand Strategy                 $500
Logo Design                  $1,200
Brand Color System             $300
Typography Selection           $250
Mini Brand Guide               $450

Total                        $2,700
```

This flexibility is important.

---

# 21. Quote Delivery

The owner should be able to mark a quote as ready and send it to the customer.

The initial implementation can support delivery through:

- Email
- Secure link
- PDF where practical

The customer should be able to clearly view:

- Services / line items
- Pricing
- Terms
- Deposit requirements
- Expiration date
- Total

A later customer portal can provide formal quote acceptance.

---

# 22. Lightweight Client Management

The application should maintain basic client/customer records.

This should intentionally remain simpler than a CRM.

A customer record may contain:

- Name
- Business name
- Email
- Phone
- Address when needed
- Notes
- Quote requests
- Quotes
- Projects
- Invoices

The objective is simply to prevent important customer information from being scattered across unrelated records.

---

# 23. Admin Portal

Primary admin sections should include:

- Dashboard
- Quote Requests
- Quotes
- Customers
- Projects — initially lightweight
- Services
- Quote Questions
- Question Groups
- Portfolio
- Pages / Content
- Blog
- Media
- Contact Submissions
- Business Settings

The admin portal should feel appropriate for a **one-person design business**, not a large sales organization.

---

# 24. Dashboard

The dashboard should emphasize things requiring the owner's attention.

Examples:

- New quote requests
- Quote requests requiring follow-up
- Quotes awaiting customer response
- Recently accepted quotes
- Active projects
- Recent contact requests

Avoid adding analytics simply for the sake of having a dashboard.

---

# 25. Content Management

The business owner should be able to manage ordinary website content without editing code.

At minimum:

- Home page content
- Services
- Service content
- Portfolio
- About
- Blog
- Contact information
- Testimonials
- FAQs
- Legal pages
- SEO metadata

This does not need to become a general-purpose CMS or drag-and-drop page builder.

---

# 26. Media Manager

The application should include a reusable media library.

The owner should be able to:

- Upload media
- Browse media
- Reuse existing images
- Add alt text
- View file information
- Remove media when appropriate

The same media library should be available to:

- Services
- Portfolio
- Blog
- Marketing content

Avoid requiring duplicate uploads of the same asset.

---

# 27. Business Settings

Each deployment should have centralized business settings.

Examples:

### Business

- Business name
- Owner/designer name
- Logo
- Business email
- Phone
- Address

### Website

- Site title
- Default meta description
- Social sharing image
- Brand settings

### Social

- Instagram
- Facebook
- LinkedIn
- Behance
- Dribbble
- Other links

### Quotes

- Default quote expiration
- Default quote terms
- Default deposit percentage
- Pricing visibility defaults

### Files

- Allowed file types
- Maximum upload size

### Notifications

- Quote request recipient
- Contact form recipient

The application should avoid hard-coding these values into templates.

---

# 28. Blog

The application should support an optional blog.

Features:

- Create/edit posts
- Draft/published status
- Publication date
- Featured image
- Categories
- SEO metadata

The blog should be capable of being disabled entirely.

When disabled, blog navigation and related interface elements should not appear.

---

# 29. Contact

The public website should contain a standard contact form.

Possible fields:

- Name
- Email
- Phone
- Subject
- Message

The system should:

- Store submissions
- Notify the owner
- Include spam protection

---

# 30. Customer Portal — Later Phase

The customer portal should be developed only after the public website, quote system, and admin portal are stable.

The portal should remain intentionally lightweight.

Customers should eventually be able to:

- Create an account
- Sign in
- View their profile
- Submit new quote requests
- View previous requests
- View quotes
- Accept quotes
- View active projects
- View basic project status
- Upload files
- View project notes/messages
- Respond to the designer
- View invoices
- Make payments

The portal should feel like a convenient client workspace rather than a CRM.

---

# 31. Projects

An accepted quote should eventually be convertible into a project.

A project might contain:

- Project name
- Client
- Related quote
- Start date
- Due date
- Project status
- Notes
- Files
- Customer-visible updates

Suggested statuses:

- Awaiting Deposit
- Scheduled
- In Progress
- Awaiting Client Feedback
- Revisions
- Awaiting Final Payment
- Complete
- Canceled

Project management should intentionally remain minimal.

The goal is to answer:

> What am I working on, where does it stand, and what does the customer need to know?

It is not intended to compete with tools such as Asana, Monday, Jira, or ClickUp.

---

# 32. Customer Communication

Future projects should support a simple threaded communication area.

The designer and customer should be able to:

- Add comments
- Respond to comments
- Attach files
- See the date/time of communication

The system should not attempt to reproduce full email or chat functionality.

Its purpose is to maintain a basic project-specific communication record.

---

# 33. Invoices and Payments — Later Phase

Projects should eventually support invoices.

Basic invoice information may include:

- Invoice number
- Customer
- Project
- Issue date
- Due date
- Line items
- Subtotal
- Tax
- Total
- Amount paid
- Balance
- Status

Payment processing should support a provider such as Stripe.

Potential payment types:

- Deposit
- Milestone payment
- Final balance
- Standalone invoice

Card information should never be stored directly by the application.

---

# 34. Email Notifications

Transactional email should support events including:

- Quote request received
- Customer quote-request confirmation
- Additional information requested
- Quote sent
- Quote accepted
- Project update
- Invoice available
- Payment received

Templates should preferably be centralized.

---

# 35. Responsive Requirements

Both the public site and application interfaces should be responsive.

Particular attention should be given to:

- Portfolio viewing
- Service browsing
- Quote requests
- File uploads
- Quote viewing
- Admin quote management

The public customer experience should be designed mobile-first.

---

# 36. SEO Requirements

Public content should support:

- Page titles
- Meta descriptions
- Canonical URLs
- Open Graph metadata
- Image alt text
- Sitemap
- Robots settings
- Human-readable URLs

Examples:

```text
/services/logo-design

/services/brand-identity

/portfolio/acme-brand-redesign

/blog/how-to-prepare-for-a-logo-design-project
```

---

# 37. Security Requirements

Use standard CodeIgniter and PHP security practices including:

- CSRF protection
- Form validation
- Output escaping
- Secure password hashing
- Authentication/authorization
- File upload validation
- MIME/type validation
- File-size restrictions
- Protected administrative routes
- Production-safe error reporting
- Application logging

Customer-provided files should not automatically reside in publicly browsable locations.

---

# 38. Architecture Requirements

The developer should design the data model around reusable concepts rather than pages containing hard-coded data.

At minimum, the architecture should recognize relationships between:

```text
Business Settings
       │
       ├── Services
       │      ├── Question Groups
       │      ├── Questions
       │      └── Portfolio Projects
       │
Customers
       │
       ├── Quote Requests
       │      ├── Services
       │      ├── Answers
       │      └── Files
       │
       ├── Quotes
       │      └── Quote Line Items
       │
       └── Projects
              ├── Files
              ├── Messages
              └── Invoices
```

The database should anticipate the later customer portal without requiring the public quote requester to create an account initially.

---

# 39. Suggested Development Phases

## Phase 1 — Application Foundation + Marketing Site

Build:

- Application structure
- Admin authentication
- Business settings
- Public site
- Home
- Services
- Service detail pages
- Portfolio
- About
- Contact
- Legal pages
- Responsive layout
- SEO foundations

---

## Phase 2 — Content + Quote Request System

Build:

- Service management
- Portfolio management
- Content management
- Media library
- Question groups
- Quote questions
- Quote cart/builder
- File uploads
- Quote submission
- Notifications
- Quote-request administration

---

## Phase 3 — Quote Management

Build:

- Customer records
- Quote construction
- Quote line items
- Pricing
- Quote terms
- Quote statuses
- Quote delivery
- Quote revision/version handling as needed

---

## Phase 4 — Lightweight Client Portal

Build:

- Customer authentication
- Customer dashboard
- Quote history
- Quote acceptance
- Projects
- Project status
- Files
- Communication

---

## Phase 5 — Invoices + Payments

Build:

- Invoices
- Deposits
- Payments
- Payment status
- Stripe or similar integration

---

# 40. Primary Initial Workflow

The most important workflow for the first usable release is:

```text
Visitor enters website
          ↓
Browses portfolio and services
          ↓
Views service details
          ↓
Adds one or more services to quote
          ↓
Completes general + service questions
          ↓
Uploads supporting material
          ↓
Submits quote request
          ↓
Designer receives request
          ↓
Designer reviews / qualifies lead
          ↓
Designer constructs actual quote
          ↓
Designer sends quote
          ↓
Customer accepts
          ↓
Quote can later become a project
```

The application should make this process feel simpler than emailing back and forth to determine basic project requirements.

---

# 41. What Should NOT Be Built Initially

To keep the product focused, the developer should **not** expand the initial implementation into:

- Full CRM
- Email marketing system
- Accounting platform
- Advanced project-management application
- Time tracking
- Team resource management
- Employee management
- Sales pipeline analytics
- Drag-and-drop website builder
- Complex workflow automation engine

These may be integrated with external systems later if needed.

The application's advantage should be that it contains **the minimum operating system a solo graphic designer needs to market services, qualify work, quote projects, and manage the resulting client relationship.**

---

## Product Design decision

Treat **question groups, services, quote requests, quotes, customers, portfolio entries, and projects as actual domain objects**, rather than thinking of this primarily as a CMS website.

That's a subtle but important distinction.

You're effectively building:

> **a graphic designer's business operating layer with a website attached to it**

rather than:

> **a graphic designer website that happens to have an admin panel.**
