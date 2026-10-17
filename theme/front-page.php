<?php
/**
 * Home: hero, selected work, services, latest notes and contact, built from
 * components. Wide layout (site--second).
 *
 * Text is read from the Home SCF fields (inc/home.php). An empty field
 * renders nothing, and a section with no content hides itself.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );

$portfolio_cta = array(
	'title' => portfolio_theme_home_text( 'home_cta_label' ),
	'url'   => home_url( '/contact/' ),
);

$portfolio_headline = portfolio_theme_home_text( 'home_headline' );

portfolio_theme_component(
	'hero-a',
	array(
		'title'       => $portfolio_headline ? $portfolio_headline : get_bloginfo( 'name' ),
		'intro'       => portfolio_theme_home_text( 'home_intro' ),
		'actions'     => array(
			'button' => $portfolio_cta,
			'links'  => array(
				array(
					'title' => __( 'Work', 'portfolio-theme' ),
					'url'   => home_url( '/work/' ),
				),
				array(
					'title' => __( 'About', 'portfolio-theme' ),
					'url'   => home_url( '/about/' ),
				),
			),
		),
		'image_id'    => function_exists( 'get_field' ) ? (int) get_field( 'home_portrait' ) : 0,
		'image_alt'   => get_bloginfo( 'name' ),
		// Preview only: draw the empty frame while there is no portrait.
		'image_frame' => true,
	)
);

$portfolio_sections = array(
	array(
		'id'    => 'selected-work',
		'title' => __( 'Selected work', 'portfolio-theme' ),
		'items' => portfolio_theme_home_rows( 'home_work' ),
		'more'  => array(
			'title' => __( 'All work', 'portfolio-theme' ),
			'url'   => home_url( '/work/' ),
		),
	),
	array(
		'id'    => 'services',
		'title' => __( 'Services', 'portfolio-theme' ),
		'items' => portfolio_theme_home_rows( 'home_services' ),
		'more'  => array(
			'title' => __( 'All services', 'portfolio-theme' ),
			'url'   => home_url( '/services/' ),
		),
	),
	array(
		'id'    => 'latest-notes',
		'title' => __( 'Latest notes', 'portfolio-theme' ),
		'items' => portfolio_theme_latest_note_rows( 3 ),
		'more'  => array(
			'title' => __( 'All notes', 'portfolio-theme' ),
			'url'   => home_url( '/notes/' ),
		),
	),
	array(
		'id'      => 'contact',
		'title'   => __( 'Contact', 'portfolio-theme' ),
		'text'    => portfolio_theme_home_text( 'home_contact_text' ),
		'actions' => array(
			'button' => $portfolio_cta,
			'links'  => portfolio_theme_home_rows( 'home_profile_links' ),
		),
	),
);

foreach ( $portfolio_sections as $portfolio_section ) {
	portfolio_theme_component( 'section-a', $portfolio_section );
}

get_footer();
