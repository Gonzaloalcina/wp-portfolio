<?php
/**
 * Home page: SCF fields and the data the front-page template reads.
 *
 * Selected work and Also built come from the project post type
 * (portfolio-core), placed by each project's "Home" field; Services from the
 * service post type. The template only depends on the helpers below.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Home field group, shown on the page set as the front page.
 */
function portfolio_theme_register_home_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_portfolio_home',
			'title'    => __( 'Home', 'portfolio-theme' ),
			'location' => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
			),
			'fields'   => array(
				array(
					'key'   => 'field_portfolio_home_tab_hero',
					'label' => __( 'Hero', 'portfolio-theme' ),
					'type'  => 'tab',
				),
				array(
					'key'       => 'field_portfolio_home_headline',
					'label'     => __( 'Headline (H1)', 'portfolio-theme' ),
					'name'      => 'home_headline',
					'type'      => 'text',
					'maxlength' => 70,
				),
				array(
					'key'       => 'field_portfolio_home_intro',
					'label'     => __( 'Short bio', 'portfolio-theme' ),
					'name'      => 'home_intro',
					'type'      => 'textarea',
					'rows'      => 3,
					'maxlength' => 160,
				),
				array(
					'key'          => 'field_portfolio_home_cta_label',
					'label'        => __( 'Contact button label', 'portfolio-theme' ),
					'name'         => 'home_cta_label',
					'type'         => 'text',
					'instructions' => __( 'Verb + object. Used in the hero and in Contact.', 'portfolio-theme' ),
				),
				array(
					'key'   => 'field_portfolio_home_tab_testimonials',
					'label' => __( 'Testimonials', 'portfolio-theme' ),
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_portfolio_home_testimonials',
					'label'        => __( 'Testimonials', 'portfolio-theme' ),
					'name'         => 'home_testimonials',
					'type'         => 'repeater',
					'layout'       => 'block',
					'max'          => 3,
					'instructions' => __( 'Real words, with the person\'s permission. Link to their LinkedIn recommendation or profile as proof.', 'portfolio-theme' ),
					'button_label' => __( 'Add testimonial', 'portfolio-theme' ),
					'sub_fields'   => array(
						array(
							'key'          => 'field_portfolio_home_testimonials_quote',
							'label'        => __( 'Quote', 'portfolio-theme' ),
							'name'         => 'quote',
							'type'         => 'textarea',
							'rows'         => 3,
							'maxlength'    => 600,
							'instructions' => __( 'As the person wrote it. A blank line starts a new paragraph.', 'portfolio-theme' ),
							'required'     => 1,
						),
						array(
							'key'      => 'field_portfolio_home_testimonials_name',
							'label'    => __( 'Name', 'portfolio-theme' ),
							'name'     => 'name',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'          => 'field_portfolio_home_testimonials_role',
							'label'        => __( 'Role', 'portfolio-theme' ),
							'name'         => 'role',
							'type'         => 'text',
							'instructions' => __( 'Role and company, e.g. Head of Marketing, Company.', 'portfolio-theme' ),
						),
						array(
							'key'   => 'field_portfolio_home_testimonials_url',
							'label' => __( 'Proof link', 'portfolio-theme' ),
							'name'  => 'url',
							'type'  => 'url',
						),
					),
				),
				array(
					'key'   => 'field_portfolio_home_tab_contact',
					'label' => __( 'Contact', 'portfolio-theme' ),
					'type'  => 'tab',
				),
				array(
					'key'   => 'field_portfolio_home_contact_text',
					'label' => __( 'Text', 'portfolio-theme' ),
					'name'  => 'home_contact_text',
					'type'  => 'textarea',
					'rows'  => 2,
				),
			),
		)
	);
}
add_action( 'acf/init', 'portfolio_theme_register_home_fields' );

/**
 * Returns a Home field as a trimmed string, or '' when SCF is off or the
 * field is empty.
 *
 * @param string $name Field name.
 * @return string
 */
function portfolio_theme_home_text( $name ) {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}

	$value = get_field( $name );

	return is_string( $value ) ? trim( $value ) : '';
}

/**
 * Returns the URL of the page whose English path is given (/work/), in the
 * current language: its Polylang translation, or the English page when it has
 * none. A Spanish page has another slug (/es/trabajo/), so a fixed path would
 * take a Spanish visitor to the English site. '/' is the home of the language.
 * Without Polylang it is the plain site path.
 *
 * @param string $path English site path, as written in docs/sitemap.md.
 * @return string
 */
