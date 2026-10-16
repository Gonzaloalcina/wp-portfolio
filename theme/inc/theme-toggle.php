<?php
/**
 * Light/dark theme toggle: no-flash inline script, toggle module and button.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Applies the stored theme before first paint, so there is no flash of the
 * wrong theme. Must stay in sync with the key and states in theme-toggle.js.
 */
function portfolio_theme_print_theme_script() {
	wp_print_inline_script_tag(
		"(function(){try{var t=localStorage.getItem('portfolio-theme');if(t==='light'||t==='dark'){document.documentElement.dataset.theme=t;}}catch(e){}})();"
	);
}
add_action( 'wp_head', 'portfolio_theme_print_theme_script', 0 );

/**
 * Enqueues the toggle as a native ES module (no build step).
 */
function portfolio_theme_enqueue_theme_toggle() {
	$file = 'assets/js/theme-toggle.js';

	wp_enqueue_script_module(
		'portfolio-theme/theme-toggle',
		get_theme_file_uri( $file ),
		array(),
		(string) filemtime( get_theme_file_path( $file ) )
	);
}
add_action( 'wp_enqueue_scripts', 'portfolio_theme_enqueue_theme_toggle' );

/**
 * Prints the toggle button. It is hidden until the module runs, so visitors
 * without JavaScript never see a button that does nothing.
 */
function portfolio_theme_toggle_button() {
	$labels = array(
		'light' => __( 'Light', 'portfolio-theme' ),
		'dark'  => __( 'Dark', 'portfolio-theme' ),
	);
	?>
	<button type="button" class="theme-toggle" data-theme-toggle hidden>
		<?php esc_html_e( 'Theme:', 'portfolio-theme' ); ?>
		<span
			data-theme-toggle-state
			aria-live="polite"
			data-label-light="<?php echo esc_attr( $labels['light'] ); ?>"
			data-label-dark="<?php echo esc_attr( $labels['dark'] ); ?>"
		><?php echo esc_html( $labels['light'] ); ?></span>
	</button>
	<?php
}
