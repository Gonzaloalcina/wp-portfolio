<?php
/**
 * Removes default WordPress output the site does not use.
 *
 * Gutenberg is only used for blog posts; pages are built with the theme and
 * SCF. Each removal below says why it is safe. Kept on purpose: Rank Math's
 * SEO output, feed links, speculation rules, the img auto-sizes rule and the
 * REST API itself (only its discovery links go).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Pages are edited with the classic editor and SCF fields; the block editor
 * stays for posts only, so no blocks (and no block CSS) end up on pages.
 *
 * @param bool   $use_block_editor Whether the post type uses the block editor.
 * @param string $post_type        Post type.
 * @return bool
 */
function portfolio_theme_block_editor_for_posts_only( $use_block_editor, $post_type ) {
	return 'page' === $post_type ? false : $use_block_editor;
}
add_filter( 'use_block_editor_for_post_type', 'portfolio_theme_block_editor_for_posts_only', 10, 2 );

/**
 * Removes head links, meta tags and HTTP headers nobody consumes.
 */
function portfolio_theme_cleanup_head() {
	// Emoji: current browsers render emoji natively. Drops the detection
	// module, its settings JSON and the inline styles.
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );

	// Generator meta: advertises the exact WordPress version.
	remove_action( 'wp_head', 'wp_generator' );

	// RSD (EditURI): discovery for XML-RPC clients, which this site does not use.
	remove_action( 'wp_head', 'rsd_link' );

	// Shortlink (?p=ID), in the head and as a Link header. The canonical URL
	// from Rank Math already identifies the page.
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );

	// REST API discovery links, in the head and as Link headers. The API keeps
	// working (the contact form uses it); it is just not announced on every page.
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );

	// oEmbed discovery: lets other sites embed our pages as cards. Embedding
	// YouTube and others inside our posts is not affected.
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 4 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
}
add_action( 'init', 'portfolio_theme_cleanup_head' );

/**
 * Block styles are only needed where block content is rendered: single blog
 * posts. Everywhere else they are about 18 KB of unused inline CSS. Per-block
 * styles (wp-block-image and so on) already load only when a block renders.
 */
function portfolio_theme_remove_block_styles() {
	if ( is_singular( 'post' ) ) {
		return;
	}

	// Common block styles.
	wp_dequeue_style( 'wp-block-library' );

	// Button and file block defaults that core adds for classic themes.
	wp_dequeue_style( 'classic-theme-styles' );

	// Global styles: --wp--preset--* custom properties and .has-* classes used
	// by editor colour and size controls. The theme uses its own tokens.
	wp_dequeue_style( 'global-styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

	// Layout styles generated from rendered blocks; empty without blocks.
	wp_dequeue_style( 'core-block-supports' );
	remove_action( 'wp_footer', 'wp_enqueue_stored_styles', 1 );
}
add_action( 'wp_enqueue_scripts', 'portfolio_theme_remove_block_styles', 100 );
