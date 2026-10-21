<?php
/**
 * Forms: connects form-a to the portfolio-core endpoint.
 *
 * The plugin owns the logic (REST endpoint, Turnstile, honeypot, rate
 * limiting, storage); the theme owns the markup and the wording. Without the
 * plugin, or without Turnstile keys, a form stays a placeholder: no action
 * and a disabled button.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * The form-a args that connect a form to the endpoint, or none when it
 * cannot be sent.
 *
 * @param string $form_id Form ID the endpoint knows: contact, services_request.
 * @return array action, challenge (Turnstile site key), hidden, trap and messages; empty when not connected.
 */
function portfolio_theme_form_connection( $form_id ) {
	if ( ! function_exists( 'portfolio_core_contact_endpoint' ) || '' === portfolio_core_turnstile_site_key() ) {
		return array();
	}

	return array(
		'action'    => portfolio_core_contact_endpoint(),
		'challenge' => portfolio_core_turnstile_site_key(),
		'hidden'    => array(
			'form_id' => $form_id,
			'lang'    => function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '',
		),
		// Bots fill a field called "website"; people never see it.
		'trap'      => 'website',
		'messages'  => portfolio_theme_form_messages(),
	);
}

/**
 * What the form says while and after it sends, by the codes the endpoint
 * returns. Field codes: required, invalid, too_short, too_long.
 *
 * @return array<string, string|array<string, string>>
 */
function portfolio_theme_form_messages() {
	return array(
		'sending'          => __( 'Sending…', 'portfolio-theme' ),
		'sent'             => __( 'Thank you. Your message has been sent.', 'portfolio-theme' ),
		'invalid_fields'   => __( 'Some fields need a change. They are marked below.', 'portfolio-theme' ),
		'challenge_failed' => __( 'The spam check did not pass. Reload the page and try again.', 'portfolio-theme' ),
		'rate_limited'     => __( 'Too many messages in a short time. Try again in an hour.', 'portfolio-theme' ),
		'error'            => __( 'The message could not be sent. Try again in a few minutes.', 'portfolio-theme' ),
		'fields'           => array(
			'required'  => __( 'This field is required.', 'portfolio-theme' ),
			'invalid'   => __( 'This value is not valid.', 'portfolio-theme' ),
			'email'     => __( 'Enter an email address, like name@example.com.', 'portfolio-theme' ),
			'url'       => __( 'Enter a web address, like example.com.', 'portfolio-theme' ),
			'too_short' => __( 'Add a few more words.', 'portfolio-theme' ),
			'too_long'  => __( 'This is too long.', 'portfolio-theme' ),
		),
	);
}

/**
 * Enqueues the module that sends connected forms. form-a calls it when it
 * renders one; modules print in the footer, so this is in time.
 */
function portfolio_theme_enqueue_form() {
	$file = 'assets/js/form-a.js';

	wp_enqueue_script_module(
		'portfolio-theme/form-a',
		get_theme_file_uri( $file ),
		array(),
		(string) filemtime( get_theme_file_path( $file ) )
	);
}
