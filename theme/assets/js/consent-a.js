/**
 * consent-a: stores the visitor's answer through portfolio-core's
 * window.portfolioConsent, and reopens the banner from any
 * [data-consent-open] link ("Cookie settings" in the footer).
 *
 * The banner shows by CSS while <html data-consent="pending">, so this
 * module only handles clicks and focus: opened from a link, focus moves to
 * the banner and returns to the link after the answer.
 */

const consent = window.portfolioConsent;
const banner = document.querySelector( '[data-consent-a]' );

if ( consent && banner ) {
	let opener = null;

	banner.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '[data-consent-choice]' );
		if ( ! button ) {
			return;
		}

		consent.set( button.dataset.consentChoice === 'granted' );
		opener?.focus();
		opener = null;
	} );

	for ( const link of document.querySelectorAll( '[data-consent-open]' ) ) {
		link.addEventListener( 'click', ( event ) => {
			event.preventDefault();
			opener = link;
			consent.open();
			banner.querySelector( 'button' ).focus();
		} );
	}
}
