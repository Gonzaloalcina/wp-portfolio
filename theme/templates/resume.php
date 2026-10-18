<?php
/**
 * Template Name: Resume
 *
 * Resume: H1, intro and the PDF download, then one section per Resume field
 * section (experience, skills…) as rows. Printable: the print styles hide
 * the site chrome. Wide layout (site--second).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );
the_post();

$portfolio_pdf = portfolio_theme_field( 'resume_pdf' );

portfolio_theme_component(
	'hero-a',
	array(
		'title'   => get_the_title(),
		'intro'   => portfolio_theme_page_intro(),
		'actions' => array(
			'button' => array(
				'title' => $portfolio_pdf ? __( 'Download the PDF', 'portfolio-theme' ) : '',
				'url'   => $portfolio_pdf,
			),
			'links'  => array(
				array(
					'title' => __( 'Contact', 'portfolio-theme' ),
					'url'   => home_url( '/contact/' ),
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
