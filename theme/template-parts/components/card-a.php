<?php
/**
 * Component card-a: an image, a title (linked when it has a URL), optional
 * meta and an optional short text. The whole card is the link's hit area;
 * the image is decorative (alt=""), since the title names it.
 *
 * Args: title, url, rel, meta, text, image_id (attachment ID; no image, no
 * frame), sizes (sizes attribute for the image), data (data-* attributes on
 * the link, see portfolio_theme_data_attrs()).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['title'] ) ) {
	return;
}
?>
<div class="card-a">
	<?php
	if ( ! empty( $args['image_id'] ) ) {
		echo wp_get_attachment_image(
			(int) $args['image_id'],
			'medium_large',
			false,
			array_filter(
				array(
					'class'   => 'card-a__image',
					'alt'     => '',
					'sizes'   => $args['sizes'] ?? '',
					'loading' => 'lazy',
				),
				static fn( $value, $key ) => 'alt' === $key || '' !== $value,
				ARRAY_FILTER_USE_BOTH
			)
		);
	}
	?>

	<?php if ( ! empty( $args['url'] ) ) : ?>
		<a class="card-a__title" href="<?php echo esc_url( $args['url'] ); ?>"<?php echo portfolio_theme_link_attrs( $args['url'], $args['rel'] ?? '' ) . portfolio_theme_data_attrs( $args['data'] ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helpers. ?>><?php echo esc_html( $args['title'] ); ?></a>
	<?php else : ?>
		<span class="card-a__title"><?php echo esc_html( $args['title'] ); ?></span>
	<?php endif; ?>

	<?php if ( ! empty( $args['meta'] ) ) : ?>
		<span class="card-a__meta"><?php echo esc_html( $args['meta'] ); ?></span>
	<?php endif; ?>

	<?php if ( ! empty( $args['text'] ) ) : ?>
		<p class="card-a__text"><?php echo esc_html( $args['text'] ); ?></p>
	<?php endif; ?>
</div>
