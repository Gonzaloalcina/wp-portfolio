<?php
/**
 * Component pagination-a: previous and next links with "Page x of y"
 * between them. Nothing renders with a single page.
 *
 * Args: prev (title, url), next (title, url), current, total, label
 * (accessible name of the nav), class.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

$portfolio_total = (int) ( $args['total'] ?? 0 );
if ( $portfolio_total < 2 ) {
	return;
}

$portfolio_prev = $args['prev'] ?? array();
$portfolio_next = $args['next'] ?? array();
?>
<nav class="<?php echo esc_attr( trim( 'pagination-a ' . ( $args['class'] ?? '' ) ) ); ?>" aria-label="<?php echo esc_attr( $args['label'] ?? __( 'Pagination', 'portfolio-theme' ) ); ?>">
	<?php if ( ! empty( $portfolio_prev['url'] ) ) : ?>
		<a class="pagination-a__prev" href="<?php echo esc_url( $portfolio_prev['url'] ); ?>" rel="prev"><?php echo esc_html( $portfolio_prev['title'] ); ?></a>
	<?php endif; ?>

	<p class="pagination-a__status">
		<?php
		printf(
			/* translators: 1: current page, 2: number of pages. */
			esc_html__( 'Page %1$d of %2$d', 'portfolio-theme' ),
			(int) ( $args['current'] ?? 1 ),
			(int) $portfolio_total
		);
		?>
	</p>

	<?php if ( ! empty( $portfolio_next['url'] ) ) : ?>
		<a class="pagination-a__next" href="<?php echo esc_url( $portfolio_next['url'] ); ?>" rel="next"><?php echo esc_html( $portfolio_next['title'] ); ?></a>
	<?php endif; ?>
</nav>
