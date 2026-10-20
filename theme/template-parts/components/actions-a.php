<?php
/**
 * Component actions-a: one primary button-a, then a link-row-a. Either can
 * be missing; with neither, nothing renders.
 *
 * Args: button (title, url), links (see link-row-a), class (extra classes).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

$portfolio_button = $args['button'] ?? array();
$portfolio_links  = $args['links'] ?? array();

if ( empty( $portfolio_button['title'] ) && ! $portfolio_links ) {
	return;
}
?>
<div class="<?php echo esc_attr( trim( 'actions-a ' . ( $args['class'] ?? '' ) ) ); ?>">
	<?php if ( ! empty( $portfolio_button['title'] ) ) : ?>
		<a class="button-a" href="<?php echo esc_url( $portfolio_button['url'] ); ?>"<?php echo portfolio_theme_link_attrs( $portfolio_button['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper. ?>><?php echo esc_html( $portfolio_button['title'] ); ?></a>
	<?php endif; ?>
	<?php portfolio_theme_component( 'link-row-a', array( 'links' => $portfolio_links ) ); ?>
</div>
