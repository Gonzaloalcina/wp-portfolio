/**
 * Connected forms ([data-form-a]): sends them to their action as JSON and
 * says how it went in the status line, without leaving the page.
 *
 * - Turnstile (Cloudflare's spam check) loads on the first interaction with
 *   a form, not with the page, so pages without a visitor typing pay
 *   nothing. Most visitors never see it (interaction-only).
 * - The browser checks the fields first (required, type, maxlength); the
 *   endpoint checks them again and its field errors are shown under each
 *   field, linked with aria-describedby.
 * - On success the form is reset and a `form-a:sent` event bubbles up with
 *   the form ID and the service chosen, for the dataLayer to pick up later.
 *
 * Wording comes from the form's data-messages (the theme translates it).
 */

const TURNSTILE = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
const TOKEN_TIMEOUT = 15000;

let turnstileReady = null;

/** Loads the Turnstile script once. */
function loadTurnstile() {
	turnstileReady ??= new Promise( ( resolve, reject ) => {
		const script = document.createElement( 'script' );
		script.src = TURNSTILE;
		script.async = true;
		script.onload = () => resolve( window.turnstile );
		script.onerror = reject;
		document.head.append( script );
	} );

	return turnstileReady;
}

/** The scheme on screen: the toggle's choice, else the system's. */
function scheme() {
	const chosen = document.documentElement.dataset.theme;
	if ( chosen === 'light' || chosen === 'dark' ) {
		return chosen;
	}

	return window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light';
}

/**
 * Sets up one form.
 *
 * @param {HTMLFormElement} form
 */
function connect( form ) {
	const messages = JSON.parse( form.dataset.messages || '{}' );
	const status = form.querySelector( '[data-form-a-status]' );
	const button = form.querySelector( 'button[type="submit"]' );
	const challenge = form.querySelector( '[data-form-a-challenge]' );
	const formId = form.elements.form_id?.value ?? '';

	let widget = null;
	let token = '';
	let waiting = null;

	function say( text ) {
		status.textContent = text;
	}

	/** Renders the Turnstile widget on first use. */
	async function startChallenge() {
		if ( ! challenge || widget !== null ) {
			return;
		}

		widget = '';
		try {
			const turnstile = await loadTurnstile();
			widget = turnstile.render( challenge, {
				sitekey: challenge.dataset.formAChallenge,
				action: formId,
				appearance: 'interaction-only',
				size: 'flexible',
				theme: scheme(),
				language: document.documentElement.lang || 'auto',
				callback: ( value ) => {
					token = value;
					waiting?.( value );
				},
				'expired-callback': () => {
					token = '';
				},
			} );
		} catch {
			widget = null;
		}
	}

	/** Resolves with a token, waiting for the widget when it has none yet. */
	function getToken() {
		if ( token || ! challenge ) {
			return Promise.resolve( token );
		}

		startChallenge();

		return new Promise( ( resolve ) => {
			const timer = setTimeout( () => resolve( '' ), TOKEN_TIMEOUT );
			waiting = ( value ) => {
				clearTimeout( timer );
				waiting = null;
				resolve( value );
			};
		} );
	}

	function clearErrors() {
		for ( const error of form.querySelectorAll( '.field-a__error' ) ) {
			const control = form.elements[ error.dataset.for ];
			control?.removeAttribute( 'aria-invalid' );
			if ( control ) {
				const ids = ( control.getAttribute( 'aria-describedby' ) || '' )
					.split( ' ' )
					.filter( ( id ) => id && id !== error.id );
				if ( ids.length ) {
					control.setAttribute( 'aria-describedby', ids.join( ' ' ) );
				} else {
					control.removeAttribute( 'aria-describedby' );
				}
			}
			error.remove();
		}
	}

	/**
	 * Shows the endpoint's field errors under each field.
	 *
	 * @param {Record<string, string>} fields Field name => code.
	 */
	function showErrors( fields ) {
		let first = null;

		for ( const [ name, code ] of Object.entries( fields ) ) {
			const control = form.elements[ name ];
			if ( ! control?.id ) {
				continue;
			}

			let text = messages.fields?.[ code ] ?? messages.fields?.invalid ?? '';
			const kind = control.inputMode === 'url' ? 'url' : control.type;
			if ( code === 'invalid' && ( kind === 'email' || kind === 'url' ) ) {
				text = messages.fields?.[ kind ] ?? text;
			}

			const error = document.createElement( 'p' );
			error.className = 'field-a__error';
			error.id = `${ control.id }-error`;
			error.dataset.for = name;
			error.textContent = text;
			( control.closest( '.field-a__select' ) ?? control ).after( error );

			control.setAttribute( 'aria-invalid', 'true' );
			control.setAttribute(
				'aria-describedby',
				[ control.getAttribute( 'aria-describedby' ), error.id ].filter( Boolean ).join( ' ' )
			);
			first ??= control;
		}

		first?.focus();
	}

	async function send( event ) {
		event.preventDefault();
		if ( button.disabled ) {
			return;
		}

		clearErrors();
		button.disabled = true;
		form.setAttribute( 'aria-busy', 'true' );
		say( messages.sending ?? '' );

		const data = Object.fromEntries( new FormData( form ) );
		data[ 'cf-turnstile-response' ] = await getToken();
		data.page = window.location.href;

		let code = 'error';
		let body = {};
		try {
			const response = await fetch( form.action, {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
				body: JSON.stringify( data ),
			} );
			body = await response.json().catch( () => ( {} ) );
			code = response.ok && body.ok ? 'sent' : body.code ?? 'error';
		} catch {
			code = 'error';
		}

		say( messages[ code ] ?? messages.error ?? '' );

		if ( code === 'sent' ) {
			form.dispatchEvent(
				new CustomEvent( 'form-a:sent', {
					bubbles: true,
					detail: { formId, service: data.service ?? '' },
				} )
			);
			form.reset();
		} else if ( code === 'invalid_fields' ) {
			showErrors( body.data?.fields ?? {} );
		}

		// A token is good for one check, whatever the answer.
		token = '';
		if ( widget ) {
			window.turnstile?.reset( widget );
		}

		form.removeAttribute( 'aria-busy' );
		button.disabled = false;
	}

	form.addEventListener( 'focusin', startChallenge, { once: true } );
	form.addEventListener( 'pointerdown', startChallenge, { once: true } );
	form.addEventListener( 'submit', send );
	button.disabled = false;
}

document.querySelectorAll( 'form[data-form-a]' ).forEach( connect );
