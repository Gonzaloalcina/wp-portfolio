<?php
/**
 * Component quote-list-a: quote-a items separated by 1px lines.
 *
 * Args: items (each one the args of a quote-a), class (extra classes).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['items'] ) ) {
	return;
}
?>
<ul class="<?php echo esc_attr( trim( 'quote-list-a ' . ( $args['class'] ?? '' ) ) ); ?>" role="list">
	<?php foreach ( $args['items'] as $portfolio_item ) : ?>
		<li class="quote-list-a__item"><?php portfolio_theme_component( 'quote-a', $portfolio_item ); ?></li>
	<?php endforeach; ?>
</ul>
