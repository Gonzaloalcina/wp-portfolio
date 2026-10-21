# PHPUnit

Tests for `portfolio-core`, on WordPress's own test library. They run
against a database of their own, which the library empties on every run:
**never the site's database**. The config refuses a database name without
"test" in it.

## Set up once (local)

1. `composer install` (PHPUnit, the test library and a WordPress core in
   `vendor/`).
2. A MySQL database and user for the tests, e.g. `wp_portfolio_tests`.
3. A `.env.testing` file at the repo root (git-ignored):

   ```
   WP_TESTS_DB_NAME=wp_portfolio_tests
   WP_TESTS_DB_USER=wp_portfolio_tests
   WP_TESTS_DB_PASSWORD=…
   WP_TESTS_DB_HOST=127.0.0.1
   ```

Variables already set in the environment win over the file; that is how CI
passes its own.

## Run

```
composer run test
```

To run them on XAMPP's PHP 8.0, the version CI uses:
`/Applications/XAMPP/xamppfiles/bin/php vendor/bin/phpunit`.

## Writing tests

- One `*Test.php` file per plugin file, a class extending `WP_UnitTestCase`.
- Only the plugin is loaded (no SCF, no Polylang, no theme).
- No network: answer outside requests with a `pre_http_request` filter, as
  `ContactFormTest` does for Turnstile.
