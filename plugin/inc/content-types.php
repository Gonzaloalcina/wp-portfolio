<?php
/**
 * Content types: projects (Work) and services.
 *
 * The hubs (/work/, /services/) are pages with a theme template, not post
 * type archives: Polylang free translates page slugs (/es/trabajo/) but not
 * the base of a post type's URL. Single URLs keep the English base for now;
 * translating it is part of the i18n phase (docs/decisions/08-page-templates.md).
 *
 * Both types use the classic editor (show_in_rest off), like pages: their
 * structure lives in SCF fields (inc/fields.php).
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the project and service post types.
 */
function portfolio_core_register_content_types() {
	register_post_type(
		'project',
		array(
			'labels'        => array(
				'name'               => __( 'Projects', 'portfolio-core' ),
				'singular_name'      => __( 'Project', 'portfolio-core' ),
				'add_new_item'       => __( 'Add project', 'portfolio-core' ),
				'edit_item'          => __( 'Edit project', 'portfolio-core' ),
				'all_items'          => __( 'All projects', 'portfolio-core' ),
				'search_items'       => __( 'Search projects', 'portfolio-core' ),
				'not_found'          => __( 'No projects found.', 'portfolio-core' ),
				'not_found_in_trash' => __( 'No projects found in Trash.', 'portfolio-core' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'show_in_rest'  => false,
			'menu_position' => 20,
			'menu_icon'     => 'dashicons-portfolio',
			'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'revisions' ),
			'rewrite'       => array(
				'slug'       => 'work',
				'with_front' => false,
			),
		)
	);

	register_post_type(
		'service',
		array(
			'labels'        => array(
				'name'               => __( 'Services', 'portfolio-core' ),
				'singular_name'      => __( 'Service', 'portfolio-core' ),
				'add_new_item'       => __( 'Add service', 'portfolio-core' ),
				'edit_item'          => __( 'Edit service', 'portfolio-core' ),
				'all_items'          => __( 'All services', 'portfolio-core' ),
				'search_items'       => __( 'Search services', 'portfolio-core' ),
				'not_found'          => __( 'No services found.', 'portfolio-core' ),
				'not_found_in_trash' => __( 'No services found in Trash.', 'portfolio-core' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'show_in_rest'  => false,
			'menu_position' => 21,
			'menu_icon'     => 'dashicons-hammer',
			'supports'      => array( 'title', 'editor', 'page-attributes', 'revisions' ),
			// No public query var: ?service=<slug> preselects the service in the
			// request form instead of loading the service (rewrites use
			// post_type=service&name= instead).
			'query_var'     => false,
			'rewrite'       => array(
				'slug'       => 'services',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'portfolio_core_register_content_types' );

/**
 * Whether a project may be shown: the client agreed to be named, or naming
 * them needs no consent (Gonzalo's employer, this site).
 *
 * @param int|WP_Post $post Project.
 * @return bool
 */
function portfolio_core_project_is_public( $post ) {
	return in_array( get_post_meta( get_post( $post )->ID, 'project_client_ok', true ), array( 'yes', 'not_needed' ), true );
}

/**
 * Whether a project is a case study: the long template, the Case studies
 * list of the Work hub.
 *
 * @param int|WP_Post $post Project.
 * @return bool
 */
function portfolio_core_project_is_featured( $post ) {
	return (bool) get_post_meta( get_post( $post )->ID, 'project_featured', true );
}

/**
 * A project's page on this site other than its own, from the Link field
 * (e.g. /how-this-site-is-built/ for this site's entry), or ''.
 *
 * @param int|WP_Post $post Project.
 * @return string
 */
function portfolio_core_project_internal_url( $post ) {
	$url = trim( (string) get_post_meta( get_post( $post )->ID, 'project_url', true ) );

	return str_starts_with( $url, '/' ) && ! str_starts_with( $url, '//' ) ? home_url( $url ) : '';
}

/**
 * Every project whose client may be named has a page. A project without
 * consent is a 404; a project whose Link is another page on this site
 * redirects there, so the same work has one URL.
 */
function portfolio_core_project_single_guard() {
	if ( ! is_singular( 'project' ) ) {
		return;
	}

	$post = get_queried_object();
	if ( ! portfolio_core_project_is_public( $post ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		return;
	}

	$internal = portfolio_core_project_internal_url( $post );
	if ( $internal ) {
		wp_safe_redirect( $internal, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'portfolio_core_project_single_guard' );
