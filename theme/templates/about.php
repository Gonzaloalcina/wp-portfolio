<?php
/**
 * Template Name: About
 *
 * About: who Gonzalo is and how he works with stakeholders. Hero (H1, intro,
 * portrait from the featured image), the page text, then the closing
 * Contact section.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );
the_post();

portfolio_theme_component(
	'hero-a',
	array(
		'title'     => get_the_title(),
		'intro'     => portfolio_theme_page_intro(),
		'image_id'  => (int) get_post_thumbnail_id(),
		'image_alt' => get_bloginfo( 'name' ),
	)
);

portfolio_theme_component( 'prose-a', array( 'content' => portfolio_theme_content() ) );

portfolio_theme_contact_section();

get_footer();
