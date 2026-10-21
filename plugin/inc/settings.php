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
					'wrapper'      => array( 'width' => '50' ),
				),
				array(
					'key'     => 'field_portfolio_core_settings_location_es',
					'label'   => __( 'Location (Spanish)', 'portfolio-core' ),
					'name'    => 'site_location_es',
					'type'    => 'text',
					'wrapper' => array( 'width' => '50' ),
				),
				array(
					'key'          => 'field_portfolio_core_settings_languages_en',
					'label'        => __( 'Languages (English)', 'portfolio-core' ),
					'name'         => 'site_languages_en',
					'type'         => 'text',
					'instructions' => __( 'Comma-separated, level in brackets, e.g. Spanish (native), English (professional). Shown on About and read by the Person schema.', 'portfolio-core' ),
					'wrapper'      => array( 'width' => '50' ),
				),
				array(
					'key'     => 'field_portfolio_core_settings_languages_es',
					'label'   => __( 'Languages (Spanish)', 'portfolio-core' ),
					'name'    => 'site_languages_es',
					'type'    => 'text',
					'wrapper' => array( 'width' => '50' ),
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
 * Returns the location in the current language: the Spanish field on
 * Spanish pages when it is filled, the main field otherwise.
 *
 * @return string
 */
function portfolio_core_location() {
	$language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
	$spanish  = 'es' === $language ? portfolio_core_setting( 'site_location_es' ) : '';

	return '' !== $spanish ? $spanish : portfolio_core_setting( 'site_location' );
}

/**
 * Returns the languages from the site settings, in the current language,
 * without their level: "Spanish (native), English (professional)" gives
 * Spanish and English.
 *
 * @return string[]
 */
function portfolio_core_languages() {
	$languages = array();
	foreach ( explode( ',', portfolio_core_setting_i18n( 'site_languages' ) ) as $language ) {
		$language = trim( preg_replace( '/\s*\(.*?\)/', '', $language ) );
		if ( '' !== $language ) {
			$languages[] = $language;
		}
	}

	return $languages;
}

/**
 * Completes the Person that Rank Math outputs (Titles & Meta › Local SEO set
 * to "Person") with the site settings: name, job title, description, the
 * languages as knowsLanguage and the rel="me" profiles as sameAs. Nothing
 * about availability.
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
			'name'          => portfolio_core_setting( 'site_person_name' ),
			'jobTitle'      => portfolio_core_setting_i18n( 'site_role' ),
			'description'   => portfolio_core_setting_i18n( 'site_bio' ),
			'knowsLanguage' => portfolio_core_languages(),
			'sameAs'        => $same_as,
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

/**
 * Marks the Notes page (the posts page) as a Blog as well as a
 * CollectionPage, as the sitemap says. Rank Math outputs only CollectionPage.
 *
 * @param array $data JSON-LD entities, keyed by Rank Math.
 * @return array
 */
function portfolio_core_blog_schema( $data ) {
	if ( ! is_home() || is_front_page() ) {
		return $data;
	}

	foreach ( $data as $key => $entity ) {
		if ( is_array( $entity ) && 'CollectionPage' === ( $entity['@type'] ?? '' ) ) {
			$data[ $key ]['@type'] = array( 'CollectionPage', 'Blog' );
		}
	}

	return $data;
}
add_filter( 'rank_math/json_ld', 'portfolio_core_blog_schema', 99 );

/**
 * Undoes what Rank Math's Polylang support adds to the Notes canonical.
 *
 * Its fix_home_canonical() runs on every page where is_home() is true, which
 * includes the posts page, and appends the path of Polylang's home URL. On a
 * site in a subfolder that turns /notes/ into /notes/subfolder/, and on the
 * Spanish page it would turn /es/notas/ into /es/notas/es/.
 *
 * @param string $canonical Canonical URL, after Rank Math's Polylang fix (10).
 * @return string
 */
function portfolio_core_fix_posts_page_canonical( $canonical ) {
	if ( ! is_home() || is_front_page() || ! function_exists( 'pll_home_url' ) || ! function_exists( 'pll_current_language' ) ) {
		return $canonical;
	}

	$path = ltrim( (string) wp_parse_url( pll_home_url( pll_current_language() ), PHP_URL_PATH ), '/' );
	if ( '' !== $path && str_ends_with( $canonical, $path ) ) {
		return substr( $canonical, 0, -strlen( $path ) );
	}

	return $canonical;
}
add_filter( 'rank_math/frontend/canonical', 'portfolio_core_fix_posts_page_canonical', 20 );
