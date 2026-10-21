<?php
/**
 * Translated URLs for the content types, with Polylang (free).
 *
 * Polylang free translates the slug of a page (/es/trabajo/) but not the base
 * of a post type or of the notes (/es/work/<slug>/), and it cannot share a
 * slug between translations, so a Spanish project that has the same slug as
 * its English one ends in -es. This file puts the translated base in the
 * Spanish URL and drops that suffix from the public URL:
 *
 *   /work/ramon-esteve/  ->  /es/trabajo/ramon-esteve/
 *
 * Output: post_type_link and post_link build the Spanish URL. Input: a rewrite
 * rule per base, and the request filter puts the -es back to find the post.
 * The default language keeps the rewrite rules WordPress registers.
 *
 * @package PortfolioCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the URL base of each content type in the languages that do not use
 * the default one: post type => language slug => base. The notes are the
 * `post` type. A language without an entry keeps the English base.
 *
 * @return array<string, array<string, string>>
 */
function portfolio_core_url_bases() {
	return (array) apply_filters(
		'portfolio_core_url_bases',
		array(
			'project' => array( 'es' => 'trabajo' ),
			'service' => array( 'es' => 'servicios' ),
			'post'    => array( 'es' => 'notas' ),
		)
	);
}

/**
 * Tells whether Polylang puts the language in a folder (/es/). The rules and
 * URLs below assume it: the setting is "The language is set from the
 * directory name in pretty permalinks".
 *
 * @return bool
 */
function portfolio_core_uses_language_directories() {
	return function_exists( 'PLL' ) && PLL() && isset( PLL()->options['force_lang'] ) && 1 === (int) PLL()->options['force_lang'];
}

/**
 * Returns the URL a non-default language starts at: /es/. Not
 * pll_home_url(), which is the Spanish front page when there is one
 * (/es/inicio/).
 *
 * @param string $lang Language slug.
 * @return string
 */
function portfolio_core_language_root( $lang ) {
	return home_url( '/' . $lang . '/' );
}

/**
 * Returns the slug a post shows in its URL: its own, or, when Polylang added
 * the language suffix because the translation has the same slug, without it.
 *
 * @param WP_Post $post Post.
 * @param string  $lang Language slug of the post.
 * @return string
 */
function portfolio_core_public_slug( $post, $lang ) {
	$slug   = $post->post_name;
	$suffix = '-' . $lang;

	if ( ! str_ends_with( $slug, $suffix ) || ! function_exists( 'pll_get_post' ) || ! function_exists( 'pll_default_language' ) ) {
		return $slug;
	}

	// Only when the shorter slug is exactly the default-language translation's.
	$short  = substr( $slug, 0, -strlen( $suffix ) );
	$source = pll_get_post( $post->ID, pll_default_language() );
	if ( $source && get_post_field( 'post_name', $source ) === $short ) {
		return $short;
	}

	return $slug;
}

/**
 * Builds the translated URL of a published project, service or note.
 *
 * @param string          $url  Permalink as Polylang left it.
 * @param WP_Post|integer $post Post.
 * @return string
 */
function portfolio_core_translate_permalink( $url, $post ) {
	$post = get_post( $post );
	if ( ! $post || '' === $post->post_name || ! function_exists( 'pll_get_post_language' ) || ! portfolio_core_uses_language_directories() ) {
		return $url;
	}

	// Drafts (?p=ID) and the permalink editor (%postname%) keep core's URL.
	if ( str_contains( $url, '?' ) || str_contains( $url, '%' ) ) {
		return $url;
	}

	$lang  = pll_get_post_language( $post->ID );
	$bases = portfolio_core_url_bases();
	if ( ! $lang || ! isset( $bases[ $post->post_type ][ $lang ] ) ) {
		return $url;
	}

	return portfolio_core_language_root( $lang ) . $bases[ $post->post_type ][ $lang ] . '/' . portfolio_core_public_slug( $post, $lang ) . '/';
}
add_filter( 'post_type_link', 'portfolio_core_translate_permalink', 20, 2 );
add_filter( 'post_link', 'portfolio_core_translate_permalink', 20, 2 );

/**
 * Pages have no base, so only the shared-slug suffix needs undoing: a Spanish
 * page whose slug is the English one plus "-es" is linked without the suffix.
 *
 * @param string $url     Page URL, with the language directory already added.
 * @param int    $post_id Page ID.
 * @return string
 */
function portfolio_core_translate_page_link( $url, $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post || ! function_exists( 'pll_get_post_language' ) || ! portfolio_core_uses_language_directories() ) {
		return $url;
	}

	$lang = pll_get_post_language( $post->ID );
	if ( ! $lang ) {
		return $url;
	}

	$public = portfolio_core_public_slug( $post, $lang );
	if ( $public === $post->post_name ) {
		return $url;
	}

	// Only the last segment; ugly permalinks (?page_id=) do not match.
	$changed = preg_replace( '#/' . preg_quote( $post->post_name, '#' ) . '/?$#', '/' . $public . '/', $url );

	return null === $changed ? $url : $changed;
}
add_filter( 'page_link', 'portfolio_core_translate_page_link', 20, 2 );

/**
 * Adds one rewrite rule per translated base, ahead of the rest.
 */
function portfolio_core_add_rewrite_rules() {
	if ( ! function_exists( 'pll_languages_list' ) || ! portfolio_core_uses_language_directories() ) {
		return;
	}

	$languages = pll_languages_list();
	foreach ( portfolio_core_url_bases() as $type => $per_language ) {
		foreach ( $per_language as $lang => $base ) {
			if ( in_array( $lang, $languages, true ) ) {
				add_rewrite_rule(
					'^' . preg_quote( $lang, '#' ) . '/' . preg_quote( $base, '#' ) . '/([^/]+)/?$',
					'index.php?post_type=' . $type . '&name=$matches[1]&lang=' . $lang,
					'top'
				);
			}
		}
	}
}
add_action( 'init', 'portfolio_core_add_rewrite_rules', 20 );

