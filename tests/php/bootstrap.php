<?php
/**
 * PHPUnit bootstrap: loads WordPress's test library with portfolio-core
 * active. Only the plugin is loaded: SCF and Polylang are not, so the code
 * under test must work without them (it does, through function_exists()).
 *
 * @package PortfolioCore
 */

$portfolio_root = dirname( __DIR__, 2 );

// Composer: PHPUnit polyfills and wp-phpunit (sets WP_PHPUNIT__DIR).
require_once $portfolio_root . '/vendor/autoload.php';

// Local database settings: KEY=value lines, # for comments. Variables
// already set (CI) win.
$portfolio_env = $portfolio_root . '/.env.testing';
if ( is_readable( $portfolio_env ) ) {
	foreach ( (array) file( $portfolio_env, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $portfolio_line ) {
		$portfolio_pair = explode( '=', trim( $portfolio_line ), 2 );
		if ( 2 === count( $portfolio_pair ) && '#' !== $portfolio_line[0] && false === getenv( $portfolio_pair[0] ) ) {
			putenv( $portfolio_pair[0] . '=' . $portfolio_pair[1] );
		}
	}
}

putenv( 'WP_PHPUNIT__TESTS_CONFIG=' . __DIR__ . '/wp-tests-config.php' );

// The Composer core ships without wp-content/themes, and WordPress warns
// when no theme directory exists. An empty one is enough (vendor/ is
// generated, so this changes nothing in the repo).
$portfolio_themes = $portfolio_root . '/vendor/roots/wordpress-no-content/wp-content/themes';
if ( ! is_dir( $portfolio_themes ) ) {
	mkdir( $portfolio_themes, 0755, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_mkdir -- CLI, before WordPress loads.
}
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $portfolio_root . '/vendor/yoast/phpunit-polyfills' );

$portfolio_tests_dir = (string) getenv( 'WP_PHPUNIT__DIR' );
require_once $portfolio_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () use ( $portfolio_root ) {
		require $portfolio_root . '/plugin/portfolio-core.php';
	}
);

require $portfolio_tests_dir . '/includes/bootstrap.php';
