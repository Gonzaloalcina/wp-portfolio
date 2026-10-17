<?php
/**
 * Component section-a: an H2, then any of one paragraph, a list-a, an
 * actions-a and a closing link. A section with nothing but its title does
 * not render.
 *
 * Args: id (used for the heading), title, text, items (see list-a),
 * actions (see actions-a), more (title, url).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

$portfolio_actions = $args['actions'] ?? array();
$portfolio_more    = $args['more'] ?? array();

if ( empty( $args['title'] ) || ( empty( $args['text'] ) && empty( $args['items'] ) && ! $portfolio_actions ) ) {
	return;
}

$portfolio_heading_id = sanitize_html_class( $args['id'] ?? sanitize_title( $args['title'] ) );
?>
<section class="section-a" aria-labelledby="<?php echo esc_attr( $portfolio_heading_id ); ?>">
	<h2 class="section-a__title" id="<?php echo esc_attr( $portfolio_heading_id ); ?>"><?php echo esc_html( $args['title'] ); ?></h2>

	<?php if ( ! empty( $args['text'] ) ) : ?>
		<p class="section-a__text"><?php echo esc_html( $args['text'] ); ?></p>
	<?php endif; ?>

	<?php
	portfolio_theme_component(
		'list-a',
		array(
			'items' => $args['items'] ?? array(),
			'class' => 'section-a__list',
		)
	);

	if ( $portfolio_actions ) {
		portfolio_theme_component( 'actions-a', $portfolio_actions + array( 'class' => 'section-a__actions' ) );
	}
	?>

	<?php if ( ! empty( $portfolio_more['title'] ) ) : ?>
		<p class="section-a__more"><a href="<?php echo esc_url( $portfolio_more['url'] ); ?>"><?php echo esc_html( $portfolio_more['title'] ); ?></a></p>
	<?php endif; ?>
</section>
