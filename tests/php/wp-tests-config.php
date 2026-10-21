<?php
/**
 * Configuration for WordPress's test library.
 *
 * The database comes from the environment: CI sets the variables, and
 * locally bootstrap.php reads them from .env.testing (git-ignored). The test
 * library empties this database on every run, so it must never be the
 * site's: a name without "test" in it is refused.
 *
 * @package PortfolioCore
 */

$portfolio_db_name = (string) getenv( 'WP_TESTS_DB_NAME' );
if ( false === strpos( $portfolio_db_name, 'test' ) ) {
	fwrite( STDERR, "WP_TESTS_DB_NAME must be a test database (its name must contain \"test\").\n" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- CLI, before WordPress loads.
	exit( 1 );
}

// WordPress core from Composer (roots/wordpress-no-content), not a site install.
define( 'ABSPATH', dirname( __DIR__, 2 ) . '/vendor/roots/wordpress-no-content/' );

define( 'DB_NAME', $portfolio_db_name );
define( 'DB_USER', (string) getenv( 'WP_TESTS_DB_USER' ) );
define( 'DB_PASSWORD', (string) getenv( 'WP_TESTS_DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'WP_TESTS_DB_HOST' ) ? (string) getenv( 'WP_TESTS_DB_HOST' ) : '127.0.0.1' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- WordPress reads this global.

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Portfolio tests' );
define( 'WP_PHP_BINARY', PHP_BINARY );
define( 'WPLANG', '' );
define( 'WP_DEBUG', true );

// Like the local site: the contact form falls back to Turnstile's test keys.
define( 'WP_ENVIRONMENT_TYPE', 'local' );
