<?php
/**
 * Template Name: Projects
 *
 * Projects hub: case studies (featured projects), then every other project as a
 * row. Every row links to the project's page on this site. Only projects whose
 * client may be named are listed. Wide layout (site--second).
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
		'id'      => 'case-studies',
		'title'   => __( 'Case studies', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_project_rows( portfolio_theme_projects( true ) ) ),
			),
		),
	)
);

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'projects',
		'title'   => __( 'Projects', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_project_rows( portfolio_theme_projects( false ) ) ),
			),
		),
	)
);

portfolio_theme_contact_section();

get_footer();
