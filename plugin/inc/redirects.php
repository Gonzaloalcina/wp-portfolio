<?php
/**
 * Redirects from the previous gonzaloalcina.dev (an Elementor site, Spanish
 * only, no /es/ prefix) to the pages that replace each address.
 *
 * Projects that are still on the site go to their own page; the ones that
 * are not, and the old portfolio categories, go to the Spanish Projects hub;
 * the blog categories go to the Spanish Notes. /contacto/ and /proyectos/
 * need no rule: WordPress finds the Spanish page by its slug and sends the
 * request to its /es/ URL. See docs/decisions/12-deploy.md.
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the old addresses and where each one goes, as paths under the
 * site's home, decoded and with a trailing slash: old => new.
 *
 * @return array<string, string>
 */
function portfolio_core_legacy_redirects() {
	$redirects = array(
		// Projects still on the site.
		'proyectos/jalea-real-photography/'    => 'es/proyectos/jalea-real-photography/',
		'proyectos/ramon-esteve-estudio/'      => 'es/proyectos/ramon-esteve/',
		'proyectos/xufa-es/'                   => 'es/proyectos/xufa/',
		// The old slug ends in U+FE0F, an invisible emoji variation selector.
		"proyectos/relyon-relocation\u{FE0F}/" => 'es/proyectos/relyon-relocation/',
		// Blog categories.
		'category/blog/'                       => 'es/notas/',
		'category/wordpress/'                  => 'es/notas/',
	);

	// Projects that are no longer listed, and the old portfolio categories.
	$to_projects = array(
		'proyectos/al-traves/',
		'proyectos/colegio-esclavas-de-maria/',
		'proyectos/faltan-gatos/',
		'proyectos/flora-veiga/',
		'proyectos/gusto-por-lo-nuestro/',
		'proyectos/h-arquitectos/',
		'proyectos/hana-lole/',
		'proyectos/opalo/',
		'proyectos/plan-proa/',
		'proyectos/the-sybarite-flamingo/',
		'category/agencias/',
		'category/arquitectura/',
		'category/arte/',
		'category/corporativo/',
		'category/fotografia/',
		'category/gastronomia/',
		'category/ocio/',
		'category/tienda-online/',
		'category/viajes/',
		'category/woocommerce/',
	);
	foreach ( $to_projects as $old ) {
		$redirects[ $old ] = 'es/proyectos/';
	}

	return (array) apply_filters( 'portfolio_core_legacy_redirects', $redirects );
}

/**
 * Sends an old address to its new page with a 301, query kept. Only on a
 * 404, so a real page always wins, and before core guesses a permalink.
 */
function portfolio_core_redirect_legacy_urls() {
	if ( ! is_404() ) {
		return;
	}

	$request = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$home    = trailingslashit( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ) );
	$path    = rawurldecode( (string) wp_parse_url( $request, PHP_URL_PATH ) );
	if ( 0 !== strpos( $path, $home ) ) {
		return;
	}
	$path = trailingslashit( substr( $path, strlen( $home ) ) );

	$redirects = portfolio_core_legacy_redirects();
	if ( ! isset( $redirects[ $path ] ) ) {
		return;
	}

	$target = home_url( '/' . $redirects[ $path ] );
	$query  = (string) wp_parse_url( $request, PHP_URL_QUERY );
	wp_safe_redirect( '' === $query ? $target : $target . '?' . $query, 301 );
	exit;
}
add_action( 'template_redirect', 'portfolio_core_redirect_legacy_urls', 1 );
