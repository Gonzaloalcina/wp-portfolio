<?php
/**
 * Service: H1 with the problem it solves and a button to the form, the text
 * from the editor, what it includes, its proof (projects and notes), then
 * the request form with this service preselected, as the Services hub ends.
 * Wide layout (site--second).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );
the_post();

portfolio_theme_component( 'breadcrumb-a', array( 'items' => portfolio_theme_breadcrumbs() ) );

portfolio_theme_component(
	'hero-a',
	array(
		'title'   => get_the_title(),
		'intro'   => portfolio_theme_field( 'service_problem' ),
		'actions' => array(
			'button' => portfolio_theme_request_button( 'service_hero', get_post_field( 'post_name' ) ),
		),
	)
);

portfolio_theme_component( 'prose-a', array( 'content' => portfolio_theme_content() ) );

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'includes',
		'title'   => __( 'What it includes', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_service_includes( get_the_ID() ) ),
			),
		),
	)
);

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'proof',
		'title'   => __( 'Proof', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_service_proof( get_the_ID() ) ),
			),
		),
		'more'    => array(
			'title' => __( 'All services', 'portfolio-theme' ),
			'url'   => portfolio_theme_page_url( '/services/' ),
		),
	)
);

portfolio_theme_request_section( get_post_field( 'post_name' ) );

get_footer();
