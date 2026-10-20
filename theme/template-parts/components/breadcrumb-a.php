<?php
/**
 * Component breadcrumb-a: the trail to the current page, above its H1. The
 * last item is the current page, not linked.
 *
 * Args: items (each: title, url), class (extra classes).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( count( $args['items'] ?? array() ) < 2 ) {
	return;
}

$portfolio_last = count( $args['items'] ) - 1;
?>
<nav class="<?php echo esc_attr( trim( 'breadcrumb-a ' . ( $args['class'] ?? '' ) ) ); ?>" aria-label="<?php esc_attr_e( 'Breadcrumb', 'portfolio-theme' ); ?>">
	<ol class="breadcrumb-a__list" role="list">
		<?php foreach ( $args['items'] as $portfolio_index => $portfolio_crumb ) : ?>
			<li class="breadcrumb-a__item">
				<?php if ( $portfolio_index === $portfolio_last || empty( $portfolio_crumb['url'] ) ) : ?>
					<span aria-current="page"><?php echo esc_html( $portfolio_crumb['title'] ); ?></span>
				<?php else : ?>
					<a href="<?php echo esc_url( $portfolio_crumb['url'] ); ?>"><?php echo esc_html( $portfolio_crumb['title'] ); ?></a>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
