# Phase 05 — Quality and CI

Coding standards, tests and continuous integration.

See [README.md](README.md) for how entries are written.

## 2026-10-16 — CI on GitHub Actions: tokens tests and PHPCS

`.github/workflows/ci.yml` runs on every push and pull request to `main`, in
two parallel jobs:

- **Design tokens:** `npm run test:tokens` (no dependencies): unit
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

## 2026-10-21 — Playwright for end-to-end checks

`@playwright/test` (pinned, dev only) with Chromium, configured in
`playwright.config.mjs`. Tests live in `tests/e2e/` and run against a site
that is already up: the local XAMPP install by default, or any other with
`BASE_URL` (Playground: `BASE_URL=http://127.0.0.1:9401/ npm run test:e2e`).
First tests: the Services hub and service pages. Browsers are installed with
`npx playwright install chromium`, outside the repo. CI (a WordPress to test
against) comes later.

## 2026-10-21 — PHPUnit with WordPress's test library

PHPUnit tests the plugin's logic, starting with the contact form: rules
that Playwright cannot reach cheaply (the sixth attempt in an hour, a token
from another site, a message 13 months old) are each a millisecond test.

- **WordPress's own test library**, from Composer: `wp-phpunit/wp-phpunit`
  (no svn checkout), `yoast/phpunit-polyfills` (required by it) and
  `phpunit/phpunit` 9.6, the newest that runs on PHP 8.0. WordPress core
  comes from `roots/wordpress-no-content`, pinned to the site's version, so
  the tests never use the files of the local site, and CI runs the same core.
- **A test database, never the site's:** `wp_portfolio_tests` with its own
  MySQL user. The library empties it on every run, so the config refuses a
  database whose name lacks "test". Locally the settings live in
  `.env.testing` (git-ignored); in CI, in the job's environment.
- **Only portfolio-core is loaded**, without SCF or Polylang: the code under
  test must work without them, and it does (`function_exists()` checks).
- **Cloudflare is never called:** siteverify is answered by a
  `pre_http_request` filter, so tests run offline and decide whether
  Turnstile passes. The `portfolio_core_turnstile_keys` filter stands in
  for an environment without keys.
- **CI:** a `phpunit` job on PHP 8.0 with MySQL 8.0 as a service.
- Tests are linted with the rest (PHPCS), except for the rules a test file
  cannot follow: class file names (PHPUnit finds `*Test.php`), doc comments
  on test methods (the name says what each checks), and unprefixed constants
  in the config, which are the ones WordPress reads.

## 2026-10-22 — Tooling on Node 24

Node 20 was holding the tooling back: npm 10 rejected the lockfile, several
dependencies of `@wordpress/scripts` and Playwright already require Node 22+,
and Playground CLI needed its own Node 24 call. Node 24 is the LTS line;
26 is ruled out because Playground CLI fails on it (decision 06).

- `.nvmrc` pins `24`; `package.json` declares `"node": "^24.15.0"`. CI reads
  `.nvmrc` (`node-version-file`). Locally `nvm use` switches the repo only;
  the machine default is left alone.
- Since Node 22, `node --test` no longer takes a directory, so the token and
  CSS tests run with a glob (`'tests/tokens/*.test.mjs'`).
