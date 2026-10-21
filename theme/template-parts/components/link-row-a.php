<?php
/**
 * Component link-row-a: quiet links on one line.
 *
 * Args: links (each: title, url, rel, data: data-* attributes, see
 * portfolio_theme_data_attrs()), class (extra classes).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['links'] ) ) {
	return;
}
?>
<ul class="<?php echo esc_attr( trim( 'link-row-a ' . ( $args['class'] ?? '' ) ) ); ?>" role="list">
	<?php foreach ( $args['links'] as $portfolio_link ) : ?>
		<li class="link-row-a__item"><a href="<?php echo esc_url( $portfolio_link['url'] ); ?>"<?php echo portfolio_theme_link_attrs( $portfolio_link['url'], $portfolio_link['rel'] ?? '' ) . portfolio_theme_data_attrs( $portfolio_link['data'] ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helpers. ?>><?php echo esc_html( $portfolio_link['title'] ); ?></a></li>
	<?php endforeach; ?>
</ul>
