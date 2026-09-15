---
id: ci4_starter_quality_gates
importance: high
tags: quality, testing, verification, handoff
title: Quality Gates
---

# Quality Gates

Use this memory to track required verification before handing off work. Update it when the generated project adopts or changes test, lint, static analysis, formatting, security, or coverage requirements.

## Current Status

The starter has PHPUnit configured and a Composer `test` script.

## Required Checks

Default for scaffold work:

- Parse changed JSON/YAML/frontmatter files when applicable.
- Run `composer test` or `vendor/bin/phpunit` for PHP behavior changes when feasible.
- For docs-only changes, inspect changed files and skip app tests if there is no runtime impact.
- For security-sensitive changes, review input validation, authorization, CSRF, output escaping, SQL construction, session/cookie behavior, and secret handling.

## Known Verification Caveat

`composer test` currently runs the existing tests successfully but exits non-zero because no code coverage driver is installed.

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
