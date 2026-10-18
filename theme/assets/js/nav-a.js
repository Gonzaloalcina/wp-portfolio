/**
 * nav-a disclosure: on narrow screens the "Menu" button shows and hides the
 * list. The button starts hidden, so without JavaScript the links stay
 * visible. data-ready tells the CSS the disclosure works; data-open, that the
 * list is shown.
 *
 * Closes on Escape (focus goes back to the button), on a click outside or on
 * the fade layer (the nav's ::before, so the click lands on the nav itself),
 * and when the screen becomes wide enough for the inline menu.
 */

const wide = window.matchMedia( '(min-width: 48rem)' );

for ( const nav of document.querySelectorAll( '[data-nav]' ) ) {
	const button = nav.querySelector( '[data-nav-toggle]' );
	if ( ! button ) {
		continue;
	}

	const setOpen = ( open ) => {
		nav.toggleAttribute( 'data-open', open );
		button.setAttribute( 'aria-expanded', String( open ) );
	};

	nav.toggleAttribute( 'data-ready', true );
	button.hidden = false;

	button.addEventListener( 'click', () => setOpen( ! nav.hasAttribute( 'data-open' ) ) );

	nav.addEventListener( 'keydown', ( event ) => {
		if ( 'Escape' === event.key && nav.hasAttribute( 'data-open' ) ) {
			setOpen( false );
			button.focus();
		}
	} );

	document.addEventListener( 'click', ( event ) => {
		if ( nav.hasAttribute( 'data-open' ) && ( event.target === nav || ! nav.contains( event.target ) ) ) {
			setOpen( false );
		}
	} );

	wide.addEventListener( 'change', () => setOpen( false ) );
}
