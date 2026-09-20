---
id: primo_graphic_designer_quality_gates
importance: high
tags: quality, testing, verification, handoff
title: Quality Gates
---

# Quality Gates

Use this memory to track required verification before handing off work. Update it when the generated project adopts or changes test, lint, static analysis, formatting, security, or coverage requirements.

## Current Status

The project has PHPUnit configured, a Composer `test` script, and a GitHub Actions production workflow. Pushes to `main` must pass Composer validation and `vendor/bin/phpunit --no-coverage` before deployment.

## Required Checks

Default for scaffold work:

- Parse changed JSON/YAML/frontmatter files when applicable.
- Run `composer test` or `vendor/bin/phpunit` for PHP behavior changes when feasible.
- For docs-only changes, inspect changed files and skip app tests if there is no runtime impact.
- For security-sensitive changes, review input validation, authorization, CSRF, output escaping, SQL construction, session/cookie behavior, and secret handling.
- For design-system changes, parse token JSON and inspect generated CSS/docs.
- For future UI implementation, verify mobile and desktop rendering, contrast risk, focus states, text overflow, and that no protected reference-site content/assets were copied.
- For future quote/upload work, verify validation, upload storage location, MIME/extension/size restrictions, CSRF, spam protection, and notification behavior.

## Known Verification Caveat

`composer test` currently runs the existing tests successfully but exits non-zero because no code coverage driver is installed.

The deployment workflow intentionally uses `vendor/bin/phpunit --no-coverage`; its clean-checkout verification currently passes 91 tests and 446 assertions.

Resolution options for generated projects:

- Install and configure Xdebug or PCOV for coverage.
- Adjust PHPUnit coverage requirements if coverage is not required.
- Keep documenting the warning until coverage is intentionally configured.

## Future Gates To Consider

- PHP-CS-Fixer or PHP_CodeSniffer for formatting/style.
- PHPStan or Psalm for static analysis.
- Composer audit or dependency vulnerability scanning.
- CI workflow that runs tests on pull requests.
- Browser/UI checks once real user-facing flows exist.

## Handoff Requirements

Every substantial agent handoff should include:

- Files changed.
- Tests/checks run.
- Checks skipped and why.
- Known risks or follow-up work.
- Updates to `development-progress.md` when current state changes.
