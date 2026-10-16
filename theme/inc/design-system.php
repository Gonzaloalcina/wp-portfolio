<?php
/**
 * Helpers for the /design-system page.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Returns the generated token data (theme/assets/data/tokens.json).
 *
 * @return array{tokens: array, colors: array, derived: array}
 */
function portfolio_theme_design_tokens() {
	static $data = null;

	if ( null === $data ) {
		$data = wp_json_file_decode(
			get_theme_file_path( 'assets/data/tokens.json' ),
			array( 'associative' => true )
		);
		$data = is_array( $data ) ? $data : array(
			'tokens'  => array(),
			'colors'  => array(),
			'derived' => array(),
		);
	}

	return $data;
}

/**
 * Returns the tokens whose path starts with a prefix, e.g. "space." or "font.size.".
 *
 * @param string $prefix Token path prefix.
 * @return array
 */
function portfolio_theme_design_tokens_by_prefix( $prefix ) {
	return array_values(
		array_filter(
			portfolio_theme_design_tokens()['tokens'],
			static fn( $token ) => str_starts_with( $token['path'], $prefix )
		)
	);
}

/**
 * Returns a single token by its path, or null.
 *
 * @param string $path Token path, e.g. "unit".
 * @return array|null
 */
function portfolio_theme_design_token( $path ) {
	foreach ( portfolio_theme_design_tokens()['tokens'] as $token ) {
		if ( $token['path'] === $path ) {
			return $token;
		}
	}
	return null;
}

/**
 * Formats a dimension for display: rem values get their px equivalent at the
 * default 16px root size, e.g. "1rem (16px)".
 *
 * @param string $value Dimension, e.g. "1rem" or "1px".
 * @return string
 */
function portfolio_theme_format_dimension( $value ) {
	if ( ! str_ends_with( $value, 'rem' ) ) {
		return $value;
	}

	return sprintf( '%s (%spx)', $value, (float) $value * 16 );
}

/**
 * WCAG 2 relative luminance of a #RRGGBB colour.
 *
 * @param string $hex Colour.
 * @return float
 */
function portfolio_theme_relative_luminance( $hex ) {
	$hex      = ltrim( $hex, '#' );
	$channels = array_map(
		static function ( $pair ) {
			$c = hexdec( $pair ) / 255;
			return $c <= 0.04045 ? $c / 12.92 : ( ( $c + 0.055 ) / 1.055 ) ** 2.4;
		},
		str_split( $hex, 2 )
	);

	return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

/**
 * WCAG 2 contrast ratio between two #RRGGBB colours.
 *
 * @param string $foreground Colour.
 * @param string $background Colour.
 * @return float
 */
function portfolio_theme_contrast_ratio( $foreground, $background ) {
	$a = portfolio_theme_relative_luminance( $foreground );
	$b = portfolio_theme_relative_luminance( $background );

	return ( max( $a, $b ) + 0.05 ) / ( min( $a, $b ) + 0.05 );
}
