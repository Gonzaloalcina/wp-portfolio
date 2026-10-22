<?php
/**
 * Notes: the posts index (WordPress loads home.php for the page set as the
 * posts page). Not to be confused with inc/home.php, the Home's fields.
 *
 * H1 and intro from the posts page, then the notes as rows (date, title, one
 * line), the pagination and the closing contact section. Wide layout
 * (site--second).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( null, array( 'variant' => 'second' ) );

$portfolio_page_id = (int) get_option( 'page_for_posts' );

portfolio_theme_component(
	'title-a',
	array(
		'title' => $portfolio_page_id ? get_the_title( $portfolio_page_id ) : __( 'Notes', 'portfolio-theme' ),
		'intro' => $portfolio_page_id ? portfolio_theme_page_intro( $portfolio_page_id ) : '',
	)
);

global $wp_query;

portfolio_theme_component(
	'section-a',
	array(
		'id'      => 'notes',
		'title'   => __( 'All notes', 'portfolio-theme' ),
		'content' => array(
			array(
				'name' => 'list-a',
				'args' => array( 'items' => array_map( 'portfolio_theme_note_row', $wp_query->posts ) ),
			),
		),
	)
);

portfolio_theme_component( 'pagination-a', portfolio_theme_posts_pagination() );

portfolio_theme_contact_section();

get_footer();
