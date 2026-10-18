<?php
/**
 * Component nav-a: a menu location as a list of links. On narrow screens a
 * "Menu" button shows and hides the list (assets/js/nav-a.js); the button is
 * rendered hidden and shown by the module, so without JavaScript the links
 * are simply visible.
 *
 * Args: location (menu location), label (accessible name of the nav),
 * class (extra classes, e.g. a BEM mix).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

$portfolio_location = $args['location'] ?? 'primary';

$portfolio_menu = wp_nav_menu(
	array(
		'theme_location'      => $portfolio_location,
		'container'           => false,
		'menu_id'             => 'nav-a-' . $portfolio_location,
		'menu_class'          => 'nav-a__list',
		'items_wrap'          => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
		'depth'               => 1,
		'fallback_cb'         => false,
		'echo'                => false,
		'portfolio_component' => 'nav-a',
	)
);

if ( ! $portfolio_menu ) {
	return;
}
?>
<nav class="<?php echo esc_attr( trim( 'nav-a ' . ( $args['class'] ?? '' ) ) ); ?>" aria-label="<?php echo esc_attr( $args['label'] ?? __( 'Main', 'portfolio-theme' ) ); ?>" data-nav>
	<button type="button" class="nav-a__toggle" aria-expanded="false" aria-controls="<?php echo esc_attr( 'nav-a-' . $portfolio_location ); ?>" data-nav-toggle hidden><?php esc_html_e( 'Menu', 'portfolio-theme' ); ?></button>
	<?php echo $portfolio_menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_nav_menu() output is escaped by core. ?>
</nav>