function portfolio_theme_page_url( $path ) {
	static $urls = array();

	$path = trim( (string) $path, '/' );
	$key  = ( function_exists( 'pll_current_language' ) ? (string) pll_current_language() : '' ) . ':' . $path;

	if ( ! isset( $urls[ $key ] ) ) {
		if ( '' === $path ) {
			$urls[ $key ] = function_exists( 'pll_home_url' ) ? (string) pll_home_url() : home_url( '/' );
		} else {
			$urls[ $key ] = home_url( '/' . $path . '/' );

			$page = function_exists( 'pll_get_post' ) ? get_page_by_path( $path ) : null;
			if ( $page ) {
				$translation  = pll_get_post( $page->ID );
				$urls[ $key ] = (string) get_permalink( $translation ? $translation : $page );
			}
		}
	}

	return $urls[ $key ];
}

/**
 * Turns a site path (/work/) into a full URL, in the current language when
 * the path is a page of the sitemap. Other paths and full URLs pass through.
 *
 * @param string $url Path or URL.
 * @return string
 */
function portfolio_theme_resolve_url( $url ) {
	$url = trim( (string) $url );

	if ( preg_match( '#^/[a-z0-9-]*/?$#', $url ) ) {
		return portfolio_theme_page_url( $url );
	}

	if ( str_starts_with( $url, '/' ) && ! str_starts_with( $url, '//' ) ) {
		return home_url( $url );
	}

	return $url;
}

/**
 * Returns the Home testimonials in the shape quote-a expects. Rows without a
 * quote or a name are dropped. Read from the front page, so other pages
 * (About) show the same quotes without a second field.
 *
 * @param int $limit Maximum quotes; 0 for all.
 * @return array<int, array{quote: string, name: string, role: string, url: string}>
 */
function portfolio_theme_home_quotes( $limit = 0 ) {
	$front_id = (int) get_option( 'page_on_front' );
	$rows     = function_exists( 'get_field' ) && $front_id ? get_field( 'home_testimonials', $front_id ) : null;
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$items = array();
	foreach ( $rows as $row ) {
		$quote = trim( (string) ( $row['quote'] ?? '' ) );
		$name  = trim( (string) ( $row['name'] ?? '' ) );
		if ( '' === $quote || '' === $name ) {
			continue;
		}

		$items[] = array(
			'quote' => $quote,
			'name'  => $name,
			'role'  => trim( (string) ( $row['role'] ?? '' ) ),
			'url'   => trim( (string) ( $row['url'] ?? '' ) ),
		);
	}

	return $limit ? array_slice( $items, 0, $limit ) : $items;
}

/**
 * Returns the projects placed on the Home (the project's "Home" field), as
 * row-a items. Only projects whose client may be named, in menu order.
 *
 * Selected work rows keep their line (what I did); Also built rows are name
 * and year · type only. Both link to the project's page.
 *
 * @param string $placement "selected" or "also_built".
 * @param int    $limit     Maximum rows; 0 for no limit.
 * @return array<int, array{title: string, url: string, meta: string, text: string}>
 */
function portfolio_theme_home_project_rows( $placement, $limit = 0 ) {
	$posts = array_values(
		array_filter(
			portfolio_theme_projects(),
			static fn( $post ) => portfolio_theme_field( 'project_home', $post->ID ) === $placement
		)
	);

	if ( $limit ) {
		$posts = array_slice( $posts, 0, $limit );
	}

	$rows = portfolio_theme_project_rows( $posts );

	if ( 'also_built' === $placement ) {
		$rows = array_map( static fn( $row ) => array_merge( $row, array( 'text' => '' ) ), $rows );
	}

	return $rows;
}

/**
 * Returns the latest published posts as row list items: date as meta, title
 * as the link.
 *
 * @param int $count Number of posts.
 * @return array<int, array{title: string, url: string, meta: string, datetime: string, text: string, rel: string}>
 */
function portfolio_theme_latest_note_rows( $count = 3 ) {
	$posts = get_posts(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'suppress_filters'    => false,
		)
	);

	return array_map(
		static fn( $post ) => array(
			'title'    => get_the_title( $post ),
			'url'      => get_permalink( $post ),
			'meta'     => get_the_date( '', $post ),
			'datetime' => get_the_date( 'Y-m-d', $post ),
			'text'     => '',
			'rel'      => '',
		),
		$posts
	);
}
