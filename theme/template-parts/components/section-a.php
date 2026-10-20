<?php
/**
 * Component section-a: an H2, then any of one paragraph, content components,
 * an actions-a and a closing link. A section with nothing but its title does
 * not render.
 *
 * Args: id (used for the heading), title, text (a blank line starts a new
 * paragraph), content, actions (see actions-a), more (title, url), variant
 * ("second": set apart by a full-width line, for the closing Contact).
 *
 * content: a list of components to render in order, each
 * array( 'name' => 'list-a', 'args' => array( 'items' => … ), 'title' => '' ).
 * An entry with no items is skipped; title, if any, is an H3 above it.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

$portfolio_content = array_filter(
	$args['content'] ?? array(),
	static fn( $entry ) => ! empty( $entry['name'] ) && ! empty( $entry['args']['items'] )
);
$portfolio_actions = $args['actions'] ?? array();
if ( empty( $portfolio_actions['button']['title'] ) && empty( $portfolio_actions['links'] ) ) {
	$portfolio_actions = array();
}
$portfolio_more = $args['more'] ?? array();

if ( empty( $args['title'] ) || ( empty( $args['text'] ) && ! $portfolio_content && ! $portfolio_actions ) ) {
	return;
}

$portfolio_heading_id = sanitize_html_class( $args['id'] ?? sanitize_title( $args['title'] ) );
?>
<section class="section-a<?php echo 'second' === ( $args['variant'] ?? '' ) ? ' section-a--second' : ''; ?>" aria-labelledby="<?php echo esc_attr( $portfolio_heading_id ); ?>">
	<h2 class="section-a__title" id="<?php echo esc_attr( $portfolio_heading_id ); ?>"><?php echo esc_html( $args['title'] ); ?></h2>

	<?php if ( ! empty( $args['text'] ) ) : ?>
		<?php foreach ( preg_split( '/\R\s*\R/', trim( $args['text'] ) ) as $portfolio_paragraph ) : ?>
			<p class="section-a__text"><?php echo esc_html( $portfolio_paragraph ); ?></p>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php foreach ( $portfolio_content as $portfolio_entry ) : ?>
		<?php if ( ! empty( $portfolio_entry['title'] ) ) : ?>
			<h3 class="section-a__subtitle"><?php echo esc_html( $portfolio_entry['title'] ); ?></h3>
		<?php endif; ?>
		<?php
		portfolio_theme_component(
			$portfolio_entry['name'],
			array_merge(
				$portfolio_entry['args'],
				array( 'class' => trim( 'section-a__content ' . ( $portfolio_entry['args']['class'] ?? '' ) ) )
			)
		);
		?>
	<?php endforeach; ?>

	<?php
	if ( $portfolio_actions ) {
		portfolio_theme_component( 'actions-a', $portfolio_actions + array( 'class' => 'section-a__actions' ) );
	}
	?>

	<?php if ( ! empty( $portfolio_more['title'] ) ) : ?>
		<p class="section-a__more"><a href="<?php echo esc_url( $portfolio_more['url'] ); ?>"<?php echo portfolio_theme_link_attrs( $portfolio_more['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper. ?>><?php echo esc_html( $portfolio_more['title'] ); ?></a></p>
	<?php endif; ?>
</section>
