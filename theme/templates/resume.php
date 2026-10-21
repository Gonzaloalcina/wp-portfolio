<?php
/**
 * Template Name: Resume
 *
 * Resume: a transcription of the CV. H1, intro and a link to Contact, then
 * one section per Resume field section (experience, skills…) as rows. No
 * PDF download. Printable: the print styles hide the site chrome. Wide
 * layout (site--second).
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
			'links' => array(
				array(
					'title' => __( 'Contact', 'portfolio-theme' ),
					'url'   => portfolio_theme_page_url( '/contact/' ),
				),
			),
		),
	)
);

portfolio_theme_component( 'prose-a', array( 'content' => portfolio_theme_content() ) );

foreach ( portfolio_theme_resume_sections( get_the_ID() ) as $portfolio_section ) {
	portfolio_theme_component(
		'section-a',
		array(
			'title'   => $portfolio_section['title'],
			'content' => array(
				array(
					'name' => 'list-a',
					'args' => array( 'items' => $portfolio_section['items'] ),
				),
			),
		)
	);
}

get_footer();
