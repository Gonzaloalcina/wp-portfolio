<?php
/**
 * Component quote-a: one testimonial. The quote, then who said it: name
 * (linked when there is a URL, e.g. their LinkedIn profile, as proof) and
 * role.
 *
 * Args: quote, name, role, url.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['quote'] ) || empty( $args['name'] ) ) {
	return;
}
?>
<figure class="quote-a">
	<blockquote class="quote-a__quote">
		<p class="quote-a__text"><?php echo esc_html( $args['quote'] ); ?></p>
	</blockquote>
	<figcaption class="quote-a__cite">
		<?php if ( ! empty( $args['url'] ) ) : ?>
			<a class="quote-a__name" href="<?php echo esc_url( $args['url'] ); ?>"<?php echo portfolio_theme_link_attrs( $args['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper. ?>><?php echo esc_html( $args['name'] ); ?></a>
		<?php else : ?>
			<span class="quote-a__name"><?php echo esc_html( $args['name'] ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $args['role'] ) ) : ?>
			<span class="quote-a__role"><?php echo esc_html( $args['role'] ); ?></span>
		<?php endif; ?>
	</figcaption>
</figure>
