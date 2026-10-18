<?php
/**
 * Template Name: Services
 *
 * Services hub: one row per published service (name and the problem it
 * solves), then the closing Contact section. Wide layout (site--second).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );
the_post();

portfolio_theme_component(
	'hero-a',
	array(
		'title' => get_the_title(),
		'intro' => portfolio_theme_page_intro(),
	)
);

portfolio_theme_component( 'prose-a', array( 'content' => portfolio_theme_content() ) );

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'all-services',
		'title'   => __( 'All services', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_service_rows() ),
			),
		),
	)
);

portfolio_theme_contact_section();

get_footer();
