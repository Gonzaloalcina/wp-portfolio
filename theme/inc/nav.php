<?php
/**
 * Main navigation: a WordPress menu (Appearance › Menus) rendered by the
 * nav-a component, plus the module that turns it into a disclosure on narrow
 * screens.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registers the menu locations: the main menu (nav-a) and the footer links
 * (link-row-a), for pages kept out of the main menu. With Polylang, each
 * language assigns its own menu to them.
 */
function portfolio_theme_register_menus() {
	register_nav_menus(
		array(
			'primary' => __( 'Main menu', 'portfolio-theme' ),
			'footer'  => __( 'Footer links', 'portfolio-theme' ),
			'legal'   => __( 'Legal links', 'portfolio-theme' ),
		)
	);
}
add_action( 'after_setup_theme', 'portfolio_theme_register_menus' );

/**
 * Returns the top-level items of the menu in a location as link-row-a links.
 *
 * @param string $location Menu location.
 * @return array<int, array{title: string, url: string}>
 */
function portfolio_theme_menu_links( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}

	$items = wp_get_nav_menu_items( $locations[ $location ] );
	if ( ! is_array( $items ) ) {
		return array();
	}

	$links = array();
	foreach ( $items as $item ) {
		if ( ! $item->menu_item_parent ) {
			$links[] = array(
				'title' => $item->title,
				'url'   => $item->url,
			);
		}
	}

	return $links;
}

/**
 * Whether a wp_nav_menu() call is the one nav-a makes, so the class filters
 * below leave any other menu alone.
 *
 * @param stdClass $args wp_nav_menu() arguments.
 * @return bool
 */
function portfolio_theme_is_nav_a( $args ) {
	return isset( $args->portfolio_component ) && 'nav-a' === $args->portfolio_component;
}

/**
 * Replaces core's item classes (menu-item, menu-item-type-custom…) with the
 * component's. The current page is marked by aria-current, which core adds
 * to the link.
 *
 * @param string[] $classes Item classes.
 * @param WP_Post  $item    Menu item.
 * @param stdClass $args    wp_nav_menu() arguments.
 * @return string[]
 */
function portfolio_theme_nav_item_classes( $classes, $item, $args ) {
	return portfolio_theme_is_nav_a( $args ) ? array( 'nav-a__item' ) : $classes;
}
add_filter( 'nav_menu_css_class', 'portfolio_theme_nav_item_classes', 10, 3 );

/**
 * Drops core's menu-item-N ids from nav-a items.
 *
 * @param string   $id   Item id.
 * @param WP_Post  $item Menu item.
 * @param stdClass $args wp_nav_menu() arguments.
 * @return string
 */
function portfolio_theme_nav_item_id( $id, $item, $args ) {
	return portfolio_theme_is_nav_a( $args ) ? '' : $id;
}
add_filter( 'nav_menu_item_id', 'portfolio_theme_nav_item_id', 10, 3 );

/**
 * Adds the component's class to nav-a links.
 *
 * @param array    $atts Link attributes.
 * @param WP_Post  $item Menu item.
 * @param stdClass $args wp_nav_menu() arguments.
 * @return array
 */
function portfolio_theme_nav_link_attributes( $atts, $item, $args ) {
	if ( portfolio_theme_is_nav_a( $args ) ) {
		$atts['class'] = 'nav-a__link';
	}
	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'portfolio_theme_nav_link_attributes', 10, 3 );

/**
 * Enqueues the disclosure module (native ES module, no build) when the menu
 * is assigned.
 */
function portfolio_theme_enqueue_nav() {
	if ( ! has_nav_menu( 'primary' ) ) {
		return;
	}

	$file = 'assets/js/nav-a.js';

	wp_enqueue_script_module(
		'portfolio-theme/nav-a',
		get_theme_file_uri( $file ),
		array(),
		(string) filemtime( get_theme_file_path( $file ) )
	);
}
add_action( 'wp_enqueue_scripts', 'portfolio_theme_enqueue_nav' );
