---
id: ci4_starter_deployment_runtime
importance: medium
tags: deployment, runtime, operations, environment
title: Deployment Runtime
---

# Deployment Runtime

Use this memory to track hosting, runtime, environment, jobs, logging, backup, and operational decisions. Update it when the generated project chooses infrastructure.

## Current Status

No deployment target is selected at scaffold time.

## Runtime Requirements

Baseline:

- PHP 8.2+
- Composer dependencies installed
- `intl` PHP extension
- `mbstring` PHP extension
- Web server document root set to `public/`
- `writable/` writable by the PHP/web server user

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

## Jobs And Schedules

No queue workers, cron jobs, or scheduled tasks are defined yet.

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
