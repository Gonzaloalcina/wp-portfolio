<?php
/**
 * Component row-a: a title (linked when it has a URL), optional meta and an
 * optional one-line text.
 *
 * Args: title, url, rel, meta, datetime (renders meta as <time>), text,
 * data (data-* attributes on the link, see portfolio_theme_data_attrs()).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['title'] ) ) {
	return;
}
?>
<div class="row-a">
	<?php if ( ! empty( $args['url'] ) ) : ?>
		<a class="row-a__title" href="<?php echo esc_url( $args['url'] ); ?>"<?php echo portfolio_theme_link_attrs( $args['url'], $args['rel'] ?? '' ) . portfolio_theme_data_attrs( $args['data'] ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helpers. ?>><?php echo esc_html( $args['title'] ); ?></a>
	<?php else : ?>
		<span class="row-a__title"><?php echo esc_html( $args['title'] ); ?></span>
	<?php endif; ?>

	<?php if ( ! empty( $args['meta'] ) ) : ?>
		<?php if ( ! empty( $args['datetime'] ) ) : ?>
			<time class="row-a__meta" datetime="<?php echo esc_attr( $args['datetime'] ); ?>"><?php echo esc_html( $args['meta'] ); ?></time>
		<?php else : ?>
			<span class="row-a__meta"><?php echo esc_html( $args['meta'] ); ?></span>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( ! empty( $args['text'] ) ) : ?>
		<p class="row-a__text"><?php echo esc_html( $args['text'] ); ?></p>
	<?php endif; ?>
</div>
