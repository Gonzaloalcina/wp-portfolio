<?php
/**
 * Theme bootstrap.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

define( 'PORTFOLIO_THEME_VERSION', wp_get_theme()->get( 'Version' ) );

require_once get_theme_file_path( 'inc/design-system.php' );
require_once get_theme_file_path( 'inc/theme-toggle.php' );
require_once get_theme_file_path( 'inc/cleanup.php' );
require_once get_theme_file_path( 'inc/editor.php' );
require_once get_theme_file_path( 'inc/security.php' );
require_once get_theme_file_path( 'inc/media.php' );
require_once get_theme_file_path( 'inc/icons.php' );

/**
 * Registers theme supports.
 */
function portfolio_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
}
add_action( 'after_setup_theme', 'portfolio_theme_setup' );

/**
 * Enqueues a theme stylesheet. The file's modification time is the version,
 * so browsers fetch it again whenever it changes.
 *
 * @param string   $handle Style handle.
 * @param string   $file   Path relative to the theme root.
 * @param string[] $deps   Handles this style depends on.
 */
function portfolio_theme_enqueue_style( $handle, $file, $deps = array() ) {
	$path = get_theme_file_path( $file );

	wp_enqueue_style(
		$handle,
		get_theme_file_uri( $file ),
		$deps,
		file_exists( $path ) ? (string) filemtime( $path ) : PORTFOLIO_THEME_VERSION
	);
}

/**
 * Enqueues front-end assets. Order: fonts and tokens, then base styles, then
 * page-specific styles.
 */
function portfolio_theme_enqueue_assets() {
	portfolio_theme_enqueue_style( 'portfolio-theme-fonts', 'assets/css/fonts.css' );
	portfolio_theme_enqueue_style( 'portfolio-theme-tokens', 'assets/css/tokens.css' );
	portfolio_theme_enqueue_style( 'portfolio-theme-base', 'assets/css/base.css', array( 'portfolio-theme-fonts', 'portfolio-theme-tokens' ) );

	// style.css is not enqueued: it only holds the theme header WordPress
	// reads to recognise the theme, so loading it would be an empty request.

	if ( is_page( 'design-system' ) ) {
		portfolio_theme_enqueue_style( 'portfolio-theme-design-system', 'assets/css/design-system.css', array( 'portfolio-theme-base' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'portfolio_theme_enqueue_assets' );

/**
 * Preloads the latin font files every page uses, so they start downloading
 * before the CSS is parsed. Latin-ext and italic load on demand.
 */
function portfolio_theme_preload_fonts() {
	$fonts = array(
		'assets/fonts/inter/inter-latin-wght-normal.woff2',
		'assets/fonts/newsreader/newsreader-latin-wght-normal.woff2',
	);

	foreach ( $fonts as $font ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( get_theme_file_uri( $font ) )
		);
	}
}
add_action( 'wp_head', 'portfolio_theme_preload_fonts', 1 );
