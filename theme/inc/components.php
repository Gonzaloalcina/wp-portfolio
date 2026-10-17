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
