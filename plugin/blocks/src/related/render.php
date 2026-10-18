<?php
/**
 * Related block: the chosen service and project as a row list.
 *
 * Data comes from portfolio-core (inc/blocks.php), read at render time, so a
 * renamed service or a project whose consent changes is reflected at once.
 * Presentation reuses the theme's list-a and row-a components when the theme
 * provides them; with another theme it falls back to a plain list of links.
 *
 * @package PortfolioCore
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$portfolio_core_items = portfolio_core_related_items( $attributes );

// Nothing chosen, or nothing chosen is public any more.
if ( ! $portfolio_core_items ) {
	return;
}

$portfolio_core_label_id = wp_unique_id( 'related-a-label-' );
?>
<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'related-a' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by core. ?> aria-labelledby="<?php echo esc_attr( $portfolio_core_label_id ); ?>">
	<p class="related-a__label" id="<?php echo esc_attr( $portfolio_core_label_id ); ?>"><?php esc_html_e( 'Related', 'portfolio-core' ); ?></p>
	<?php if ( function_exists( 'portfolio_theme_component' ) ) : ?>
		<?php
		portfolio_theme_component(
			'list-a',
			array(
				'items' => $portfolio_core_items,
				'class' => 'related-a__list',
			)
		);
		?>
	<?php else : ?>
		<ul class="related-a__list">
			<?php foreach ( $portfolio_core_items as $portfolio_core_item ) : ?>
				<li><a href="<?php echo esc_url( $portfolio_core_item['url'] ); ?>"><?php echo esc_html( $portfolio_core_item['title'] ); ?></a> (<?php echo esc_html( $portfolio_core_item['meta'] ); ?>)</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</aside>
