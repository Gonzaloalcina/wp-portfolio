<?php
/**
 * Component form-a: field-a items, then a submit button-a and an optional
 * note under it.
 *
 * Static for now: the contact form's logic (REST endpoint, Turnstile,
 * honeypot, rate limiting) belongs to the portfolio-core plugin. Without an
 * action the button is disabled, so the form cannot be sent anywhere.
 *
 * Args: label (accessible name), fields (each the args of a field-a),
 * button (label), action (URL; empty: not connected), note, class.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['fields'] ) || empty( $args['button'] ) ) {
	return;
}

$portfolio_action = $args['action'] ?? '';
?>
<form class="<?php echo esc_attr( trim( 'form-a ' . ( $args['class'] ?? '' ) ) ); ?>"<?php echo $portfolio_action ? ' action="' . esc_url( $portfolio_action ) . '" method="post"' : ''; ?><?php echo empty( $args['label'] ) ? '' : ' aria-label="' . esc_attr( $args['label'] ) . '"'; ?>>
	<?php foreach ( $args['fields'] as $portfolio_field ) : ?>
		<?php portfolio_theme_component( 'field-a', $portfolio_field + array( 'class' => 'form-a__field' ) ); ?>
	<?php endforeach; ?>

	<button class="button-a form-a__submit" type="submit"<?php echo $portfolio_action ? '' : ' disabled'; ?>><?php echo esc_html( $args['button'] ); ?></button>

	<?php if ( ! empty( $args['note'] ) ) : ?>
		<p class="form-a__note"><?php echo esc_html( $args['note'] ); ?></p>
	<?php endif; ?>
</form>
