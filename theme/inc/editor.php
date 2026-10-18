<?php
/**
 * Block editor settings for blog posts.
 *
 * Limits the editor's presets to the design tokens, which also shrinks the
 * global-styles CSS printed on single posts.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Replaces core's default presets with the design system. A classic theme has
 * no theme.json, so the settings go through this filter.
 *
 * - Colour: off. The system is one paper, one ink and one grey, and they
 *   swap in dark mode; a colour fixed inside a post would break the dark
 *   theme. Text colour belongs to the theme.
 * - Gradients and duotone: off (the brief rules out gradients).
 * - Font sizes: the four token sizes only, pointing at the token custom
 *   properties so they follow any token change. No custom sizes.
 * - Default spacing and shadow presets: off; nothing uses them. Aspect
 *   ratios stay: the image block uses them to crop.
 *
 * @param WP_Theme_JSON_Data $theme_json Theme data.
 * @return WP_Theme_JSON_Data
 */
function portfolio_theme_editor_presets( $theme_json ) {
	$font_sizes = array();

	foreach ( array(
		'sm'   => __( 'Small', 'portfolio-theme' ),
		'base' => __( 'Base', 'portfolio-theme' ),
		'lg'   => __( 'Large', 'portfolio-theme' ),
		'xl'   => __( 'Extra large', 'portfolio-theme' ),
	) as $slug => $name ) {
		$font_sizes[] = array(
			'slug' => $slug,
			'name' => $name,
			'size' => "var(--font-size-{$slug})",
		);
	}

	return $theme_json->update_with(
		array(
			'version'  => 3,
			'settings' => array(
				'color'      => array(
					'custom'           => false,
					'customGradient'   => false,
					'customDuotone'    => false,
					'defaultPalette'   => false,
					'defaultGradients' => false,
					'defaultDuotone'   => false,
					'palette'          => array(),
					'gradients'        => array(),
					'duotone'          => array(),
				),
				'typography' => array(
					'customFontSize'   => false,
					'defaultFontSizes' => false,
					'fontSizes'        => $font_sizes,
				),
				'spacing'    => array(
					'defaultSpacingSizes' => false,
				),
				'shadow'     => array(
					'defaultPresets' => false,
				),
			),
		)
	);
}
add_filter( 'wp_theme_json_data_theme', 'portfolio_theme_editor_presets' );

/**
 * The default* flags above only hide core's presets in the editor: core still
 * prints them (custom properties and .has-* classes, about 7 KB) so that old
 * content keeps its colours. This blog has no such content, so the presets
 * are emptied at the source.
 *
 * @param WP_Theme_JSON_Data $theme_json Core's default data.
 * @return WP_Theme_JSON_Data
 */
function portfolio_theme_remove_default_presets( $theme_json ) {
	return $theme_json->update_with(
		array(
			'version'  => 3,
			'settings' => array(
				'color'      => array(
					'palette'   => array(),
					'gradients' => array(),
					'duotone'   => array(),
				),
				'typography' => array(
					'fontSizes' => array(),
				),
				// Core generates its spacing presets from a scale, not a list.
				'spacing'    => array(
					'spacingScale' => array( 'steps' => 0 ),
				),
				'shadow'     => array(
					'presets' => array(),
				),
			),
		)
	);
}
add_filter( 'wp_theme_json_data_default', 'portfolio_theme_remove_default_presets' );

/**
 * Loads the tokens in the editor, so the font size presets and the
 * portfolio-core blocks (token custom properties) preview at their real
 * size. The block editor reads add_editor_style() files only when the theme
 * declares editor-styles support.
 */
function portfolio_theme_editor_styles() {
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/tokens.css' );
}
add_action( 'after_setup_theme', 'portfolio_theme_editor_styles' );
