<?php
/**
 * Theme bootstrap.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

define( 'PORTFOLIO_THEME_VERSION', wp_get_theme()->get( 'Version' ) );

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
 * Enqueues front-end assets.
 */
function portfolio_theme_enqueue_assets() {
	wp_enqueue_style(
		'portfolio-theme-fonts',
		get_theme_file_uri( 'assets/css/fonts.css' ),
		array(),
		PORTFOLIO_THEME_VERSION
	);

	wp_enqueue_style(
		'portfolio-theme-tokens',
		get_theme_file_uri( 'assets/css/tokens.css' ),
		array(),
		PORTFOLIO_THEME_VERSION
	);

	wp_enqueue_style(
		'portfolio-theme',
		get_stylesheet_uri(),
		array( 'portfolio-theme-fonts', 'portfolio-theme-tokens' ),
		PORTFOLIO_THEME_VERSION
	);
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
