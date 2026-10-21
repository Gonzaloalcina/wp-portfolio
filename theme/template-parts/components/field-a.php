<?php
/**
 * Component field-a: a form field. Label, optional hint, then the control
 * (input, textarea or select), full width, 1px line.
 *
 * Args: name, label, type (text, email, textarea, select…), required,
 * autocomplete, hint, class. Select only: options (value => label; an ''
 * value first asks for a choice, so a required select is never sent with a
 * choice nobody made) and selected (a value of options).
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['name'] ) || empty( $args['label'] ) ) {
	return;
}

$portfolio_type = $args['type'] ?? 'text';
if ( 'select' === $portfolio_type && empty( $args['options'] ) ) {
	return;
}

$portfolio_id       = 'field-a-' . sanitize_html_class( $args['name'] );
$portfolio_required = ! empty( $args['required'] );
$portfolio_hint_id  = empty( $args['hint'] ) ? '' : $portfolio_id . '-hint';
$portfolio_attrs    = sprintf(
	'class="field-a__control" id="%1$s" name="%2$s"%3$s%4$s%5$s',
	esc_attr( $portfolio_id ),
	esc_attr( $args['name'] ),
	$portfolio_required ? ' required' : '',
	empty( $args['autocomplete'] ) ? '' : ' autocomplete="' . esc_attr( $args['autocomplete'] ) . '"',
	$portfolio_hint_id ? ' aria-describedby="' . esc_attr( $portfolio_hint_id ) . '"' : ''
);
?>
<div class="<?php echo esc_attr( trim( 'field-a ' . ( $args['class'] ?? '' ) ) ); ?>">
	<label class="field-a__label" for="<?php echo esc_attr( $portfolio_id ); ?>">
		<?php echo esc_html( $args['label'] ); ?>
		<?php if ( ! $portfolio_required ) : ?>
			<span class="field-a__optional"><?php esc_html_e( '(optional)', 'portfolio-theme' ); ?></span>
		<?php endif; ?>
	</label>

	<?php if ( $portfolio_hint_id ) : ?>
		<p class="field-a__hint" id="<?php echo esc_attr( $portfolio_hint_id ); ?>"><?php echo esc_html( $args['hint'] ); ?></p>
	<?php endif; ?>

	<?php if ( 'textarea' === $portfolio_type ) : ?>
		<textarea <?php echo $portfolio_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?> rows="6"></textarea>
	<?php elseif ( 'select' === $portfolio_type ) : ?>
		<span class="field-a__select">
			<select <?php echo $portfolio_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>>
				<?php foreach ( $args['options'] as $portfolio_value => $portfolio_label ) : ?>
					<option value="<?php echo esc_attr( (string) $portfolio_value ); ?>"<?php selected( (string) ( $args['selected'] ?? '' ), (string) $portfolio_value ); ?>><?php echo esc_html( $portfolio_label ); ?></option>
				<?php endforeach; ?>
			</select>
		</span>
	<?php else : ?>
		<input type="<?php echo esc_attr( $portfolio_type ); ?>" <?php echo $portfolio_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above. ?>>
	<?php endif; ?>
</div>
