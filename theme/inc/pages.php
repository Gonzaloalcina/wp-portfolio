<?php
/**
 * Page templates: shared fields and the data helpers the templates read.
 *
 * Templates (templates/*.php, page.php, home.php, single-*.php, 404.php)
 * only choose and combine components; everything they show comes through
 * the helpers below, so an empty field renders nothing.
 *
 * Projects and services are post types of the portfolio-core plugin. Without
 * it the helpers return nothing and the sections that use them hide.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the page field groups: an intro for every page except the Home
 * (which has its own fields), and the Resume sections.
 */
function portfolio_theme_register_page_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_portfolio_page',
			'title'    => __( 'Page', 'portfolio-theme' ),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
					array(
						'param'    => 'page_type',
						'operator' => '!=',
						'value'    => 'front_page',
					),
				),
			),
			'position' => 'acf_after_title',
			'fields'   => array(
				array(
					'key'          => 'field_portfolio_page_intro',
					'label'        => __( 'Intro', 'portfolio-theme' ),
					'name'         => 'page_intro',
					'type'         => 'textarea',
					'rows'         => 3,
					'maxlength'    => 160,
					'instructions' => __( 'One or two sentences under the title.', 'portfolio-theme' ),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_portfolio_resume',
			'title'    => __( 'Resume', 'portfolio-theme' ),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/resume.php',
					),
				),
			),
			'fields'   => array(
				array(
					'key'          => 'field_portfolio_resume_sections',
					'label'        => __( 'Sections', 'portfolio-theme' ),
					'name'         => 'resume_sections',
					'type'         => 'repeater',
					'layout'       => 'block',
					'instructions' => __( 'e.g. Experience, Skills, Education, Languages.', 'portfolio-theme' ),
					'button_label' => __( 'Add section', 'portfolio-theme' ),
					'sub_fields'   => array(
						array(
							'key'      => 'field_portfolio_resume_section_title',
							'label'    => __( 'Title', 'portfolio-theme' ),
							'name'     => 'title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'          => 'field_portfolio_resume_section_rows',
							'label'        => __( 'Rows', 'portfolio-theme' ),
							'name'         => 'rows',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => __( 'Add row', 'portfolio-theme' ),
							'sub_fields'   => array(
								array(
									'key'      => 'field_portfolio_resume_row_title',
									'label'    => __( 'Title', 'portfolio-theme' ),
									'name'     => 'title',
									'type'     => 'text',
									'required' => 1,
								),
								array(
									'key'          => 'field_portfolio_resume_row_meta',
									'label'        => __( 'Meta', 'portfolio-theme' ),
									'name'         => 'meta',
									'type'         => 'text',
									'instructions' => __( 'Years, place.', 'portfolio-theme' ),
								),
								array(
									'key'       => 'field_portfolio_resume_row_text',
									'label'     => __( 'Line', 'portfolio-theme' ),
									'name'      => 'text',
									'type'      => 'text',
									'maxlength' => 140,
								),
							),
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'portfolio_theme_register_page_fields' );

/**
 * Returns a field as a trimmed string, or ''. Reads SCF when it is active,
 * the raw post meta otherwise (same storage for text fields).
 *
 * @param string $name    Field name.
 * @param int    $post_id Post ID; 0 for the current post.
 * @return string
 */
function portfolio_theme_field( $name, $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$value = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : get_post_meta( $post_id, $name, true );

	// True/false fields come back as booleans: true is '1', false is ''.
	if ( is_bool( $value ) ) {
		return $value ? '1' : '';
	}

	return is_string( $value ) || is_numeric( $value ) ? trim( (string) $value ) : '';
}

/**
 * Returns the intro of a page (the page_intro field).
 *
 * @param int $post_id Page ID; 0 for the current page.
 * @return string
 */
function portfolio_theme_page_intro( $post_id = 0 ) {
	return portfolio_theme_field( 'page_intro', $post_id );
}

/**
 * Returns a Home field as a trimmed string. Other pages reuse the Home's
 * contact label, text and profile links, so each lives in one place.
 *
 * @param string $name Field name.
 * @return string
 */
function portfolio_theme_front_text( $name ) {
	$front_id = (int) get_option( 'page_on_front' );

	return $front_id ? portfolio_theme_field( $name, $front_id ) : '';
}

/**
 * Returns the profile links (GitHub, LinkedIn…) from the site settings in
 * portfolio-core, as link-row-a links. Empty when the plugin is off.
 *
 * @return array<int, array{title: string, url: string, rel: string}>
 */
function portfolio_theme_profile_links() {
	if ( ! function_exists( 'portfolio_core_profile_links' ) ) {
		return array();
	}

	$links = array();
	foreach ( portfolio_core_profile_links() as $link ) {
		$links[] = array(
			'title' => $link['title'],
			'url'   => portfolio_theme_resolve_url( $link['url'] ),
			'rel'   => $link['is_profile'] ? 'me' : '',
		);
	}

	return $links;
}

/**
 * Returns the contact button: the Home's label, pointing at /contact/. No
 * label, no button.
 *
 * @return array{title: string, url: string}
 */
function portfolio_theme_contact_button() {
	return array(
		'title' => portfolio_theme_front_text( 'home_cta_label' ),
		'url'   => home_url( '/contact/' ),
	);
}

/**
 * Renders the closing Contact section pages end with: the Home's contact
 * text, the contact button and the profile links. It hides itself when the
 * Home has none of them.
 */
function portfolio_theme_contact_section() {
	portfolio_theme_component(
		'section-a',
		array(
			'id'      => 'contact',
			'title'   => __( 'Contact', 'portfolio-theme' ),
			'text'    => portfolio_theme_front_text( 'home_contact_text' ),
			'actions' => array(
				'button' => portfolio_theme_contact_button(),
				'links'  => portfolio_theme_profile_links(),
			),
		)
	);
}

/**
 * Returns published projects that may be shown (client OK to name: Yes or
 * Not needed), in menu order.
 *
 * @param bool|null $featured True: case studies only. False: the rest. Null: all.
 * @return WP_Post[]
 */
function portfolio_theme_projects( $featured = null ) {
	if ( ! post_type_exists( 'project' ) ) {
		return array();
	}

	$meta_query = array(
		array(
			'key'     => 'project_client_ok',
			'value'   => array( 'yes', 'not_needed' ),
			'compare' => 'IN',
		),
	);

	if ( null !== $featured ) {
		$meta_query[] = $featured
			? array(
				'key'   => 'project_featured',
				'value' => '1',
			)
			: array(
				'relation' => 'OR',
				array(
					'key'     => 'project_featured',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'project_featured',
					'value'   => '1',
					'compare' => '!=',
				),
			);
	}

	return get_posts(
		array(
			'post_type'        => 'project',
			'post_status'      => 'publish',
			'posts_per_page'   => 100,
			'orderby'          => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'meta_query'       => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- A few dozen projects at most.
			'suppress_filters' => false,
		)
	);
}

/**
 * Where a project links: its case page when featured, otherwise the link
 * field (the live site, or a page on this site).
 *
 * @param WP_Post $post Project.
 * @return string
 */
function portfolio_theme_project_url( $post ) {
	if ( portfolio_theme_field( 'project_featured', $post->ID ) ) {
		return get_permalink( $post );
	}

	return portfolio_theme_resolve_url( portfolio_theme_field( 'project_url', $post->ID ) );
}

/**
 * Returns projects as row-a items: name, year · type, what I did and the
 * role (agency work says through whom, and who did the design).
 *
 * @param WP_Post[] $posts Projects.
 * @return array<int, array{title: string, url: string, meta: string, text: string}>
 */
function portfolio_theme_project_rows( $posts ) {
	return array_map(
		static fn( $post ) => array(
			'title' => get_the_title( $post ),
			'url'   => portfolio_theme_project_url( $post ),
			'meta'  => implode(
				' · ',
				array_filter(
					array(
						portfolio_theme_field( 'project_year', $post->ID ),
						portfolio_theme_field( 'project_type', $post->ID ),
					)
				)
			),
			'text'  => portfolio_theme_project_line( $post ),
		),
		$posts
	);
}

/**
 * A project's one line for lists: what I did, then the role as a sentence.
 *
 * @param WP_Post $post Project.
 * @return string
 */
function portfolio_theme_project_line( $post ) {
	$role = portfolio_theme_field( 'project_role', $post->ID );
	if ( '' !== $role && ! preg_match( '/[.!?]$/', $role ) ) {
		$role .= '.';
	}

	return trim( portfolio_theme_field( 'project_what', $post->ID ) . ' ' . $role );
}

/**
 * Returns a project's facts for facts-a: client, year, type, role, built
 * with and the live site. Empty facts are dropped by the component.
 *
 * @param WP_Post $post Project.
 * @return array<int, array{label: string, value: string, url?: string}>
 */
function portfolio_theme_project_facts( $post ) {
	$url = portfolio_theme_resolve_url( portfolio_theme_field( 'project_url', $post->ID ) );

	return array(
		array(
			'label' => __( 'Client', 'portfolio-theme' ),
			'value' => portfolio_theme_field( 'project_client', $post->ID ),
		),
		array(
			'label' => __( 'Year', 'portfolio-theme' ),
			'value' => portfolio_theme_field( 'project_year', $post->ID ),
		),
		array(
			'label' => __( 'Type', 'portfolio-theme' ),
			'value' => portfolio_theme_field( 'project_type', $post->ID ),
		),
		array(
			'label' => __( 'Role', 'portfolio-theme' ),
			'value' => portfolio_theme_field( 'project_role', $post->ID ),
		),
		array(
			'label' => __( 'Built with', 'portfolio-theme' ),
			'value' => portfolio_theme_field( 'project_built_with', $post->ID ),
		),
		array(
			'label' => __( 'Site', 'portfolio-theme' ),
			'value' => $url ? (string) wp_parse_url( $url, PHP_URL_HOST ) : '',
			'url'   => $url,
		),
	);
}

/**
 * Returns published services as row-a items: name and the problem it solves.
 *
 * @param bool $home True: only services shown on the Home (the service's
 *                   "Show on the Home" field; on unless switched off).
 * @return array<int, array{title: string, url: string, text: string}>
 */
function portfolio_theme_service_rows( $home = false ) {
	if ( ! post_type_exists( 'service' ) ) {
		return array();
	}

	$posts = get_posts(
		array(
			'post_type'        => 'service',
			'post_status'      => 'publish',
			'posts_per_page'   => 20,
			'orderby'          => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'suppress_filters' => false,
		)
	);

	if ( $home ) {
		// Raw meta: a service saved before the field existed has none and stays on.
		$posts = array_values(
			array_filter(
				$posts,
				static fn( $post ) => '0' !== get_post_meta( $post->ID, 'service_home', true )
			)
		);
	}

	return array_map(
		static fn( $post ) => array(
			'title' => get_the_title( $post ),
			'url'   => get_permalink( $post ),
			'text'  => portfolio_theme_field( 'service_problem', $post->ID ),
		),
		$posts
	);
}

/**
 * Returns a service's "What it includes" repeater as row-a items.
 *
 * @param int $post_id Service ID.
 * @return array<int, array{title: string, text: string}>
 */
function portfolio_theme_service_includes( $post_id ) {
	$rows = function_exists( 'get_field' ) ? get_field( 'service_includes', $post_id ) : null;
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$items = array();
	foreach ( $rows as $row ) {
		$title = trim( (string) ( $row['title'] ?? '' ) );
		if ( '' !== $title ) {
			$items[] = array(
				'title' => $title,
				'text'  => trim( (string) ( $row['text'] ?? '' ) ),
			);
		}
	}

	return $items;
}

/**
 * Returns a service's proof (projects and notes) as row-a items. Projects
 * that may not be shown, and anything unpublished, are dropped.
 *
 * @param int $post_id Service ID.
 * @return array<int, array{title: string, url: string, meta: string, text: string}>
 */
function portfolio_theme_service_proof( $post_id ) {
	$ids = function_exists( 'get_field' ) ? get_field( 'service_proof', $post_id ) : null;
	if ( ! is_array( $ids ) ) {
		return array();
	}

	$items = array();
	foreach ( array_map( 'get_post', $ids ) as $post ) {
		if ( ! $post || 'publish' !== $post->post_status ) {
			continue;
		}

		if ( 'project' === $post->post_type ) {
			if ( in_array( portfolio_theme_field( 'project_client_ok', $post->ID ), array( 'yes', 'not_needed' ), true ) ) {
				$items = array_merge( $items, portfolio_theme_project_rows( array( $post ) ) );
			}
			continue;
		}

		$items[] = portfolio_theme_note_row( $post );
	}

	return $items;
}

/**
 * Returns a post as a row-a item: title, date and a one-line excerpt.
 *
 * @param WP_Post $post Post.
 * @return array{title: string, url: string, meta: string, datetime: string, text: string}
 */
function portfolio_theme_note_row( $post ) {
	return array(
		'title'    => get_the_title( $post ),
		'url'      => get_permalink( $post ),
		'meta'     => get_the_date( '', $post ),
		'datetime' => get_the_date( 'Y-m-d', $post ),
		'text'     => wp_trim_words( get_the_excerpt( $post ), 20 ),
	);
}

/**
 * Returns the Resume sections, each with its rows as row-a items. Sections
 * without a title or rows are dropped.
 *
 * @param int $post_id Resume page ID.
 * @return array<int, array{title: string, items: array}>
 */
function portfolio_theme_resume_sections( $post_id ) {
	$sections = function_exists( 'get_field' ) ? get_field( 'resume_sections', $post_id ) : null;
	if ( ! is_array( $sections ) ) {
		return array();
	}

	$result = array();
	foreach ( $sections as $section ) {
		$title = trim( (string) ( $section['title'] ?? '' ) );
		$items = array();

		foreach ( (array) ( $section['rows'] ?? array() ) as $row ) {
			$row_title = trim( (string) ( $row['title'] ?? '' ) );
			if ( '' !== $row_title ) {
				$items[] = array(
					'title' => $row_title,
					'meta'  => trim( (string) ( $row['meta'] ?? '' ) ),
					'text'  => trim( (string) ( $row['text'] ?? '' ) ),
				);
			}
		}

		if ( '' !== $title && $items ) {
			$result[] = array(
				'title' => $title,
				'items' => $items,
			);
		}
	}

	return $result;
}

/**
 * Returns the posts-page pagination as pagination-a args.
 *
 * @return array{prev: array, next: array, current: int, total: int}
 */
function portfolio_theme_posts_pagination() {
	global $wp_query;

	$total   = (int) $wp_query->max_num_pages;
	$current = max( 1, (int) get_query_var( 'paged' ) );

	return array(
		'prev'    => $current > 1
			? array(
				'title' => __( 'Newer notes', 'portfolio-theme' ),
				'url'   => get_pagenum_link( $current - 1 ),
			)
			: array(),
		'next'    => $current < $total
			? array(
				'title' => __( 'Older notes', 'portfolio-theme' ),
				'url'   => get_pagenum_link( $current + 1 ),
			)
			: array(),
		'current' => $current,
		'total'   => $total,
	);
}

/**
 * Returns the current post's content as the_content() would print it, for
 * prose-a.
 *
 * @return string
 */
function portfolio_theme_content() {
	return apply_filters( 'the_content', get_the_content() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Core hook.
}
