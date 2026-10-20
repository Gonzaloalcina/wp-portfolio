<?php
/**
 * Service: H1 with the problem it solves and the contact button, the text
 * from the editor, what it includes, its proof (projects and notes), then
 * the closing Contact section. Wide layout (site--second).
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
			'button' => portfolio_theme_contact_button(),
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
			'url'   => home_url( '/services/' ),
		),
	)
);

portfolio_theme_contact_section();

get_footer();
