/**
 * dataLayer events, read from the theme's data-* hooks
 * (docs/decisions/08-page-templates.md and 13-contact-form.md):
 *
 * - cta_click: a link or button with data-cta-location (and data-service
 *   on service CTAs).
 * - generate_lead: a form sent (the form-a:sent event), with its
 *   data-form-id, data-cta-location and the service chosen.
 *
 * Pushed only with analytics accepted (basic mode): before that, not even
 * the dataLayer keeps a record of what the visitor does. Every event
 * carries all its keys, empty when unknown, so a value never leaks from one
 * event into the next in GTM's merged data model. Services are reported by
 * their default-language slug.
 */

const consent = window.portfolioConsent;

/**
 * Pushes an event when analytics is accepted.
 *
 * @param {Record<string, string>} data Event and parameters.
 */
function push( data ) {
	if ( consent?.granted() ) {
		window.dataLayer.push( data );
	}
}

/**
 * @param {string|undefined} slug Service slug in the page's language.
 * @return {string}
 */
function service( slug ) {
	return consent?.service( slug ?? '' ) ?? slug ?? '';
}

document.addEventListener( 'click', ( event ) => {
	const element = event.target.closest?.( '[data-cta-location]' );
	// A form carries data-cta-location too; its leads are generate_lead.
	if ( ! element || ! [ 'A', 'BUTTON' ].includes( element.tagName ) ) {
		return;
	}

	push( {
		event: 'cta_click',
		cta_location: element.dataset.ctaLocation,
		service: service( element.dataset.service ),
		link_url: element.href ?? '',
	} );
} );

document.addEventListener( 'form-a:sent', ( event ) => {
	const form = event.target;

	push( {
		event: 'generate_lead',
		form_id: event.detail?.formId ?? form.dataset.formId ?? '',
		cta_location: form.dataset.ctaLocation ?? '',
		service: service( event.detail?.service ),
	} );
} );
