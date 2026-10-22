<?php
/**
 * Brand: the favicon links in <head>. The icons live in assets/brand/ (see
 * docs/decisions/15-brand.md). A Site Icon set in the Customizer wins:
 * WordPress prints its own links then.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prints the favicon links: ICO as the fallback, SVG for browsers that take
 * it, and the Apple touch icon.
 */
function portfolio_theme_favicon_links() {
	if ( has_site_icon() ) {
		return;
	}

	$base = get_theme_file_uri( 'assets/brand/' );

	printf( '<link rel="icon" href="%s" sizes="48x48">' . "\n", esc_url( $base . 'favicon.ico' ) );
	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( $base . 'favicon.svg' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $base . 'apple-touch-icon.png' ) );
}
add_action( 'wp_head', 'portfolio_theme_favicon_links', 2 );
