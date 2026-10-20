<?php
/**
 * Blog blocks: Related, Code with file name and Aside.
 *
 * Sources in blocks/src/, built with @wordpress/scripts into blocks/build/
 * (committed, so Playground and a zip work without building; CI checks it is
 * up to date). They live in the plugin so posts keep their blocks if the
 * theme changes.
 *
 * Each block's CSS is its block.json "style": core prints it only on pages
 * that render the block, so it never loads outside single posts.
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers every block in blocks/build/ from the manifest the build writes,
 * which saves reading each block.json on every request.
 */
function portfolio_core_register_blocks() {
	$build = dirname( __DIR__ ) . '/blocks/build';

	wp_register_block_types_from_metadata_collection( $build, $build . '/blocks-manifest.php' );
}
add_action( 'init', 'portfolio_core_register_blocks' );

/**
 * Reads a field through SCF when it is active, post meta otherwise.
 *
 * @param string $name    Field name.
 * @param int    $post_id Post ID.
 * @return string
 */
function portfolio_core_field( $name, $post_id ) {
	$value = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : get_post_meta( $post_id, $name, true );

	return is_scalar( $value ) ? (string) $value : '';
}

/**
 * Where a project links: its page on this site (another page when its Link
 * field is a path on this site).
 *
 * @param WP_Post $post Project.
 * @return string
 */
function portfolio_core_project_url( $post ) {
	$internal = portfolio_core_project_internal_url( $post );

	return $internal ? $internal : get_permalink( $post );
}

/**
 * Whether a post may be linked from a note: a published service, or a
 * published project whose client may be named.
 *
 * @param WP_Post|null $post      Post.
 * @param string       $post_type Expected post type.
 * @return bool
 */
function portfolio_core_is_linkable( $post, $post_type ) {
	if ( ! $post || $post_type !== $post->post_type || 'publish' !== $post->post_status ) {
		return false;
	}

	return 'project' !== $post_type || portfolio_core_project_is_public( $post );
}

/**
 * The Related block's rows, in the shape of the theme's row-a: title, URL,
 * the one-line text and what the row is (meta). Anything no longer public
 * is skipped.
 *
 * @param array $attributes Block attributes (serviceId, projectId).
 * @return array<int, array{title: string, url: string, text: string, meta: string}>
 */
function portfolio_core_related_items( $attributes ) {
	$items = array();

	$service = get_post( absint( $attributes['serviceId'] ?? 0 ) );
	if ( portfolio_core_is_linkable( $service, 'service' ) ) {
		$items[] = array(
			'title' => get_the_title( $service ),
			'url'   => get_permalink( $service ),
			'text'  => portfolio_core_field( 'service_problem', $service->ID ),
			'meta'  => __( 'Service', 'portfolio-core' ),
		);
	}

	$project = get_post( absint( $attributes['projectId'] ?? 0 ) );
	if ( portfolio_core_is_linkable( $project, 'project' ) ) {
		$items[] = array(
			'title' => get_the_title( $project ),
			'url'   => portfolio_core_project_url( $project ),
			'text'  => portfolio_core_field( 'project_what', $project->ID ),
			'meta'  => __( 'Project', 'portfolio-core' ),
		);
	}

	return $items;
}

/**
 * REST route for the Related block's editor: the services and projects a
 * note may link to. The post types are not in the REST API (they use the
 * classic editor), so this route exposes only IDs and titles, to editors.
 */
function portfolio_core_register_related_route() {
	register_rest_route(
		'portfolio-core/v1',
		'/related-options',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'portfolio_core_related_options',
			'permission_callback' => static fn() => current_user_can( 'edit_posts' ),
		)
	);
}
add_action( 'rest_api_init', 'portfolio_core_register_related_route' );

/**
 * Lists linkable services and projects, in the order the site shows them.
 *
 * @return WP_REST_Response
 */
function portfolio_core_related_options() {
	$query = array(
		'post_status'    => 'publish',
		'posts_per_page' => 100,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	);

	$services = get_posts( array( 'post_type' => 'service' ) + $query );
	$projects = array_filter(
		get_posts( array( 'post_type' => 'project' ) + $query ),
		'portfolio_core_project_is_public'
	);

	$options = static fn( $posts ) => array_values(
		array_map(
			static fn( $post ) => array(
				'id'    => $post->ID,
				'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			),
			$posts
		)
	);

	return rest_ensure_response(
		array(
			'services' => $options( $services ),
			'projects' => $options( $projects ),
		)
	);
}
