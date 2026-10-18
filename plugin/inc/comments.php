<?php
/**
 * Comments are off site-wide.
 *
 * A portfolio gains little from comments and pays for them in spam,
 * moderation, personal data to look after and code on every page. This turns
 * them off everywhere: front end, admin, REST API and pings.
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Removes comment and trackback support from every post type.
 */
function portfolio_core_remove_comment_support() {
	foreach ( get_post_types() as $post_type ) {
		if ( post_type_supports( $post_type, 'comments' ) ) {
			remove_post_type_support( $post_type, 'comments' );
			remove_post_type_support( $post_type, 'trackbacks' );
		}
	}
}
add_action( 'init', 'portfolio_core_remove_comment_support', 100 );

// Closed everywhere, including posts created before this plugin.
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );

// Existing comments are never listed.
add_filter( 'comments_array', '__return_empty_array', 20 );

/**
 * Removes the comment endpoints from the REST API, so comments cannot be
 * posted or listed there either.
 *
 * @param array $endpoints REST endpoints.
 * @return array
 */
function portfolio_core_remove_comment_endpoints( $endpoints ) {
	foreach ( array_keys( $endpoints ) as $route ) {
		if ( str_starts_with( $route, '/wp/v2/comments' ) ) {
			unset( $endpoints[ $route ] );
		}
	}

	return $endpoints;
}
add_filter( 'rest_endpoints', 'portfolio_core_remove_comment_endpoints' );

/**
 * Removes the Comments screens from the admin and sends direct visits to the
 * dashboard.
 */
function portfolio_core_remove_comment_admin() {
	remove_menu_page( 'edit-comments.php' );
	remove_submenu_page( 'options-general.php', 'options-discussion.php' );
}
add_action( 'admin_menu', 'portfolio_core_remove_comment_admin' );

/**
 * Redirects the Comments and Discussion screens to the dashboard.
 */
function portfolio_core_redirect_comment_screens() {
	global $pagenow;

	if ( in_array( $pagenow, array( 'edit-comments.php', 'options-discussion.php', 'comment.php' ), true ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
}
add_action( 'admin_init', 'portfolio_core_redirect_comment_screens' );

/**
 * Every comment query returns nothing, including WordPress's sample comment
 * on a new install. The dashboard then hides its comment count (At a Glance)
 * and its recent comments (Activity) on its own.
 *
 * @param array|int|null   $comments Short-circuit value; null runs the query.
 * @param WP_Comment_Query $query    Comment query.
 * @return array|int
 */
function portfolio_core_empty_comment_queries( $comments, $query ) {
	return empty( $query->query_vars['count'] ) ? array() : 0;
}
add_filter( 'comments_pre_query', 'portfolio_core_empty_comment_queries', 10, 2 );

/**
 * Comment counts are always zero.
 *
 * @return stdClass
 */
function portfolio_core_zero_comment_count() {
	return (object) array(
		'approved'       => 0,
		'moderated'      => 0,
		'spam'           => 0,
		'trash'          => 0,
		'post-trashed'   => 0,
		'total_comments' => 0,
		'all'            => 0,
	);
}
add_filter( 'wp_count_comments', 'portfolio_core_zero_comment_count' );

/**
 * Removes the comments bubble from the admin bar.
 *
 * @param WP_Admin_Bar $admin_bar Admin bar.
 */
function portfolio_core_remove_comment_admin_bar( $admin_bar ) {
	$admin_bar->remove_node( 'comments' );
}
add_action( 'admin_bar_menu', 'portfolio_core_remove_comment_admin_bar', 100 );

// No comment feeds: drop their links from <head>.
add_filter( 'feed_links_show_comments_feed', '__return_false' );
