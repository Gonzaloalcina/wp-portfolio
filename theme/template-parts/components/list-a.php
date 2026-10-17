<?php
/**
 * Component list-a: a list of row-a items separated by 1px lines.
 *
 * Args: items (each one the args of a row-a), class (extra classes, e.g. a
 * BEM mix with the parent component).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['items'] ) ) {
	return;
}
?>
<ul class="<?php echo esc_attr( trim( 'list-a ' . ( $args['class'] ?? '' ) ) ); ?>" role="list">
	<?php foreach ( $args['items'] as $portfolio_item ) : ?>
		<li class="list-a__item"><?php portfolio_theme_component( 'row-a', $portfolio_item ); ?></li>
	<?php endforeach; ?>
</ul>
