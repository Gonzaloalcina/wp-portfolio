<?php
/**
 * Component card-list-a: card-a items in a grid, one column on phones and
 * three from 48rem.
 *
 * Args: items (each one the args of a card-a), class (extra classes, e.g. a
 * BEM mix with the parent component).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['items'] ) ) {
	return;
}

// The image is one third of the wide column, less the gaps; full width below 48rem.
$portfolio_gutters = 2 * portfolio_theme_layout_rem( 'layout.gutter', 1.5 );
$portfolio_sizes   = sprintf(
	'(min-width: 48rem) calc((min(100vw - %1$srem, %2$srem) - %3$srem) / 3), calc(100vw - %1$srem)',
	$portfolio_gutters,
	portfolio_theme_layout_rem( 'layout.wide', 64 ),
	$portfolio_gutters
);
?>
<ul class="<?php echo esc_attr( trim( 'card-list-a ' . ( $args['class'] ?? '' ) ) ); ?>" role="list">
	<?php foreach ( $args['items'] as $portfolio_item ) : ?>
		<li class="card-list-a__item"><?php portfolio_theme_component( 'card-a', $portfolio_item + array( 'sizes' => $portfolio_sizes ) ); ?></li>
	<?php endforeach; ?>
</ul>
