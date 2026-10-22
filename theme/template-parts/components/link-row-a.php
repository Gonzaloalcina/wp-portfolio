<?php
/**
 * Component link-row-a: quiet links on one line.
 *
 * Args: links (each: title, url, rel, data: data-* attributes, see
 * portfolio_theme_data_attrs(), icon: a brand icon name, see
 * portfolio_theme_get_brand_icon(), shown instead of the title, which becomes
 * the aria-label), class (extra classes).
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
		<?php $portfolio_icon = empty( $portfolio_link['icon'] ) ? '' : portfolio_theme_get_brand_icon( $portfolio_link['icon'] ); ?>
		<li class="link-row-a__item">
			<?php if ( $portfolio_icon ) : ?>
				<a class="link-row-a__icon" href="<?php echo esc_url( $portfolio_link['url'] ); ?>" aria-label="<?php echo esc_attr( $portfolio_link['title'] ); ?>"<?php echo portfolio_theme_link_attrs( $portfolio_link['url'], $portfolio_link['rel'] ?? '' ) . portfolio_theme_data_attrs( $portfolio_link['data'] ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helpers. ?>><?php echo wp_kses( $portfolio_icon, portfolio_theme_icon_kses() ); ?></a>
			<?php else : ?>
				<a href="<?php echo esc_url( $portfolio_link['url'] ); ?>"<?php echo portfolio_theme_link_attrs( $portfolio_link['url'], $portfolio_link['rel'] ?? '' ) . portfolio_theme_data_attrs( $portfolio_link['data'] ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helpers. ?>><?php echo esc_html( $portfolio_link['title'] ); ?></a>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
