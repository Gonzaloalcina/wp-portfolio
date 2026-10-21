<?php
/**
 * Consent and analytics: Google Consent Mode v2 in basic mode, Google Tag
 * Manager, and the dataLayer events.
 *
 * Basic mode: nothing from Google loads until the visitor accepts. An inline
 * script at the top of <head> (assets/js/consent.js) sets every Consent Mode
 * signal to denied, pushes the page context to the dataLayer and reads the
 * visitor's choice from the `portfolio_consent` cookie. Only with
 * analytics accepted does it grant analytics_storage and load GTM; GTM then
 * runs GA4. Ad signals stay denied: the site shows no ads.
 *
 * The banner itself (asking, and "Cookie settings" to change the answer) is
 * the theme's consent-a component, which calls window.portfolioConsent.
 * Without JavaScript there is no banner and nothing loads.
 *
 * The GTM container ID lives in wp-config.php (PORTFOLIO_GTM_ID), like the
 * Turnstile keys. Without it the dataLayer still fills, so events can be
 * checked locally in the console, but no request leaves the site.
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Version of what visitors consent to. Raise it when the tools or their use
 * change: every stored choice is then void and the banner asks again.
 */
define( 'PORTFOLIO_CORE_CONSENT_VERSION', 1 );

/**
 * How long a choice is kept, in days. The AEPD guide accepts up to 24
 * months; 12 asks again sooner, for accepts and rejects alike.
 */
define( 'PORTFOLIO_CORE_CONSENT_DAYS', 365 );

/**
 * The GTM container ID from wp-config.php, when it is a valid one.
 *
 * @return string GTM-XXXXXXX, or empty.
 */
function portfolio_core_gtm_id() {
	$id = defined( 'PORTFOLIO_GTM_ID' ) ? (string) PORTFOLIO_GTM_ID : '';

	/**
	 * Filters the GTM container ID. Used by the PHPUnit tests.
	 *
	 * @param string $id Container ID.
	 */
	$id = strtoupper( trim( (string) apply_filters( 'portfolio_core_gtm_id', $id ) ) );

	return preg_match( '/^GTM-[A-Z0-9]{4,12}$/', $id ) ? $id : '';
}

/**
 * What kind of page this is, for the dataLayer: home, notes, note, project,
 * service, 404, a page template's name (about, work, services, contact,
 * resume…) or page.
 *
 * @return string
 */
function portfolio_core_page_type() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_home() ) {
		return 'notes';
	}
	if ( is_404() ) {
		return '404';
	}
	if ( is_singular( 'post' ) ) {
		return 'note';
	}
	if ( is_singular( array( 'project', 'service' ) ) ) {
		return (string) get_post_type();
	}
	if ( is_page() ) {
		$template = (string) get_page_template_slug( get_queried_object_id() );
		if ( '' !== $template ) {
			return sanitize_key( basename( $template, '.php' ) );
		}
		// The design system page has its own file, page-design-system.php.
		return 'design-system' === get_post_field( 'post_name', get_queried_object_id() ) ? 'design-system' : 'page';
	}

	return 'other';
}

/**
 * Every published service slug, in every language, mapped to the slug of
 * the service in the default language, so a report counts
 * `mantenimiento-wordpress` and `wordpress-maintenance` as one service.
 * Without Polylang there is nothing to map.
 *
 * @return array<string, string> Slug => default-language slug.
 */
function portfolio_core_service_slugs() {
	if ( ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_default_language' ) ) {
		return array();
	}

	$ids = get_posts(
		array(
			'post_type'        => 'service',
			'post_status'      => 'publish',
			'fields'           => 'ids',
			'posts_per_page'   => 50,
			'no_found_rows'    => true,
			'suppress_filters' => false,
			'lang'             => '', // Polylang: any language.
		)
	);

	$map = array();
	foreach ( $ids as $id ) {
		$default = pll_get_post( $id, pll_default_language() );
		if ( ! $default || $default === $id ) {
			continue;
		}
		$map[ (string) get_post_field( 'post_name', $id ) ] = (string) get_post_field( 'post_name', $default );
	}

	return $map;
}

/**
 * Settings the head script reads.
 *
 * @return array{version: int, days: int, gtm: string, pageType: string, lang: string, services: array<string, string>}
 */
function portfolio_core_consent_config() {
	return array(
		'version'  => PORTFOLIO_CORE_CONSENT_VERSION,
		'days'     => PORTFOLIO_CORE_CONSENT_DAYS,
		'gtm'      => portfolio_core_gtm_id(),
		'pageType' => portfolio_core_page_type(),
		'lang'     => function_exists( 'pll_current_language' ) ? (string) pll_current_language() : substr( get_locale(), 0, 2 ),
		'services' => (object) portfolio_core_service_slugs(),
	);
}

/**
 * Prints the consent script first thing in <head>, before any tag could
 * read the dataLayer. Inline and synchronous on purpose: the defaults must
 * be in place before GTM, and the banner must know whether to show before
 * first paint (data-consent on <html>), so it never flashes.
 */
function portfolio_core_print_consent_script() {
	$file = dirname( __DIR__ ) . '/assets/js/consent.js';
	if ( ! is_readable( $file ) ) {
		return;
	}

	wp_print_inline_script_tag(
		'window.portfolioConsentConfig = ' . wp_json_encode( portfolio_core_consent_config() ) . ";\n"
		. file_get_contents( $file ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- A local file of the plugin.
	);
}
add_action( 'wp_head', 'portfolio_core_print_consent_script', 1 );

/**
 * Enqueues the dataLayer events module (CTA clicks and leads).
 */
function portfolio_core_enqueue_datalayer_events() {
	$file = 'assets/js/datalayer-events.js';

	wp_enqueue_script_module(
		'portfolio-core/datalayer-events',
		plugins_url( $file, dirname( __DIR__ ) . '/portfolio-core.php' ),
		array(),
		(string) filemtime( dirname( __DIR__ ) . '/' . $file )
	);
}
add_action( 'wp_enqueue_scripts', 'portfolio_core_enqueue_datalayer_events' );
