---
id: primo_graphic_designer_deployment_runtime
importance: medium
tags: deployment, runtime, operations, environment
title: Deployment Runtime
---

# Deployment Runtime

Use this memory to track hosting, runtime, environment, jobs, logging, backup, and operational decisions. Update it when the generated project chooses infrastructure.

## Current Status

The initial production deployment runs on Ubuntu 24.04 with Nginx, PHP 8.3 FPM, and MySQL at `primodemo.eastpointsoftware.net`. Pushes to `main` are tested by GitHub Actions and deploy the exact tested commit through a restricted SSH command.

## Runtime Requirements

Baseline:

- PHP 8.2+
- Composer dependencies installed
- `intl` PHP extension
- `mbstring` PHP extension
- Web server document root set to `public/`
- `writable/` writable by the PHP/web server user
- `public/uploads/media/` writable by the PHP/web server user and included in backups; legacy `public/uploads/portfolio/` files must also be retained when present
- Session storage available for the pre-submission quote cart; multi-instance deployments must use a shared session handler or compatible sticky-session strategy

## Hosting

| Setting | Value |
| --- | --- |
| Provider | Amazon EC2 |
| Service type | Ubuntu virtual machine |
| Region | Not recorded |
| PHP version | 8.3 |
| Web server | Nginx with PHP-FPM |

## Environment Variables

Document project-specific variables here. Do not record secret values.

| Variable | Purpose | Required |
| --- | --- | --- |
| `CI_ENVIRONMENT` | CodeIgniter environment name | Yes |
| `app.baseURL` | Application base URL | Yes |
| `email.fromEmail`, `email.fromName`, and provider-specific mail settings | Contact notifications and later quote delivery | Required for email delivery; contact records still persist without them |
| TBD file/upload settings | Upload limits and storage location for private quote files and public media | Yes before upload launch |

## Jobs And Schedules

No queue workers, cron jobs, or scheduled tasks are defined yet. Future notification delivery, quote expiration reminders, or payment reconciliation may require background work, but should not be introduced until needed.

## Logging And Monitoring

Default:

- CodeIgniter logs write under `writable/logs/`.
- Deployment logs write to `/var/log/primo-deploy.log` and GitHub Actions.
- Production projects should centralize logs and define alert-worthy failures.

## Backups And Restore

- Every automated release creates and validates a compressed MySQL dump under `/var/backups/primo/mysql` before migrations.
- Releases are stored under `/var/www/primo/releases`, with `/var/www/primo/current` switched atomically.
- Failed post-switch health checks restore the previous code symlink, but migrations are never reversed automatically.
- Scheduled and off-host backups are not configured yet.

## Delivery Contract

- Workflow: `.github/workflows/deploy-production.yml` on pushes to `main` or manual dispatch.
- Required GitHub secret: `PRODUCTION_SSH_KEY` only; never record its value.
- The SSH host key is pinned in `deploy/known_hosts`.
- The deployment key is restricted to `/usr/local/sbin/primo-deploy-entrypoint` and cannot open a shell or forward connections.
- Server-owned configuration and persistent paths remain outside releases under `/var/www/primo/shared`.

## Open Questions

- Where will the generated project be hosted?
- Will the app need background jobs, queues, or scheduled commands?
- What data needs backup and restore testing?
- Where will private customer uploads be stored?
- Which transactional email provider will be used?
- Which payment provider will be used in the later invoices/payments phase?
