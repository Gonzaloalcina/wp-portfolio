<?php
/**
 * Home: hero, the sites I've built (with also built), services, testimonials,
 * latest notes and contact, built from components. Wide layout (site--second).
 *
 * Text is read from the Home SCF fields (inc/home.php); Sites I've built and
 * Also built are projects placed by their "Home" field, Services are the
 * published services shown on the Home. An empty field
 * renders nothing, and a section with no content hides itself.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );

$portfolio_cta = array(
	'title' => portfolio_theme_home_text( 'home_cta_label' ),
	'url'   => portfolio_theme_page_url( '/contact/' ),
);

$portfolio_headline = portfolio_theme_home_text( 'home_headline' );

portfolio_theme_component(
	'hero-a',
	array(
		'title'   => $portfolio_headline ? $portfolio_headline : get_bloginfo( 'name' ),
		'intro'   => portfolio_theme_home_text( 'home_intro' ),
		'actions' => array(
			'button' => $portfolio_cta,
			'links'  => array(
				array(
					'title' => __( 'Projects', 'portfolio-theme' ),
					'url'   => portfolio_theme_page_url( '/projects/' ),
				),
				array(
					'title' => __( 'About', 'portfolio-theme' ),
					'url'   => portfolio_theme_page_url( '/about/' ),
				),
			),
		),
	)
);

$portfolio_sections = array(
	array(
		'id'      => 'selected-work',
		'title'   => __( 'Sites I\'ve built', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_home_project_rows( 'selected', 3 ) ),
			),
			array(
				'title' => __( 'Also built', 'portfolio-theme' ),
				'name'  => 'list-a',
				'args'  => array( 'items' => portfolio_theme_home_project_rows( 'also_built' ) ),
			),
		),
		'more'    => array(
			'title' => __( 'All projects', 'portfolio-theme' ),
			'url'   => portfolio_theme_page_url( '/projects/' ),
		),
	),
	array(
		'id'      => 'services',
		'title'   => __( 'Services', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_service_rows( true ) ),
			),
		),
		'more'    => array(
			'title' => __( 'All services', 'portfolio-theme' ),
			'url'   => portfolio_theme_page_url( '/services/' ),
		),
	),
	array(
		'id'      => 'testimonials',
		'title'   => __( 'Testimonials', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'quote-list-a',
				'args' => array( 'items' => portfolio_theme_home_quotes() ),
			),
		),
	),
	array(
		'id'      => 'latest-notes',
		'title'   => __( 'Latest notes', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_latest_note_rows( 3 ) ),
			),
		),
		'more'    => array(
			'title' => __( 'All notes', 'portfolio-theme' ),
			'url'   => portfolio_theme_page_url( '/notes/' ),
		),
	),
	array(
		'id'      => 'contact',
		'title'   => __( 'Contact', 'portfolio-theme' ),
		'variant' => 'second',
		'text'    => portfolio_theme_home_text( 'home_contact_text' ),
		'actions' => array(
			'button' => $portfolio_cta,
			// Resume first, then the profiles from Site settings (Home brief 2.5).
			'links'  => array_merge(
				array(
					array(
						'title' => __( 'Resume', 'portfolio-theme' ),
						'url'   => portfolio_theme_page_url( '/resume/' ),
					),
				),
				portfolio_theme_profile_links()
			),
		),
	),
);

foreach ( $portfolio_sections as $portfolio_section ) {
	portfolio_theme_component( 'section-a', $portfolio_section );
}

get_footer();
