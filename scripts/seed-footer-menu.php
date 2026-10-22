<?php
/**
 * Builds the footer menus, one per language, assigned through Polylang:
 *
 * - `footer`: a reduced copy of the main menu plus Resume and "How this site
 *   is built". WordPress maintenance is not here: it is linked from the
 *   Services hub and from each service page.
 * - `legal`: Privacy, Legal notice and Cookies, on their own row at the
 *   bottom of the footer, with "Cookie settings".
 *
 * Items are pages, not custom links, so each follows its language's
 * permalinks (/es/…) and the address of the install. Safe to rerun: it
 * rebuilds the items of the "Footer", "Footer ES", "Legal" and "Legal ES"
 * menus.
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

// Location => [ menu name, English slugs in menu order ]. Spanish pages are their translations.
$footer_menus = array(
	'footer' => array( 'Footer', array( 'about', 'work', 'services', 'notes', 'contact', 'resume', 'how-this-site-is-built' ) ),
	'legal'  => array( 'Legal', array( 'privacy', 'legal-notice', 'cookies' ) ),
);
$languages    = function_exists( 'pll_languages_list' ) ? pll_languages_list() : array( 'en' );
$locations    = (array) get_theme_mod( 'nav_menu_locations', array() );
$pll_menus    = function_exists( 'PLL' ) && PLL() ? PLL()->options->get( 'nav_menus' ) : null;

foreach ( $footer_menus as $location => list( $base_name, $slugs ) ) {
	$menus = array();
	foreach ( $languages as $language ) {
		$name = 'en' === $language ? $base_name : $base_name . ' ' . strtoupper( $language );
		$menu = wp_get_nav_menu_object( $name );

		$menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $name );
		foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
			wp_delete_post( $item->ID, true ); // Menu items only: rebuilt below.
		}

		foreach ( $slugs as $slug ) {
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
	if ( is_array( $pll_menus ) ) {
		$pll_menus[ get_stylesheet() ][ $location ] = $menus;
	}
	$locations[ $location ] = $menus['en'] ?? reset( $menus );
}

if ( is_array( $pll_menus ) ) {
	PLL()->options->set( 'nav_menus', $pll_menus );
	PLL()->options->save();
}
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::success( 'Footer menus built.' );
