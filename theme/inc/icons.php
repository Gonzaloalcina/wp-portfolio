<?php
/**
 * Icon library and the reusable SCF icon field.
 *
 * Icons are Lucide SVGs (ISC licence) in assets/icons/. Every SVG in that
 * folder is available to templates and in the icon picker; adding a file adds
 * an icon.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

define( 'PORTFOLIO_THEME_ICON_FIELD', 'field_portfolio_icon' );

/**
 * Returns the available icon names, sorted.
 *
 * @return string[]
 */
function portfolio_theme_icon_names() {
	static $names = null;

	if ( null === $names ) {
		$files = glob( get_theme_file_path( 'assets/icons/*.svg' ) );
		$names = array_map( static fn( $file ) => basename( $file, '.svg' ), $files ? $files : array() );
		sort( $names );
	}

	return $names;
}

/**
 * Elements and attributes allowed in icon markup.
 *
 * @return array
 */
function portfolio_theme_icon_kses() {
	$shape = array(
		'd'      => true,
		'cx'     => true,
		'cy'     => true,
		'r'      => true,
		'x'      => true,
		'y'      => true,
		'width'  => true,
		'height' => true,
		'rx'     => true,
	);

	return array(
		'svg'    => array(
			'xmlns'           => true,
			'viewbox'         => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'class'           => true,
			'aria-hidden'     => true,
			'focusable'       => true,
		),
		'path'   => $shape,
		'circle' => $shape,
		'rect'   => $shape,
	);
}

/**
 * Returns the inline SVG for an icon, or an empty string if it does not exist.
 * Icons are decorative: they are hidden from assistive technology, so the
 * text next to them must carry the meaning.
 *
 * @param string $name Icon name (file name without .svg).
 * @return string
 */
function portfolio_theme_get_icon( $name ) {
	if ( ! in_array( $name, portfolio_theme_icon_names(), true ) ) {
		return '';
	}

	// Local theme file, not a remote URL.
	$svg = file_get_contents( get_theme_file_path( "assets/icons/{$name}.svg" ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	if ( false === $svg ) {
		return '';
	}

	// Drop the licence comment (the licence ships in assets/icons/LICENSE.txt).
	$svg = preg_replace( '/<!--.*?-->/s', '', $svg );

	// Size comes from CSS; replace the Lucide class with ours.
	$svg = preg_replace_callback(
		'/<svg\b[^>]*>/',
		static function ( $matches ) use ( $name ) {
			$tag = preg_replace( '/\s(?:class|width|height)="[^"]*"/', '', $matches[0] );
			return str_replace( '<svg', sprintf( '<svg class="icon icon-%s" aria-hidden="true" focusable="false"', $name ), $tag );
		},
		$svg
	);

	return trim( preg_replace( '/\s+/', ' ', $svg ) );
}

/**
 * Prints an icon.
 *
 * @param string $name Icon name.
 */
function portfolio_theme_icon( $name ) {
	echo wp_kses( portfolio_theme_get_icon( $name ), portfolio_theme_icon_kses() );
}

/**
 * Registers the reusable icon field group, and the demo group for the
 * /design-system page.
 *
 * The icon group is inactive: it is not shown on its own, only through Clone
 * fields in other groups (e.g. a card), so the picker is defined once.
 */
function portfolio_theme_register_icon_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'         => 'group_portfolio_icon',
			'title'       => __( 'Icon', 'portfolio-theme' ),
			'description' => __( 'Reusable icon picker. Add it to other field groups with a Clone field.', 'portfolio-theme' ),
			'active'      => false,
			'location'    => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			'fields'      => array(
				array(
					'key'           => PORTFOLIO_THEME_ICON_FIELD,
					'label'         => __( 'Icon', 'portfolio-theme' ),
					'name'          => 'icon',
					'type'          => 'radio',
					'choices'       => array(),
					'allow_null'    => 1,
					'layout'        => 'horizontal',
					'return_format' => 'value',
					'wrapper'       => array( 'class' => 'portfolio-icon-picker' ),
				),
			),
		)
	);

	$design_system = get_page_by_path( 'design-system' );
	if ( ! $design_system ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_portfolio_design_system_demo',
			'title'    => __( 'Design system demo', 'portfolio-theme' ),
			'location' => array(
				array(
					array(
						'param'    => 'page',
						'operator' => '==',
						'value'    => (string) $design_system->ID,
					),
				),
			),
			'fields'   => array(
				array(
					'key'         => 'field_portfolio_ds_card',
					'label'       => __( 'Demo card', 'portfolio-theme' ),
					'name'        => 'demo_card',
					'type'        => 'clone',
					'clone'       => array( PORTFOLIO_THEME_ICON_FIELD ),
					'display'     => 'seamless',
					'prefix_name' => 1,
				),
			),
		)
	);
}
add_action( 'acf/init', 'portfolio_theme_register_icon_fields' );

/**
 * Fills the picker with every icon in the library. The label is a span with
 * data-icon; the admin CSS draws the icon from it (SVG is not allowed in
 * choice labels).
 *
 * @param array $field Field settings.
 * @return array
 */
function portfolio_theme_icon_field_choices( $field ) {
	$field['choices'] = array();

	foreach ( portfolio_theme_icon_names() as $name ) {
		$field['choices'][ $name ] = sprintf(
			'<span class="portfolio-icon-choice" data-icon="%1$s">%2$s</span>',
			esc_attr( $name ),
			esc_html( $name )
		);
	}

	return $field;
}
add_filter( 'acf/load_field/key=' . PORTFOLIO_THEME_ICON_FIELD, 'portfolio_theme_icon_field_choices' );

/**
 * Admin styles for the picker: a grid of icon tiles. Each icon is drawn with a
 * CSS mask from its SVG file, so it takes the text colour.
 */
function portfolio_theme_icon_picker_styles() {
	// Prefixed with .acf-field so these rules outrank SCF's own radio styles.
	$picker = '.acf-field.portfolio-icon-picker';
	$css    = "
		{$picker} ul.acf-radio-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); gap: 8px; }
		{$picker} ul.acf-radio-list li { margin: 0; list-style: none; }
		{$picker} ul.acf-radio-list li label { position: relative; display: block; padding: 12px 4px 8px; border: 1px solid #dcdcde; border-radius: 2px; text-align: center; font-size: 12px; line-height: 16px; cursor: pointer; }
		{$picker} ul.acf-radio-list li label:hover { border-color: #8c8f94; }
		{$picker} ul.acf-radio-list li label.selected { border-color: #2271b1; box-shadow: 0 0 0 1px #2271b1; }
		{$picker} ul.acf-radio-list li label:focus-within { outline: 2px solid #2271b1; outline-offset: 2px; }
		{$picker} ul.acf-radio-list li input { position: absolute; opacity: 0; }
		.portfolio-icon-choice::before { content: ''; display: block; width: 24px; height: 24px; margin: 0 auto 6px; background: currentColor; mask: var(--icon) center / contain no-repeat; }
	";

	foreach ( portfolio_theme_icon_names() as $name ) {
		$css .= sprintf(
			'.portfolio-icon-choice[data-icon="%1$s"] { --icon: url("%2$s"); }',
			esc_attr( $name ),
			esc_url( get_theme_file_uri( "assets/icons/{$name}.svg" ) )
		);
	}

	wp_add_inline_style( 'acf-input', $css );
}
add_action( 'acf/input/admin_enqueue_scripts', 'portfolio_theme_icon_picker_styles' );
