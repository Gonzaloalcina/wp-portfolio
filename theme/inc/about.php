<?php
/**
 * About page: SCF fields and the data the About template reads.
 *
 * Identity (name, short bio, location, languages) comes from the site
 * settings in portfolio-core, experience from the Resume page and
 * testimonials from the Home, so each fact is entered once. The About
 * fields hold only what no other page has. Without SCF or the plugin the
 * helpers return nothing and their sections hide.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the About field group, shown on pages with the About template.
 */
function portfolio_theme_register_about_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_portfolio_about',
			'title'    => __( 'About', 'portfolio-theme' ),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'templates/about.php',
					),
				),
			),
			'fields'   => array(
				array(
					'key'          => 'field_portfolio_about_currently',
					'label'        => __( 'Currently', 'portfolio-theme' ),
					'name'         => 'about_currently',
					'type'         => 'text',
					'instructions' => __( 'Role and company, e.g. Website Lead, Company. No availability wording.', 'portfolio-theme' ),
				),
				array(
					'key'          => 'field_portfolio_about_how',
					'label'        => __( 'How I work', 'portfolio-theme' ),
					'name'         => 'about_how',
					'type'         => 'repeater',
					'layout'       => 'table',
					'instructions' => __( 'One row per area of the site: what you do there and with whom.', 'portfolio-theme' ),
					'button_label' => __( 'Add row', 'portfolio-theme' ),
					'sub_fields'   => array(
						array(
							'key'      => 'field_portfolio_about_how_area',
							'label'    => __( 'Area', 'portfolio-theme' ),
							'name'     => 'area',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'       => 'field_portfolio_about_how_text',
							'label'     => __( 'What I do', 'portfolio-theme' ),
							'name'      => 'text',
							'type'      => 'text',
							'maxlength' => 140,
						),
						array(
							'key'          => 'field_portfolio_about_how_who',
							'label'        => __( 'With whom', 'portfolio-theme' ),
							'name'         => 'who',
							'type'         => 'text',
							'instructions' => __( 'Teams or roles, e.g. Growth, Sales.', 'portfolio-theme' ),
						),
					),
				),
				array(
					'key'          => 'field_portfolio_about_tools',
					'label'        => __( 'Tools and AI', 'portfolio-theme' ),
					'name'         => 'about_tools',
					'type'         => 'textarea',
					'rows'         => 5,
					'new_lines'    => '',
					'instructions' => __( 'A blank line starts a new paragraph.', 'portfolio-theme' ),
				),
				array(
					'key'       => 'field_portfolio_about_outside',
					'label'     => __( 'Other things', 'portfolio-theme' ),
					'name'      => 'about_outside',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
			),
		)
	);
}
add_action( 'acf/init', 'portfolio_theme_register_about_fields' );

/**
 * Returns a site setting from portfolio-core in the current language when
 * it has one, or ''. Without the plugin, ''.
 *
 * @param string $name Field name; translatable ones without the suffix.
 * @param bool   $i18n Whether the field has `_en` and `_es` versions.
 * @return string
 */
function portfolio_theme_setting( $name, $i18n = false ) {
	if ( $i18n ) {
		return function_exists( 'portfolio_core_setting_i18n' ) ? portfolio_core_setting_i18n( $name ) : '';
	}

	return function_exists( 'portfolio_core_setting' ) ? portfolio_core_setting( $name ) : '';
}

/**
 * Returns the About facts for facts-a: location, languages and the current
 * role. Empty values are dropped by facts-a.
 *
 * @param int $post_id About page ID; 0 for the current page.
 * @return array<int, array{label: string, value: string}>
 */
function portfolio_theme_about_facts( $post_id = 0 ) {
	return array(
		array(
			'label' => __( 'Based in', 'portfolio-theme' ),
			'value' => function_exists( 'portfolio_core_location' ) ? portfolio_core_location() : '',
		),
		array(
			'label' => __( 'Languages', 'portfolio-theme' ),
			'value' => portfolio_theme_setting( 'site_languages', true ),
		),
		array(
			'label' => __( 'Currently', 'portfolio-theme' ),
			'value' => portfolio_theme_field( 'about_currently', $post_id ),
		),
	);
}

/**
 * Returns the "How I work" rows as row-a items: the area as title, what I
 * do as the line, with whom as meta. Rows without an area are dropped.
 *
 * @param int $post_id About page ID; 0 for the current page.
 * @return array<int, array{title: string, text: string, meta: string}>
 */
function portfolio_theme_about_how_rows( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$rows    = function_exists( 'get_field' ) ? get_field( 'about_how', $post_id ) : null;
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$items = array();
	foreach ( $rows as $row ) {
		$area = trim( (string) ( $row['area'] ?? '' ) );
		if ( '' === $area ) {
			continue;
		}

		$items[] = array(
			'title' => $area,
			'text'  => trim( (string) ( $row['text'] ?? '' ) ),
			'meta'  => trim( (string) ( $row['who'] ?? '' ) ),
		);
	}

	return $items;
}

/**
 * Returns the Resume page in the current language: the published page with
 * the Resume template. Polylang filters the query by language.
 *
 * @return int Page ID, or 0.
 */
function portfolio_theme_resume_page_id() {
	$pages = get_posts(
		array(
			'post_type'        => 'page',
			'post_status'      => 'publish',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One indexed key, few pages.
			'meta_value'       => 'templates/resume.php', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- See above.
			'suppress_filters' => false,
		)
	);

	return $pages ? (int) $pages[0] : 0;
}

/**
 * Returns the URL of the Resume page in the current language, or the
 * English path when there is none.
 *
 * @return string
 */
function portfolio_theme_resume_url() {
	$resume_id = portfolio_theme_resume_page_id();

	return $resume_id ? (string) get_permalink( $resume_id ) : home_url( '/resume/' );
}

/**
 * Returns the "Where I have done it" rows: the first section of the Resume
 * page (its experience), so the experience is entered once. Each row shows
 * its one-line summary when it has one, the Resume's longer text otherwise.
 *
 * @return array<int, array{title: string, meta: string, text: string}>
 */
function portfolio_theme_about_where_rows() {
	$resume_id = portfolio_theme_resume_page_id();
	if ( ! $resume_id ) {
		return array();
	}

	$sections = portfolio_theme_resume_sections( $resume_id );

	return $sections
		? array_map( static fn( $row ) => array( 'text' => $row['summary'] ? $row['summary'] : $row['text'] ) + $row, $sections[0]['items'] )
		: array();
}
