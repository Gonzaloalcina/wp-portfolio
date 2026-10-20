<?php
/**
 * Template Name: About
 *
 * About: the entity page. Hero with the person's name as H1, the short bio
 * and a larger portrait (featured image); facts; the long bio (page text);
 * How I work; Where I have done it (the Resume's first section); Tools and
 * AI; Testimonials (the Home's); Other things; then the closing Contact
 * section. A section with no content does not render.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );
the_post();

$portfolio_name  = portfolio_theme_setting( 'site_person_name' );
$portfolio_title = $portfolio_name ? $portfolio_name : get_the_title();
$portfolio_intro = portfolio_theme_setting( 'site_bio', true );

portfolio_theme_component(
	'hero-a',
	array(
		'title'     => $portfolio_title,
		'intro'     => $portfolio_intro ? $portfolio_intro : portfolio_theme_page_intro(),
		'image_id'  => (int) get_post_thumbnail_id(),
		'image_alt' => $portfolio_title,
		'variant'   => 'second',
	)
);

portfolio_theme_component( 'facts-a', array( 'items' => portfolio_theme_about_facts() ) );

portfolio_theme_component( 'prose-a', array( 'content' => portfolio_theme_content() ) );

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'how-i-work',
		'title'   => __( 'How I work with the people around a website', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_about_how_rows() ),
			),
		),
	)
);

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'where',
		'title'   => __( 'Where I have done it', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => portfolio_theme_about_where_rows() ),
			),
		),
		'more'    => array(
			'title' => __( 'Full resume', 'portfolio-theme' ),
			'url'   => home_url( '/resume/' ),
		),
	)
);

portfolio_theme_component(
	'section-a',
	array(
		'id'    => 'tools',
		'title' => __( 'Tools and AI', 'portfolio-theme' ),
		'text'  => portfolio_theme_field( 'about_tools' ),
		'more'  => array(
			'title' => __( 'How this site is built', 'portfolio-theme' ),
			'url'   => home_url( '/how-this-site-is-built/' ),
		),
	)
);

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'testimonials',
		'title'   => __( 'Testimonials', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'quote-list-a',
				'args' => array( 'items' => portfolio_theme_home_quotes( 2 ) ),
			),
		),
	)
);

portfolio_theme_component(
	'section-a',
	array(
		'id'    => 'other-things',
		'title' => __( 'Other things', 'portfolio-theme' ),
		'text'  => portfolio_theme_field( 'about_outside' ),
	)
);

portfolio_theme_contact_section();

get_footer();
