<?php
/**
 * Component title-a: the page's H1 for text pages (notes, cases, legal
 * pages), with an optional meta line (a date, a label) and an optional
 * intro. hero-a is the H1 for pages that lead to an action.
 *
 * Args: title, meta, datetime (renders meta as <time>), intro, class, variant
 * ("second": a 1px line under the header, to set the title and intro apart
 * from the text that follows).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['title'] ) ) {
	return;
}
?>
<header class="<?php echo esc_attr( trim( 'title-a ' . ( 'second' === ( $args['variant'] ?? '' ) ? 'title-a--second ' : '' ) . ( $args['class'] ?? '' ) ) ); ?>">
	<h1 class="title-a__title"><?php echo esc_html( $args['title'] ); ?></h1>

	<?php if ( ! empty( $args['meta'] ) ) : ?>
		<p class="title-a__meta">
			<?php if ( ! empty( $args['datetime'] ) ) : ?>
				<time datetime="<?php echo esc_attr( $args['datetime'] ); ?>"><?php echo esc_html( $args['meta'] ); ?></time>
			<?php else : ?>
				<?php echo esc_html( $args['meta'] ); ?>
			<?php endif; ?>
		</p>
	<?php endif; ?>

	<?php if ( ! empty( $args['intro'] ) ) : ?>
		<p class="title-a__intro"><?php echo esc_html( $args['intro'] ); ?></p>
	<?php endif; ?>
</header>
