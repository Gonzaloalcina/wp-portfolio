<?php
/**
 * Builds the footer menus: a reduced copy of the main menu plus Resume and
 * "How this site is built", one menu per language, assigned to the theme's
 * `footer` location through Polylang. WordPress maintenance is not here: it
 * is linked from the Services hub and from each service page.
 *
 * Items are pages, not custom links, so each follows its language's
 * permalinks (/es/…) and the address of the install. Safe to rerun: it
 * rebuilds the items of the "Footer" and "Footer ES" menus.
 *
 * Usage (from the repo, with XAMPP's PHP, see CLAUDE.md):
 *   wp eval-file scripts/seed-footer-menu.php --path=<WordPress install>
 *
 * See docs/decisions/07-information-architecture.md.
 *
 * @package PortfolioTheme
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- WP-CLI script, not loaded by the theme.

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

// English slugs, in menu order. Spanish pages are their translations.
$footer_slugs = array( 'about', 'work', 'services', 'notes', 'contact', 'resume', 'how-this-site-is-built' );
$languages    = function_exists( 'pll_languages_list' ) ? pll_languages_list() : array( 'en' );

$menus = array();
foreach ( $languages as $language ) {
	$name   = 'en' === $language ? 'Footer' : 'Footer ' . strtoupper( $language );
	$footer = wp_get_nav_menu_object( $name );

	$menu_id = $footer ? (int) $footer->term_id : (int) wp_create_nav_menu( $name );
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
		wp_delete_post( $item->ID, true ); // Menu items only: rebuilt below.
	}

	foreach ( $footer_slugs as $slug ) {
		$page_post = get_page_by_path( $slug );
		$page_id   = $page_post ? (int) $page_post->ID : 0;
		if ( $page_id && 'en' !== $language && function_exists( 'pll_get_post' ) ) {
			$page_id = (int) pll_get_post( $page_id, $language );
		}
		if ( ! $page_id ) {
			WP_CLI::warning( "No {$language} page for {$slug}: skipped." );
			continue;
		}

		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-status'    => 'publish',
			)
		);
	}

	$menus[ $language ] = $menu_id;
	WP_CLI::log( "{$name}: menu {$menu_id}" );
}

// Polylang keeps one menu per language and location in its own option, and
// only maps locations already in the theme mod: set both.
if ( function_exists( 'PLL' ) && PLL() ) {
	$pll_menus = PLL()->options->get( 'nav_menus' );

	$pll_menus[ get_stylesheet() ]['footer'] = $menus;
	PLL()->options->set( 'nav_menus', $pll_menus );
	PLL()->options->save();
}

$locations           = (array) get_theme_mod( 'nav_menu_locations', array() );
$locations['footer'] = $menus['en'] ?? reset( $menus );
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::success( 'Footer menus built.' );
