<?php
/**
 * Component hero-a: the page's H1, a short intro, an actions-a and a square
 * image with a 1px line.
 *
 * Args: title, intro, actions (see actions-a), image_id, image_alt,
 * image_frame (true: draw the empty frame when there is no image; for the
 * preview only).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['title'] ) ) {
	return;
}

$portfolio_image_id = (int) ( $args['image_id'] ?? 0 );
?>
<section class="hero-a" aria-labelledby="hero-a-title">
	<div class="hero-a__text">
		<h1 class="hero-a__title" id="hero-a-title"><?php echo esc_html( $args['title'] ); ?></h1>

		<?php if ( ! empty( $args['intro'] ) ) : ?>
			<p class="hero-a__intro"><?php echo esc_html( $args['intro'] ); ?></p>
		<?php endif; ?>

		<?php
		if ( ! empty( $args['actions'] ) ) {
			portfolio_theme_component( 'actions-a', $args['actions'] + array( 'class' => 'hero-a__actions' ) );
		}
		?>
	</div>

	<?php if ( $portfolio_image_id || ! empty( $args['image_frame'] ) ) : ?>
		<div class="hero-a__image">
			<?php
			if ( $portfolio_image_id ) {
				echo wp_get_attachment_image(
					$portfolio_image_id,
					'medium',
					false,
					array(
						'alt'     => $args['image_alt'] ?? '',
						'sizes'   => '8rem',
						'loading' => 'eager',
					)
				);
			}
			?>
		</div>
	<?php endif; ?>
</section>
