<?php
/**
 * Component facts-a: label and value pairs (client, year, role…) separated
 * by 1px lines. A value can link out. Pairs without a value are dropped; with
 * none left, nothing renders.
 *
 * Args: items (each: label, value, url), class.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

$portfolio_facts = array_filter(
	$args['items'] ?? array(),
	static fn( $item ) => ! empty( $item['label'] ) && ! empty( $item['value'] )
);

if ( ! $portfolio_facts ) {
	return;
}
?>
<dl class="<?php echo esc_attr( trim( 'facts-a ' . ( $args['class'] ?? '' ) ) ); ?>">
	<?php foreach ( $portfolio_facts as $portfolio_fact ) : ?>
		<div class="facts-a__item">
			<dt class="facts-a__label"><?php echo esc_html( $portfolio_fact['label'] ); ?></dt>
			<dd class="facts-a__value">
				<?php if ( ! empty( $portfolio_fact['url'] ) ) : ?>
					<a href="<?php echo esc_url( $portfolio_fact['url'] ); ?>"<?php echo portfolio_theme_link_attrs( $portfolio_fact['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper. ?>><?php echo esc_html( $portfolio_fact['value'] ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $portfolio_fact['value'] ); ?>
				<?php endif; ?>
			</dd>
		</div>
	<?php endforeach; ?>
</dl>
