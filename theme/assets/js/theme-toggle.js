/**
 * Light/dark theme toggle.
 *
 * The site follows the system scheme until the visitor clicks. Each click
 * switches to the other scheme. A choice that differs from the system is
 * stored in localStorage and set as data-theme on <html>; a choice that
 * matches the system is not stored, so the site goes back to following it.
 * The inline script in <head> applies the stored choice before first paint
 * (see inc/theme-toggle.php).
 */

const KEY = 'portfolio-theme';
const SCHEMES = [ 'light', 'dark' ];
const root = document.documentElement;
const systemDark = window.matchMedia( '(prefers-color-scheme: dark)' );

function system() {
	return systemDark.matches ? 'dark' : 'light';
}

function stored() {
	try {
		const value = localStorage.getItem( KEY );
		return SCHEMES.includes( value ) ? value : null;
	} catch {
		return null;
	}
}

/** The scheme on screen: the stored choice, or the system's. */
function current() {
	return stored() ?? system();
}

function choose( scheme ) {
	try {
		if ( scheme === system() ) {
			localStorage.removeItem( KEY );
		} else {
			localStorage.setItem( KEY, scheme );
		}
	} catch {}

	if ( stored() ) {
		root.dataset.theme = scheme;
	} else {
		delete root.dataset.theme;
	}
}

function render() {
	const scheme = current();

	for ( const label of document.querySelectorAll( '[data-theme-toggle-state]' ) ) {
		label.textContent = 'dark' === scheme ? label.dataset.labelDark : label.dataset.labelLight;
	}
	for ( const output of document.querySelectorAll( '[data-theme-current]' ) ) {
		output.textContent = `${ scheme } (${ stored() ? 'chosen' : 'system' })`;
	}
}

for ( const button of document.querySelectorAll( '[data-theme-toggle]' ) ) {
	button.hidden = false;
	button.addEventListener( 'click', () => {
		choose( 'dark' === current() ? 'light' : 'dark' );
		render();
	} );
}

systemDark.addEventListener( 'change', render );

render();
