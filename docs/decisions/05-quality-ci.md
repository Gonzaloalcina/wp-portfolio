# Phase 05 — Quality and CI

Coding standards, tests and continuous integration.

See [README.md](README.md) for how entries are written.

## 2026-10-16 — CI on GitHub Actions: tokens tests and PHPCS

`.github/workflows/ci.yml` runs on every push and pull request to `main`, in
two parallel jobs:

- **Design tokens:** `npm run test:tokens` on Node 20 (no dependencies): unit
  rules and generated files up to date.
- **PHP coding standards:** `composer run lint` on PHP 8.0: WordPress Coding
  Standards (Core, Docs, Extra) and PHPCompatibilityWP for PHP 8.0+, configured
  in `phpcs.xml.dist` (text domain `portfolio-theme`, prefixes
  `portfolio_theme` / `portfolio`).

PHPUnit with a MySQL service comes with the first PHP tests (contact form
plugin). JS and CSS linters are not set up yet.

## 2026-10-16 — Composer for dev tools, resolved for PHP 8.0

Coding standards are Composer dev dependencies (`composer.lock` committed,
`vendor/` ignored). `config.platform.php` is set to 8.0 so dependencies resolve
for the CI and theme PHP version even when Composer runs on a newer PHP
locally.

## 2026-10-16 — Actions pinned by commit SHA, updated by Dependabot

Every action is pinned to a full commit SHA with the version in a comment
(`actions/checkout@<sha> # v7.0.1`). A tag can be moved to point at different
code; a SHA cannot, which protects the pipeline from supply-chain changes.
Dependabot (`.github/dependabot.yml`) opens weekly grouped updates for the
actions and the Composer dev dependencies. Workflow permissions are read-only.