/**
 * Finds a post whose slug carries Polylang's language suffix: the URL has
 * the shared slug, the post has slug-es.
 *
 * @param array $query_vars Query variables of the request.
 * @return array
 */
function portfolio_core_resolve_shared_slug( $query_vars ) {
	if ( empty( $query_vars['name'] ) || empty( $query_vars['lang'] ) || empty( $query_vars['post_type'] ) || ! function_exists( 'pll_get_post_language' ) ) {
		return $query_vars;
	}

	$type  = $query_vars['post_type'];
	$lang  = $query_vars['lang'];
	$bases = portfolio_core_url_bases();
	if ( ! is_string( $type ) || ! is_string( $lang ) || ! isset( $bases[ $type ][ $lang ] ) ) {
		return $query_vars;
	}

	$candidate = get_page_by_path( $query_vars['name'] . '-' . $lang, OBJECT, $type );
	if ( $candidate && pll_get_post_language( $candidate->ID ) === $lang ) {
		$query_vars['name'] = $candidate->post_name;
	}

	return $query_vars;
}
add_filter( 'request', 'portfolio_core_resolve_shared_slug' );

/**
 * The page counterpart of the resolver above: /es/cookies/ is the Spanish
 * page whose slug is "cookies-es", when there is one.
 *
 * @param array $query_vars Request query vars.
 * @return array
 */
function portfolio_core_resolve_shared_page_slug( $query_vars ) {
	if ( empty( $query_vars['pagename'] ) || empty( $query_vars['lang'] ) || ! is_string( $query_vars['pagename'] ) || ! is_string( $query_vars['lang'] ) || ! function_exists( 'pll_get_post_language' ) ) {
		return $query_vars;
	}

	$candidate = get_page_by_path( $query_vars['pagename'] . '-' . $query_vars['lang'] );
	if ( $candidate && pll_get_post_language( $candidate->ID ) === $query_vars['lang'] ) {
		$query_vars['pagename'] = get_page_uri( $candidate );
	}

	return $query_vars;
}
add_filter( 'request', 'portfolio_core_resolve_shared_page_slug' );

/**
 * Adds x-default to the hreflang links, pointing to the default language.
 *
 * @param array<string, string> $hreflangs URLs by hreflang code.
 * @return array<string, string>
 */
function portfolio_core_hreflang_x_default( $hreflangs ) {
	$default = function_exists( 'pll_default_language' ) ? pll_default_language() : '';
	if ( $default && isset( $hreflangs[ $default ] ) && ! isset( $hreflangs['x-default'] ) ) {
		$hreflangs['x-default'] = $hreflangs[ $default ];
	}

	return $hreflangs;
}
add_filter( 'pll_rel_hreflang_attributes', 'portfolio_core_hreflang_x_default' );

/**
 * Switches Polylang on for projects and services from code, so a fresh
 * install (Playground, CI) translates them without anyone ticking the boxes
 * in Languages > Settings. In that screen Polylang shows them checked and
 * locked, because they are active programmatically.
 *
 * Added at plugin load: Polylang asks for the list early (see the filter's
 * documentation in its source).
 *
 * @param array<string, string> $post_types Post type names, as keys and values.
 * @return array<string, string>
 */
function portfolio_core_polylang_post_types( $post_types ) {
	return array_merge(
		$post_types,
		array(
			'project' => 'project',
			'service' => 'service',
		)
	);
}
add_filter( 'pll_get_post_types', 'portfolio_core_polylang_post_types' );

/**
 * Sends the URL a translated post had before (/es/work/ramon-esteve-es/) to
 * its current one (/es/trabajo/ramon-esteve/). Polylang still answers on the
 * English base, and WordPress does not redirect it, so without this the same
 * page would live at two addresses.
 */
function portfolio_core_redirect_to_translated_url() {
	if ( is_admin() || is_preview() || ! is_singular( array_merge( array_keys( portfolio_core_url_bases() ), array( 'page' ) ) ) || ! function_exists( 'pll_get_post_language' ) ) {
		return;
	}

	$post = get_queried_object();
	$lang = $post instanceof WP_Post ? pll_get_post_language( $post->ID ) : '';
	if ( ! $lang ) {
		return;
	}

	if ( 'page' === $post->post_type ) {
		// Pages have no base: only the shared-slug suffix (/es/cookies-es/) is redirected.
		if ( portfolio_core_public_slug( $post, $lang ) === $post->post_name ) {
			return;
		}
	} elseif ( ! isset( portfolio_core_url_bases()[ $post->post_type ][ $lang ] ) ) {
		return;
	}

	$expected = get_permalink( $post );
	$request  = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path     = untrailingslashit( (string) wp_parse_url( $request, PHP_URL_PATH ) );
	if ( '' === $request || untrailingslashit( (string) wp_parse_url( $expected, PHP_URL_PATH ) ) === $path ) {
		return;
	}

	$query = (string) wp_parse_url( $request, PHP_URL_QUERY );
	wp_safe_redirect( '' === $query ? $expected : $expected . '?' . $query, 301 );
	exit;
}
add_action( 'template_redirect', 'portfolio_core_redirect_to_translated_url' );

/**
 * Loads the plugin's front-end translations (the Related block's labels).
 */
function portfolio_core_load_textdomain() {
	load_plugin_textdomain( 'portfolio-core', false, dirname( plugin_basename( __DIR__ ) ) . '/languages' );
}
add_action( 'init', 'portfolio_core_load_textdomain' );
