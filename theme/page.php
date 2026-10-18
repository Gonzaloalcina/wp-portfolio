<?php
/**
 * Default page: a text page (How this site is built, privacy, cookies,
 * legal notice). H1 with the last update date, intro, then the page text.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );
the_post();

portfolio_theme_component(
	'title-a',
	array(
		'title'    => get_the_title(),
		/* translators: %s: date the page was last updated. */
		'meta'     => sprintf( __( 'Updated %s', 'portfolio-theme' ), get_the_modified_date() ),
		'datetime' => get_the_modified_date( 'Y-m-d' ),
		'intro'    => portfolio_theme_page_intro(),
	)
);

portfolio_theme_component( 'prose-a', array( 'content' => portfolio_theme_content() ) );

get_footer();
