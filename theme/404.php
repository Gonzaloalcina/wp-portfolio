<?php
/**
 * 404: a helpful dead end. H1, one line, and links to the main sections.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );

portfolio_theme_component(
	'title-a',
	array(
		'title' => __( 'Page not found', 'portfolio-theme' ),
		'intro' => __( 'This address does not lead to a page on this site. It may have moved, or the link may be wrong.', 'portfolio-theme' ),
	)
);

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'try',
		'title'   => __( 'Try one of these', 'portfolio-theme' ),
		'actions' => array(
			'links' => array(
				array(
					'title' => __( 'Home', 'portfolio-theme' ),
					'url'   => portfolio_theme_page_url( '/' ),
				),
				array(
					'title' => __( 'Work', 'portfolio-theme' ),
					'url'   => portfolio_theme_page_url( '/work/' ),
				),
				array(
					'title' => __( 'Services', 'portfolio-theme' ),
					'url'   => portfolio_theme_page_url( '/services/' ),
				),
				array(
					'title' => __( 'Notes', 'portfolio-theme' ),
					'url'   => portfolio_theme_page_url( '/notes/' ),
				),
				array(
					'title' => __( 'Contact', 'portfolio-theme' ),
					'url'   => portfolio_theme_page_url( '/contact/' ),
				),
			),
		),
	)
);

get_footer();
