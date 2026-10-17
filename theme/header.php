<?php
/**
 * Site header. Opens the site block, closed in footer.php.
 *
 * Args (get_header( null, $args )): variant, a numeric modifier of the site
 * block. "second" is the wide layout (layout.wide); the default is the text
 * measure (layout.text).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

$portfolio_site_class = 'site';
if ( ! empty( $args['variant'] ) ) {
	$portfolio_site_class .= ' site--' . sanitize_html_class( $args['variant'] );
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'portfolio-theme' ); ?></a>
<div class="<?php echo esc_attr( $portfolio_site_class ); ?>">
<header class="site__header">
	<a class="site__title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
	<?php portfolio_theme_toggle_button(); ?>
</header>
<main id="content" class="site__main">
