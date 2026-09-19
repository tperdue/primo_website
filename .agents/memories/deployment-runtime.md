---
id: primo_graphic_designer_deployment_runtime
importance: medium
tags: deployment, runtime, operations, environment
title: Deployment Runtime
---

# Deployment Runtime

Use this memory to track hosting, runtime, environment, jobs, logging, backup, and operational decisions. Update it when the generated project chooses infrastructure.

## Current Status

No deployment target is selected yet. Each deployment should support configurable business identity, public website content, admin access, email notifications, file uploads, and eventually customer portal/invoice/payment workflows.

## Runtime Requirements

Baseline:

- PHP 8.2+
- Composer dependencies installed
- `intl` PHP extension
- `mbstring` PHP extension
- Web server document root set to `public/`
- `writable/` writable by the PHP/web server user
- `public/uploads/portfolio/` writable by the PHP/web server user and included in backups; these are intentionally public media files

## Hosting

| Setting | Value |
| --- | --- |
| Provider | TBD |
| Service type | TBD |
| Region | TBD |
| PHP version | TBD |
| Web server | TBD |

## Environment Variables

Document project-specific variables here. Do not record secret values.

| Variable | Purpose | Required |
| --- | --- | --- |
| `CI_ENVIRONMENT` | CodeIgniter environment name | Yes |
| `app.baseURL` | Application base URL | Yes |
| TBD mail settings | Transactional email for quote/contact notifications and later quote delivery | Yes before quote submission launch |
| TBD file/upload settings | Upload limits and storage location for private quote files and public media | Yes before upload launch |

## Jobs And Schedules

No queue workers, cron jobs, or scheduled tasks are defined yet. Future notification delivery, quote expiration reminders, or payment reconciliation may require background work, but should not be introduced until needed.

## Logging And Monitoring

Default:

- CodeIgniter logs write under `writable/logs/`.
- Production projects should centralize logs and define alert-worthy failures.

## Backups And Restore

No backup strategy is defined yet.

## Open Questions

- Where will the generated project be hosted?
- Will the app need background jobs, queues, or scheduled commands?
- What data needs backup and restore testing?
- Where will private customer uploads be stored?
- Which transactional email provider will be used?
- Which payment provider will be used in the later invoices/payments phase?
