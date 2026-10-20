<?php
/**
 * Components: agnostic UI pieces named by what they are plus a design letter
 * (hero-a, row-a…), never by the page that uses them.
 *
 * Markup: template-parts/components/<name>.php, data in through $args.
 * Styles: assets/css/components/<name>.css, compiled into the site
 * stylesheet by scripts/build-css.mjs.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Renders a component.
 *
 * @param string $name Component name, e.g. "hero-a".
 * @param array  $args Data for the component's template.
 */
function portfolio_theme_component( $name, $args = array() ) {
	get_template_part( 'template-parts/components/' . $name, null, $args );
}

/**
 * Whether a URL leaves this site: http(s) on another host. Relative URLs,
 * anchors, mailto: and tel: stay.
 *
 * @param string $url URL.
 * @return bool
 */
function portfolio_theme_is_external( $url ) {
	$parts = wp_parse_url( (string) $url );
	if ( empty( $parts['host'] ) || ! in_array( $parts['scheme'] ?? '', array( 'http', 'https' ), true ) ) {
		return false;
	}

	return strtolower( $parts['host'] ) !== strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
}

/**
 * Attributes for a link, after href: external links open in a new tab, with
 * noopener and a description that says so (the note printed in the footer).
 *
 * @param string $url URL.
 * @param string $rel Extra rel values, e.g. "me".
 * @return string Escaped attributes with a leading space, or ''.
 */
function portfolio_theme_link_attrs( $url, $rel = '' ) {
	$rel = trim( (string) $rel );
	$out = '';

	if ( portfolio_theme_is_external( $url ) ) {
		$rel  = trim( $rel . ' noopener' );
		$out .= ' target="_blank" aria-describedby="new-tab-note"';
	}

	return ( '' === $rel ? '' : ' rel="' . esc_attr( $rel ) . '"' ) . $out;
}

/**
 * Applies portfolio_theme_link_attrs() to the external links in editor
 * content (notes, case studies, services).
 *
 * @param string $content HTML.
 * @return string
 */
function portfolio_theme_external_links_in_content( $content ) {
	if ( false === strpos( $content, '<a ' ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $content;
	}

	$tags = new WP_HTML_Tag_Processor( $content );
	while ( $tags->next_tag( 'a' ) ) {
		$href = (string) $tags->get_attribute( 'href' );
		if ( ! portfolio_theme_is_external( $href ) ) {
			continue;
		}

		$rel = array_filter( explode( ' ', (string) $tags->get_attribute( 'rel' ) ) );
		$tags->set_attribute( 'rel', implode( ' ', array_unique( array_merge( $rel, array( 'noopener' ) ) ) ) );
		$tags->set_attribute( 'target', '_blank' );
		$tags->set_attribute( 'aria-describedby', 'new-tab-note' );
	}

	return $tags->get_updated_html();
}
add_filter( 'the_content', 'portfolio_theme_external_links_in_content', 20 );
