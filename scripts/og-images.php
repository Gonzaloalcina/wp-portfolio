<?php
/**
 * Per-page social images, the WordPress side of scripts/og-images.mjs.
 *
 * Modes:
 *
 * - export <list.json>: writes the pages that get an image: every published
 *   page, note, project and service in both languages, except the front
 *   pages (they keep the default image set in Rank Math), noindex pages and
 *   redirected ones.
 * - import <dir>: puts each PNG in the media library (replacing the file
 *   on a rerun, same attachment) and sets it as the page's Facebook image
 *   in Rank Math; Twitter reuses it.
 *
 * Usage (WP-CLI):
 *   wp eval-file scripts/og-images.php export /tmp/og.json
 *   node scripts/og-images.mjs /tmp/og.json /tmp/og
 *   wp eval-file scripts/og-images.php import /tmp/og
 *
 * @package portfolio-core
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.WP.AlternativeFunctions -- CLI script, local files.

$portfolio_og_mode = $args[0] ?? '';
$portfolio_og_path = $args[1] ?? '';

if ( ! in_array( $portfolio_og_mode, array( 'export', 'import' ), true ) || ! $portfolio_og_path ) {
	WP_CLI::error( 'Usage: wp eval-file scripts/og-images.php export <list.json> | import <dir>' );
}

/**
 * Pages that get their own image.
 *
 * @return WP_Post[]
 */
function portfolio_og_posts() {
	$fronts = array_filter( array( (int) get_option( 'page_on_front' ) ) );
	if ( function_exists( 'pll_get_post_translations' ) && $fronts ) {
		$fronts = array_map( 'intval', array_values( pll_get_post_translations( $fronts[0] ) ) );
	}

	$excluded = array_filter( array_map( 'intval', explode( ',', (string) ( get_option( 'rank-math-options-sitemap' )['exclude_posts'] ?? '' ) ) ) );

	$posts = get_posts(
		array(
			'post_type'   => array( 'page', 'post', 'project', 'service' ),
			'post_status' => 'publish',
			'numberposts' => -1,
			'orderby'     => 'ID',
			'order'       => 'ASC',
		)
	);

	return array_values(
		array_filter(
			$posts,
			static function ( $post ) use ( $fronts, $excluded ) {
				$robots = (array) get_post_meta( $post->ID, 'rank_math_robots', true );
				return ! in_array( $post->ID, $fronts, true )
					&& ! in_array( $post->ID, $excluded, true )
					&& ! in_array( 'noindex', $robots, true );
			}
		)
	);
}

/**
 * File name of a page's image: og-<slug>-<lang>.png.
 *
 * @param WP_Post $post Page.
 * @return string
 */
function portfolio_og_file( $post ) {
	$lang = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post->ID ) : 'en';
	return sprintf( 'og-%s-%s.png', preg_replace( '/-es$/', '', $post->post_name ), $lang ? $lang : 'en' );
}

if ( 'export' === $portfolio_og_mode ) {
	$portfolio_og_list = array_map(
		static fn( $post ) => array(
			'id'    => $post->ID,
			'title' => html_entity_decode( get_the_title( $post ), ENT_QUOTES ),
			'file'  => portfolio_og_file( $post ),
		),
		portfolio_og_posts()
	);
	file_put_contents( $portfolio_og_path, wp_json_encode( $portfolio_og_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
	WP_CLI::success( count( $portfolio_og_list ) . " pages written to $portfolio_og_path" );
	return;
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$portfolio_og_done = 0;
foreach ( portfolio_og_posts() as $portfolio_og_post ) {
	$portfolio_og_png = trailingslashit( $portfolio_og_path ) . portfolio_og_file( $portfolio_og_post );
	if ( ! is_readable( $portfolio_og_png ) ) {
		WP_CLI::warning( "No image for {$portfolio_og_post->ID}: $portfolio_og_png" );
		continue;
	}

	$portfolio_og_id      = (int) get_post_meta( $portfolio_og_post->ID, 'rank_math_facebook_image_id', true );
	$portfolio_og_is_ours = $portfolio_og_id && get_post_meta( $portfolio_og_id, '_portfolio_og_for', true );

	if ( $portfolio_og_is_ours ) {
		// Same attachment, new pixels.
		copy( $portfolio_og_png, get_attached_file( $portfolio_og_id ) );
		wp_update_attachment_metadata( $portfolio_og_id, wp_generate_attachment_metadata( $portfolio_og_id, get_attached_file( $portfolio_og_id ) ) );
	} else {
		$portfolio_og_tmp = wp_tempnam( basename( $portfolio_og_png ) );
		copy( $portfolio_og_png, $portfolio_og_tmp );
		$portfolio_og_id = media_handle_sideload(
			array(
				'name'     => basename( $portfolio_og_png ),
				'tmp_name' => $portfolio_og_tmp,
			),
			0,
			get_the_title( $portfolio_og_post )
		);
		if ( is_wp_error( $portfolio_og_id ) ) {
			WP_CLI::warning( "{$portfolio_og_post->ID}: " . $portfolio_og_id->get_error_message() );
			continue;
		}
		update_post_meta( $portfolio_og_id, '_portfolio_og_for', $portfolio_og_post->ID );
		update_post_meta( $portfolio_og_id, '_wp_attachment_image_alt', get_the_title( $portfolio_og_post ) );
		if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_get_post_language' ) ) {
			pll_set_post_language( $portfolio_og_id, pll_get_post_language( $portfolio_og_post->ID ) );
		}
	}

	update_post_meta( $portfolio_og_post->ID, 'rank_math_facebook_image_id', $portfolio_og_id );
	update_post_meta( $portfolio_og_post->ID, 'rank_math_facebook_image', wp_get_attachment_url( $portfolio_og_id ) );
	update_post_meta( $portfolio_og_post->ID, 'rank_math_twitter_use_facebook', 'on' );
	++$portfolio_og_done;
}

WP_CLI::success( "$portfolio_og_done images set." );
