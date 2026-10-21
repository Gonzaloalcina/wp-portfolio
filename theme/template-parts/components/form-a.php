<?php
/**
 * Component form-a: field-a items, then a submit button-a and an optional
 * note under it.
 *
 * Connected when it has an action (see portfolio_theme_form_connection()):
 * the form-a module sends it to that URL as JSON, with a Turnstile token,
 * and says how it went in a status line. The button is disabled until the
 * module runs, because the form cannot pass the spam check without
 * JavaScript. Without an action the button stays disabled: a placeholder.
 *
 * Args: label (accessible name), fields (each the args of a field-a),
 * button (label), note, more (title, url: a quiet link under the note, such
 * as the privacy page), class, data (data-* attributes on the form, see
 * portfolio_theme_data_attrs()). Connected only: action (URL), challenge
 * (Turnstile site key), hidden (name => value), trap (honeypot field name),
 * messages (see portfolio_theme_form_messages()).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['fields'] ) || empty( $args['button'] ) ) {
	return;
}

$portfolio_action = $args['action'] ?? '';
$portfolio_attrs  = $portfolio_action
	? ' action="' . esc_url( $portfolio_action ) . '" method="post" data-form-a data-messages="' . esc_attr( (string) wp_json_encode( $args['messages'] ?? array() ) ) . '"'
	: '';

if ( $portfolio_action ) {
	portfolio_theme_enqueue_form();
}
?>
<form class="<?php echo esc_attr( trim( 'form-a ' . ( $args['class'] ?? '' ) ) ); ?>"<?php echo $portfolio_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?><?php echo empty( $args['label'] ) ? '' : ' aria-label="' . esc_attr( $args['label'] ) . '"'; ?><?php echo portfolio_theme_data_attrs( $args['data'] ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper. ?>>
	<?php foreach ( $args['fields'] as $portfolio_field ) : ?>
		<?php portfolio_theme_component( 'field-a', $portfolio_field + array( 'class' => 'form-a__field' ) ); ?>
	<?php endforeach; ?>

	<?php if ( $portfolio_action ) : ?>
		<?php foreach ( (array) ( $args['hidden'] ?? array() ) as $portfolio_name => $portfolio_value ) : ?>
			<input type="hidden" name="<?php echo esc_attr( (string) $portfolio_name ); ?>" value="<?php echo esc_attr( (string) $portfolio_value ); ?>">
		<?php endforeach; ?>

		<?php if ( ! empty( $args['trap'] ) ) : ?>
			<div class="form-a__trap" aria-hidden="true">
				<label>
					<?php esc_html_e( 'Leave this field empty', 'portfolio-theme' ); ?>
					<input type="text" name="<?php echo esc_attr( $args['trap'] ); ?>" tabindex="-1" autocomplete="off">
				</label>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $args['challenge'] ) ) : ?>
			<div class="form-a__challenge" data-form-a-challenge="<?php echo esc_attr( $args['challenge'] ); ?>"></div>
		<?php endif; ?>
	<?php endif; ?>

	<button class="button-a form-a__submit" type="submit" disabled><?php echo esc_html( $args['button'] ); ?></button>

	<?php if ( $portfolio_action ) : ?>
		<p class="form-a__status" role="status" data-form-a-status></p>
		<noscript><p class="form-a__note"><?php esc_html_e( 'This form needs JavaScript to send.', 'portfolio-theme' ); ?></p></noscript>
	<?php endif; ?>

	<?php if ( ! empty( $args['note'] ) ) : ?>
		<p class="form-a__note"><?php echo esc_html( $args['note'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $args['more']['title'] ) ) : ?>
		<p class="form-a__more"><a href="<?php echo esc_url( $args['more']['url'] ); ?>"<?php echo portfolio_theme_link_attrs( $args['more']['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped in the helper. ?>><?php echo esc_html( $args['more']['title'] ); ?></a></p>
	<?php endif; ?>
</form>
