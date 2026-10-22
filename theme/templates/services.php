<?php
/**
 * Template Name: Services
 *
 * Services hub: helps a visitor choose by situation, then asks for
 * information. H1 with a button to the form, the text from the editor, one
 * row per published service (the situation it fits and its price), How it
 * works, then the request form instead of the generic Contact section.
 * Wide layout (site--second).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );
the_post();

portfolio_theme_component(
	'hero-a',
	array(
		'title'   => get_the_title(),
		'intro'   => portfolio_theme_page_intro(),
		'actions' => array(
			'button' => portfolio_theme_request_button( 'services_hero' ),
		),
	)
);

portfolio_theme_component( 'prose-a', array( 'content' => portfolio_theme_content() ) );

$portfolio_rows = portfolio_theme_services_hub_rows();

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'which-one',
		'title'   => __( 'Which one do you need?', 'portfolio-theme' ),
		// Only when a row shows a figure ("Quoted" is not one).
		'text'    => preg_grep( '/\d/', array_column( $portfolio_rows, 'meta' ) ) ? __( 'Prices exclude VAT.', 'portfolio-theme' ) : '',
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => $portfolio_rows ),
			),
		),
	)
);

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'how-it-works',
		'title'   => __( 'How it works', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_services_steps( get_the_ID() ) ),
			),
		),
	)
);

portfolio_theme_request_section();

get_footer();
