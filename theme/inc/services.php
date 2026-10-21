<?php
/**
 * Services: the hub's fields and rows, and the request section that ends
 * the hub and every service page.
 *
 * The hub helps a visitor choose by situation (each service with the line
 * that says when it fits, and its price where there is one), then asks for
 * information in a form with the service already chosen when the visitor
 * comes from a service page or a `?service=<slug>` link, so a request
 * arrives classified.
 *
 * The form stays a static placeholder until portfolio-core provides its
 * endpoint. Analytics hooks are data-* attributes (cta_click,
 * generate_lead); the dataLayer push is a separate piece.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Services field group, shown on pages with the Services
 * template: the "How it works" steps.
 */
function portfolio_theme_register_services_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_portfolio_services',
			'title'    => __( 'Services', 'portfolio-theme' ),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/services.php',
					),
				),
			),
			'fields'   => array(
				array(
					'key'          => 'field_portfolio_services_steps',
					'label'        => __( 'How it works', 'portfolio-theme' ),
					'name'         => 'services_steps',
					'type'         => 'repeater',
					'layout'       => 'table',
					'instructions' => __( 'Three or four steps, from the first message to the quote. Empty: the section hides.', 'portfolio-theme' ),
					'button_label' => __( 'Add step', 'portfolio-theme' ),
					'sub_fields'   => array(
						array(
							'key'      => 'field_portfolio_services_step_title',
							'label'    => __( 'Title', 'portfolio-theme' ),
							'name'     => 'title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'       => 'field_portfolio_services_step_text',
							'label'     => __( 'Line', 'portfolio-theme' ),
							'name'      => 'text',
							'type'      => 'text',
							'maxlength' => 140,
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'portfolio_theme_register_services_fields' );

/**
 * Returns the services as hub rows: name (linked), the situation it fits
 * (the problem line when empty, so no row is blank) and the price as meta.
 *
 * @return array<int, array{title: string, url: string, meta: string, text: string, data: array}>
 */
function portfolio_theme_services_hub_rows() {
	return array_map(
		static function ( $post ) {
			$situation = portfolio_theme_field( 'service_situation', $post->ID );

			return array(
				'title' => get_the_title( $post ),
				'url'   => get_permalink( $post ),
				'meta'  => portfolio_theme_field( 'service_price', $post->ID ),
				'text'  => '' !== $situation ? $situation : portfolio_theme_field( 'service_problem', $post->ID ),
				'data'  => array(
					'cta-location' => 'services_row',
					'service'      => $post->post_name,
				),
			);
		},
		portfolio_theme_services()
	);
}

/**
 * Returns the hub's "How it works" steps as row-a items.
 *
 * @param int $post_id Services page ID.
 * @return array<int, array{title: string, text: string}>
 */
function portfolio_theme_services_steps( $post_id ) {
	$rows = function_exists( 'get_field' ) ? get_field( 'services_steps', $post_id ) : null;
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$items = array();
	foreach ( $rows as $row ) {
		$title = trim( (string) ( $row['title'] ?? '' ) );
		if ( '' !== $title ) {
			$items[] = array(
				'title' => $title,
				'text'  => trim( (string) ( $row['text'] ?? '' ) ),
			);
		}
	}

	return $items;
}

/**
 * The button that leads to the request form on the same page.
 *
 * @param string $location cta_location for analytics (services_hero, service_hero).
 * @param string $service  Service slug, when the page is a service.
 * @return array{title: string, url: string, data: array}
 */
function portfolio_theme_request_button( $location, $service = '' ) {
	return array(
		'title' => __( 'Request information', 'portfolio-theme' ),
		'url'   => '#request',
		'data'  => array(
			'cta-location' => $location,
			'service'      => $service,
		),
	);
}

/**
 * The service options of the request form: the published services in the
 * current language and "Not sure", after an empty choice.
 *
 * @return array<string, string> Slug => label.
 */
function portfolio_theme_request_options() {
	$options = array( '' => __( 'Choose one', 'portfolio-theme' ) );
	foreach ( portfolio_theme_services() as $post ) {
		$options[ $post->post_name ] = get_the_title( $post );
	}

	if ( count( $options ) < 2 ) {
		return array();
	}

	$options['not-sure'] = __( 'Not sure', 'portfolio-theme' );

	return $options;
}

/**
 * Renders the request section that ends the Services hub and every service
 * page, with a service preselected: the one given, else `?service=<slug>`.
 * A slug that is not a published service preselects nothing.
 *
 * @param string $service_slug Service to preselect.
 */
function portfolio_theme_request_section( $service_slug = '' ) {
	if ( '' === $service_slug && isset( $_GET['service'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only: preselects a choice.
		$service_slug = sanitize_title( wp_unslash( $_GET['service'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only: preselects a choice.
	}

	$options    = portfolio_theme_request_options();
	$privacy    = get_privacy_policy_url();
	$connection = portfolio_theme_form_connection( 'services_request' );
	$reply      = __( 'I reply within 2 working days.', 'portfolio-theme' );

	portfolio_theme_component(
		'section-a',
		array(
			'id'      => 'request',
			'title'   => __( 'Request information', 'portfolio-theme' ),
			'variant' => 'second',
			'content' => array(
				array(
					'name' => 'form-a',
					'args' => $connection + array(
						'label'  => __( 'Request information', 'portfolio-theme' ),
						'fields' => array(
							array(
								'name'         => 'name',
								'label'        => __( 'Name', 'portfolio-theme' ),
								'required'     => true,
								'autocomplete' => 'name',
								'maxlength'    => 100,
							),
							array(
								'name'         => 'email',
								'label'        => __( 'Email', 'portfolio-theme' ),
								'type'         => 'email',
								'required'     => true,
								'autocomplete' => 'email',
								'maxlength'    => 254,
							),
							array(
								'name'     => 'service',
								'label'    => __( 'What do you need?', 'portfolio-theme' ),
								'type'     => 'select',
								'required' => true,
								'options'  => $options,
								'selected' => isset( $options[ $service_slug ] ) ? $service_slug : '',
							),
							array(
								'name'         => 'site',
								'label'        => __( 'Site URL', 'portfolio-theme' ),
								'type'         => 'text',
								'inputmode'    => 'url',
								'autocomplete' => 'url',
								'maxlength'    => 200,
								'hint'         => __( 'If you already have one.', 'portfolio-theme' ),
							),
							array(
								'name'      => 'message',
								'label'     => __( 'Message', 'portfolio-theme' ),
								'type'      => 'textarea',
								'required'  => true,
								'maxlength' => 5000,
								'hint'      => __( 'What it is and when it starts.', 'portfolio-theme' ),
							),
						),
						'button' => __( 'Send request', 'portfolio-theme' ),
						'note'   => $connection ? $reply : $reply . ' ' . __( 'The form is not connected yet.', 'portfolio-theme' ),
						'data'   => array(
							'form-id'      => 'services_request',
							'cta-location' => 'services_form',
							'page-type'    => is_singular( 'service' ) ? 'service' : 'services',
						),
					),
				),
			),
			'more'    => $privacy
				? array(
					'title' => __( 'How the data you send is used', 'portfolio-theme' ),
					'url'   => $privacy,
				)
				: array(),
		)
	);
}
