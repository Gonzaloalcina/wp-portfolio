<?php
/**
 * Component prose-a: long text written in the editor (the_content), on the
 * text measure. Its children are styled with element selectors, since the
 * editor does not add classes.
 *
 * Args: content (HTML, already filtered by the_content), class.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['content'] ) || '' === trim( wp_strip_all_tags( $args['content'] ) ) ) {
	return;
}
?>
<div class="<?php echo esc_attr( trim( 'prose-a ' . ( $args['class'] ?? '' ) ) ); ?>">
	<?php echo wp_kses_post( $args['content'] ); ?>
</div>
