<?php
/**
 * Image sizes and content width.
 *
 * The layout is narrow: a 40rem text column (640px) and 64rem wide images
 * (1024px). Images take the width of their container (base.css); these sizes
 * give the browser a file a little larger than each common case needs:
 *
 * - medium        640  text column at 1x, phones at 1x.
 * - medium_large  960  phones at 2x (about 680-780px needed).
 * - large        1280  text column on retina, phones at 3x, wide images at 1x.
 * - full        ≤2048  wide images on retina; bigger uploads are scaled down.
 * - thumbnail     150  square crop for the media library grid in the admin.
 *
 * Sizes are WordPress options (Settings › Media), stored per database. They
 * are forced here so local, production and Playground generate the same
 * files; the Settings › Media fields have no effect.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Content width in px, from the layout.text token. WordPress sizes embeds
 * (YouTube and others) to it; the default without it is 500px.
 */
function portfolio_theme_content_width() {
	$token = portfolio_theme_design_token( 'layout.text' );

	// Token values are rem; 1rem is 16px at the default root size.
	$GLOBALS['content_width'] = $token ? (int) round( (float) $token['value'] * 16 ) : 640;
}
add_action( 'after_setup_theme', 'portfolio_theme_content_width', 0 );

/**
 * Returns a layout token in rem as a number, e.g. 40 for layout.text.
 *
 * @param string $path     Token path.
 * @param float  $fallback Value if the token is missing.
 * @return float
 */
function portfolio_theme_layout_rem( $path, $fallback ) {
	$token = portfolio_theme_design_token( $path );

	return $token ? (float) $token['value'] : $fallback;
}

/**
 * Builds a sizes attribute for a column of the given width: the column width
 * on screens wide enough to hold it plus the gutters, the viewport minus the
 * gutters below that. Same rule as the CSS: min(column, 100% - 2 × gutter).
 *
 * @param string $column_token layout.text or layout.wide.
 * @return string
 */
function portfolio_theme_column_sizes( $column_token ) {
	$column  = portfolio_theme_layout_rem( $column_token, 'layout.wide' === $column_token ? 64 : 40 );
	$gutters = 2 * portfolio_theme_layout_rem( 'layout.gutter', 1.5 );

	return sprintf( '(min-width: %1$srem) %2$srem, calc(100vw - %3$srem)', $column + $gutters, $column, $gutters );
}

/**
 * Default sizes attribute: the text column. Core's default is the file's own
 * width (e.g. 1280px), which makes retina screens fetch the 2048px original
 * for a 640px column. Lazy-loaded images still get "auto" prepended by core,
 * so browsers that support it use the real layout width instead.
 *
 * @return string
 */
function portfolio_theme_image_sizes() {
	return portfolio_theme_column_sizes( 'layout.text' );
}
add_filter( 'wp_calculate_image_sizes', 'portfolio_theme_image_sizes' );

/**
 * Wide image blocks use the wide column. Core keeps a sizes attribute that is
 * already there and only adds srcset, so it is set here, before
 * wp_filter_content_tags() runs on the content.
 *
 * @param string $block_content Rendered block.
 * @param array  $block         Block data.
 * @return string
 */
function portfolio_theme_wide_image_sizes( $block_content, $block ) {
	if ( 'wide' !== ( $block['attrs']['align'] ?? '' ) ) {
		return $block_content;
	}

	$tags = new WP_HTML_Tag_Processor( $block_content );

	if ( $tags->next_tag( 'img' ) ) {
		$tags->set_attribute( 'sizes', portfolio_theme_column_sizes( 'layout.wide' ) );
	}

	return $tags->get_updated_html();
}
add_filter( 'render_block_core/image', 'portfolio_theme_wide_image_sizes', 10, 2 );

/**
 * Image size options. A height of 0 means no limit, so tall screenshots are
 * not shrunk to fit a box.
 *
 * @return array<string, int> Option name => value.
 */
function portfolio_theme_image_size_options() {
	return array(
		'thumbnail_size_w'    => 150,
		'thumbnail_size_h'    => 150,
		'thumbnail_crop'      => 1,
		'medium_size_w'       => 640,
		'medium_size_h'       => 0,
		'medium_large_size_w' => 960,
		'medium_large_size_h' => 0,
		'large_size_w'        => 1280,
		'large_size_h'        => 0,
	);
}

foreach ( array_keys( portfolio_theme_image_size_options() ) as $portfolio_option ) {
	add_filter(
		"pre_option_{$portfolio_option}",
		static fn() => portfolio_theme_image_size_options()[ $portfolio_option ]
	);
}
unset( $portfolio_option );

/**
 * Drops core's 1536x1536 and 2048x2048 sizes: the scaled original (up to
 * 2048px, below) already covers wide images on retina screens.
 *
 * @param string[] $sizes Intermediate size names.
 * @return string[]
 */
function portfolio_theme_remove_image_sizes( $sizes ) {
	return array_values( array_diff( $sizes, array( '1536x1536', '2048x2048' ) ) );
}
add_filter( 'intermediate_image_sizes', 'portfolio_theme_remove_image_sizes' );

/**
 * Uploads wider or taller than 2048px are scaled down to 2048px and used as
 * the full size (WordPress keeps the untouched original on disk). The default
 * is 2560px, more than this layout ever shows.
 *
 * @return int
 */
function portfolio_theme_big_image_threshold() {
	return 2048;
}
add_filter( 'big_image_size_threshold', 'portfolio_theme_big_image_threshold' );

/**
 * Enqueues the autoplay module for screen recordings, only on pages whose
 * content has one (a video with data-autoplay).
 */
function portfolio_theme_enqueue_video_autoplay() {
	if ( ! is_singular() || false === strpos( (string) get_post_field( 'post_content', get_queried_object_id() ), 'data-autoplay' ) ) {
		return;
	}

	$file = 'assets/js/video-autoplay.js';

	wp_enqueue_script_module(
		'portfolio-theme/video-autoplay',
		get_theme_file_uri( $file ),
		array(),
		(string) filemtime( get_theme_file_path( $file ) )
	);
}
add_action( 'wp_enqueue_scripts', 'portfolio_theme_enqueue_video_autoplay' );

/**
 * Allows <source> (src, type) in post content, so a video can offer WebM
 * with an MP4 fallback. Core allows <video> but not its sources.
 *
 * @param array  $tags    Allowed tags.
 * @param string $context Context.
 * @return array
 */
function portfolio_theme_allow_video_sources( $tags, $context ) {
	if ( 'post' === $context ) {
		$tags['source'] = array(
			'src'  => true,
			'type' => true,
		);
	}
	return $tags;
}
add_filter( 'wp_kses_allowed_html', 'portfolio_theme_allow_video_sources', 10, 2 );
