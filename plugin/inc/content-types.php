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
 * Whether a project has its own case page.
 *
 * @param int|WP_Post $post Project.
 * @return bool
 */
function portfolio_core_project_is_featured( $post ) {
	return (bool) get_post_meta( get_post( $post )->ID, 'project_featured', true );
}

/**
 * Only featured projects with consent have a page. Any other project URL is
 * a 404: those projects are rows in the Work hub, linked to the live site.
 */
function portfolio_core_project_single_404() {
	if ( ! is_singular( 'project' ) ) {
		return;
	}

	$post = get_queried_object();
	if ( portfolio_core_project_is_featured( $post ) && portfolio_core_project_is_public( $post ) ) {
		return;
	}

	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
}
add_action( 'template_redirect', 'portfolio_core_project_single_404' );
