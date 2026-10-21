<?php
/**
 * Component consent-a: the cookie banner. A short text with a link to the
 * policy, then Accept and Reject as two equal buttons (the AEPD asks that
 * rejecting be as easy as accepting). Fixed to the bottom of the screen,
 * without covering or blocking the page.
 *
 * Hidden unless <html data-consent="pending"> (set by portfolio-core's head
 * script before first paint); the consent-a module stores the answer.
 *
 * Args: label (accessible name), text, more (title, url), accept, reject.
 *
 * @package PortfolioTheme
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['text'] ) || empty( $args['accept'] ) || empty( $args['reject'] ) ) {
	return;
}
?>
<section class="consent-a" aria-label="<?php echo esc_attr( $args['label'] ?? '' ); ?>" data-consent-a>
	<p class="consent-a__text">
		<?php echo esc_html( $args['text'] ); ?>
		<?php if ( ! empty( $args['more']['title'] ) ) : ?>
			<a href="<?php echo esc_url( $args['more']['url'] ); ?>"><?php echo esc_html( $args['more']['title'] ); ?></a>
		<?php endif; ?>
	</p>
	<div class="consent-a__actions">
		<button class="button-a consent-a__button" type="button" data-consent-choice="granted"><?php echo esc_html( $args['accept'] ); ?></button>
		<button class="button-a consent-a__button" type="button" data-consent-choice="denied"><?php echo esc_html( $args['reject'] ); ?></button>
	</div>
</section>
