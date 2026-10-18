<?php
/**
 * Site settings: one SCF options page for data the whole site uses (identity
 * and profile links), plus the Person schema built from it.
 *
 * Copy that changes with the language has an English and a Spanish field
 * (`_en`, `_es`): Polylang (free) does not translate options pages.
 *
 * Kept discreet on purpose: no availability or "hire me" data.
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

define( 'PORTFOLIO_CORE_SETTINGS_SLUG', 'portfolio-site-settings' );

/**
 * Registers the options page and its fields.
 */
function portfolio_core_register_settings() {
	if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __( 'Site settings', 'portfolio-core' ),
			'menu_title' => __( 'Site settings', 'portfolio-core' ),
			'menu_slug'  => PORTFOLIO_CORE_SETTINGS_SLUG,
			'capability' => 'manage_options',
			'icon_url'   => 'dashicons-id',
			'position'   => 61,
			'redirect'   => false,
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_portfolio_core_settings',
			'title'    => __( 'Site settings', 'portfolio-core' ),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => PORTFOLIO_CORE_SETTINGS_SLUG,
					),
				),
			),
			'fields'   => array(
				array(
					'key'   => 'field_portfolio_core_settings_tab_identity',
					'label' => __( 'Identity', 'portfolio-core' ),
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_portfolio_core_settings_name',
					'label'        => __( 'Name', 'portfolio-core' ),
					'name'         => 'site_person_name',
					'type'         => 'text',
					'instructions' => __( 'Your full name, as it should appear in search results.', 'portfolio-core' ),
				),
				array(
					'key'          => 'field_portfolio_core_settings_role_en',
					'label'        => __( 'Role (English)', 'portfolio-core' ),
					'name'         => 'site_role_en',
					'type'         => 'text',
					'instructions' => __( 'e.g. Senior WordPress Developer.', 'portfolio-core' ),
					'wrapper'      => array( 'width' => '50' ),
				),
				array(
					'key'     => 'field_portfolio_core_settings_role_es',
					'label'   => __( 'Role (Spanish)', 'portfolio-core' ),
					'name'    => 'site_role_es',
					'type'    => 'text',
					'wrapper' => array( 'width' => '50' ),
				),
				array(
					'key'          => 'field_portfolio_core_settings_bio_en',
					'label'        => __( 'Short bio (English)', 'portfolio-core' ),
					'name'         => 'site_bio_en',
					'type'         => 'textarea',
					'rows'         => 3,
					'instructions' => __( 'One or two sentences about what you do. No availability or job-seeking wording.', 'portfolio-core' ),
					'wrapper'      => array( 'width' => '50' ),
				),
				array(
					'key'     => 'field_portfolio_core_settings_bio_es',
					'label'   => __( 'Short bio (Spanish)', 'portfolio-core' ),
					'name'    => 'site_bio_es',
					'type'    => 'textarea',
					'rows'    => 3,
					'wrapper' => array( 'width' => '50' ),
				),
				array(
					'key'          => 'field_portfolio_core_settings_location',
					'label'        => __( 'Location', 'portfolio-core' ),
					'name'         => 'site_location',
					'type'         => 'text',
					'instructions' => __( 'e.g. Spain · CET.', 'portfolio-core' ),
				),
				array(
					'key'   => 'field_portfolio_core_settings_tab_profiles',
					'label' => __( 'Profiles', 'portfolio-core' ),
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_portfolio_core_settings_profile_links',
					'label'        => __( 'Profile links', 'portfolio-core' ),
					'name'         => 'site_profile_links',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => __( 'Add link', 'portfolio-core' ),
					'instructions' => __( 'Shown wherever the site lists your profiles (Home, Contact, page endings). Profiles marked rel="me" also feed the Person schema.', 'portfolio-core' ),
					'sub_fields'   => array(
						array(
							'key'      => 'field_portfolio_core_settings_profile_links_title',
							'label'    => __( 'Label', 'portfolio-core' ),
							'name'     => 'title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'      => 'field_portfolio_core_settings_profile_links_url',
							'label'    => __( 'Link', 'portfolio-core' ),
							'name'     => 'url',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'          => 'field_portfolio_core_settings_profile_links_me',
							'label'        => __( 'Profile (rel="me")', 'portfolio-core' ),
							'name'         => 'is_profile',
							'type'         => 'true_false',
							'instructions' => __( 'Tick for GitHub, LinkedIn and other profiles of yours.', 'portfolio-core' ),
							'ui'           => 1,
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/init', 'portfolio_core_register_settings' );

/**
 * Returns a site setting as a trimmed string, or '' when SCF is off or the
 * field is empty.
 *
 * @param string $name Field name.
 * @return string
 */
function portfolio_core_setting( $name ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	$value = get_field( $name, 'option' );

	return is_string( $value ) ? trim( $value ) : '';
}

/**
 * Returns a translatable site setting in the current language: the `_es`
 * field on Spanish pages, `_en` otherwise. Falls back to English.
 *
 * @param string $name Field name without the language suffix, e.g. "site_role".
 * @return string
 */
function portfolio_core_setting_i18n( $name ) {
	$language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';

	if ( 'es' === $language ) {
		$value = portfolio_core_setting( "{$name}_es" );
		if ( '' !== $value ) {
			return $value;
		}
	}

	return portfolio_core_setting( "{$name}_en" );
}

/**
 * Returns the profile links. Rows without a label are dropped.
 *
 * @return array<int, array{title: string, url: string, is_profile: bool}>
 */
function portfolio_core_profile_links() {
	$rows = function_exists( 'get_field' ) ? get_field( 'site_profile_links', 'option' ) : null;
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$links = array();
	foreach ( $rows as $row ) {
		$title = trim( (string) ( $row['title'] ?? '' ) );
		if ( '' === $title ) {
			continue;
		}

		$links[] = array(
			'title'      => $title,
			'url'        => trim( (string) ( $row['url'] ?? '' ) ),
			'is_profile' => ! empty( $row['is_profile'] ),
		);
	}

	return $links;
}

/**
 * Completes the Person that Rank Math outputs (Titles & Meta › Local SEO set
 * to "Person") with the site settings: name, job title, description and the
 * rel="me" profiles as sameAs. Nothing about availability.
 *
 * @param array $data JSON-LD entities, keyed by Rank Math.
 * @return array
 */
function portfolio_core_person_schema( $data ) {
	$same_as = array();
	foreach ( portfolio_core_profile_links() as $link ) {
		if ( $link['is_profile'] && wp_http_validate_url( $link['url'] ) ) {
			$same_as[] = esc_url_raw( $link['url'] );
		}
	}

	$fields = array_filter(
		array(
			'name'        => portfolio_core_setting( 'site_person_name' ),
			'jobTitle'    => portfolio_core_setting_i18n( 'site_role' ),
			'description' => portfolio_core_setting_i18n( 'site_bio' ),
			'sameAs'      => $same_as,
		)
	);

	// Only the site's Person (id ending in #person), not post authors.
	foreach ( $data as $key => $entity ) {
		if ( ! is_array( $entity ) ) {
			continue;
		}

		$types = (array) ( $entity['@type'] ?? array() );
		$id    = (string) ( $entity['@id'] ?? '' );
		if ( in_array( 'Person', $types, true ) && str_ends_with( $id, '#person' ) ) {
			$data[ $key ] = array_merge( $entity, $fields );
		}
	}

	return $data;
}
add_filter( 'rank_math/json_ld', 'portfolio_core_person_schema', 99 );
