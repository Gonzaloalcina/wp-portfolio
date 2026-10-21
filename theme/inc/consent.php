<?php
/**
 * Consent banner: the consent-a component and the footer's "Cookie
 * settings" link. The consent logic (cookie, Consent Mode, GTM) is
 * portfolio-core's (plugin/inc/consent.php); without the plugin there is no
 * banner, and nothing to consent to.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether the plugin's consent layer is active.
 *
 * @return bool
 */
function portfolio_theme_has_consent() {
	return function_exists( 'portfolio_core_consent_config' );
}

/**
 * Renders the banner, first thing in <body> so screen readers and keyboard
 * users meet it before the page. It shows only when the head script set
 * data-consent="pending" on <html>.
 */
function portfolio_theme_consent_banner() {
	if ( ! portfolio_theme_has_consent() ) {
		return;
	}

	portfolio_theme_component(
		'consent-a',
		array(
			'label'  => __( 'Cookie consent', 'portfolio-theme' ),
			'text'   => __( 'I use Google Analytics to see which pages people read. It only runs if you accept.', 'portfolio-theme' ),
			'more'   => array(
				'title' => __( 'Cookie policy', 'portfolio-theme' ),
				'url'   => portfolio_theme_page_url( '/cookies/' ),
			),
			'accept' => __( 'Accept', 'portfolio-theme' ),
			'reject' => __( 'Reject', 'portfolio-theme' ),
		)
	);

	$file = 'assets/js/consent-a.js';
	wp_enqueue_script_module(
		'portfolio-theme/consent-a',
		get_theme_file_uri( $file ),
		array(),
		(string) filemtime( get_theme_file_path( $file ) )
	);
}

/**
 * The footer link that opens the banner again. Without JavaScript it is a
 * plain link to the cookie policy.
 *
 * @return array<int, array> Zero or one link-row-a link.
 */
function portfolio_theme_consent_link() {
	if ( ! portfolio_theme_has_consent() ) {
		return array();
	}

	return array(
		array(
			'title' => __( 'Cookie settings', 'portfolio-theme' ),
			'url'   => portfolio_theme_page_url( '/cookies/' ),
			'data'  => array( 'consent-open' => '1' ),
		),
	);
}
