<?php
/**
 * Breadcrumbs. Rank Math builds the trail and its BreadcrumbList schema; this
 * file shapes the trail and breadcrumb-a renders it, so what people see and
 * what search engines and agents read are the same list.
 *
 * Only below the first level: a case, a service and a note, each under its
 * hub (Work, Services, Notes) in the current language. Anywhere else, or
 * while the hub does not exist in that language, there is no trail and no
 * schema. Without Rank Math there are no breadcrumbs.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Turns on Rank Math's breadcrumbs from code, so every environment has them
 * without a setting in the database.
 */
function portfolio_theme_breadcrumbs_support() {
	add_theme_support( 'rank-math-breadcrumbs' );
}
add_action( 'after_setup_theme', 'portfolio_theme_breadcrumbs_support' );

/**
 * The hub page a post sits under: Notes (the posts page) for a note, the
 * page with the Work or Services template for a project or a service. Only
 * a hub in the current language counts.
 *
 * @param WP_Post $post Post.
 * @return int Page ID, or 0.
 */
function portfolio_theme_breadcrumb_hub( $post ) {
	$templates = array(
		'project' => 'templates/work.php',
		'service' => 'templates/services.php',
	);

	if ( 'post' === $post->post_type ) {
		$hub = (int) get_option( 'page_for_posts' );
	} elseif ( isset( $templates[ $post->post_type ] ) ) {
		$ids = get_posts(
			array(
				'post_type'        => 'page',
				'post_status'      => 'publish',
				'posts_per_page'   => 1,
				'fields'           => 'ids',
				'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One indexed lookup per page view.
				'meta_value'       => $templates[ $post->post_type ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- See above.
				'suppress_filters' => false, // Polylang: the current language only.
			)
		);
		$hub = $ids ? (int) $ids[0] : 0;
	} else {
		return 0;
	}

	if ( $hub && function_exists( 'pll_get_post_language' ) && pll_get_post_language( $hub ) !== pll_current_language() ) {
		return 0;
	}

	return $hub;
}

/**
 * Shapes Rank Math's trail: Home › hub › current page on a case, a service
 * or a note; nothing anywhere else.
 *
 * @param array $crumbs Rank Math crumbs: [ name, url, 'hide_in_schema' => bool ].
 * @return array
 */
function portfolio_theme_breadcrumb_items( $crumbs ) {
	if ( count( $crumbs ) < 2 || ! is_singular( array( 'post', 'project', 'service' ) ) ) {
		return array();
	}

	$hub = portfolio_theme_breadcrumb_hub( get_queried_object() );
	if ( ! $hub ) {
		return array();
	}

	$home    = reset( $crumbs );
	$home[0] = __( 'Home', 'portfolio-theme' ); // Rank Math has one label for every language.
	$home[1] = portfolio_theme_page_url( '/' ); // Rank Math links the Home without the slash of its canonical URL.

	return array(
		$home,
		array(
			get_the_title( $hub ),
			get_permalink( $hub ),
			'hide_in_schema' => false,
		),
		end( $crumbs ),
	);
}
add_filter( 'rank_math/frontend/breadcrumb/items', 'portfolio_theme_breadcrumb_items' );

/**
 * Returns the current trail as breadcrumb-a items.
 *
 * @return array<int, array{title: string, url: string}>
 */
function portfolio_theme_breadcrumbs() {
	if ( ! class_exists( '\RankMath\Frontend\Breadcrumbs' ) ) {
		return array();
	}

	$trail = \RankMath\Frontend\Breadcrumbs::get();
	if ( ! $trail ) {
		return array();
	}

	return array_map(
		static fn( $crumb ) => array(
			'title' => (string) $crumb[0],
			'url'   => (string) $crumb[1],
		),
		$trail->get_crumbs()
	);
}
