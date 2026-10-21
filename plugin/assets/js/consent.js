/**
 * Consent state, Google Consent Mode v2 (basic mode) and the GTM loader.
 *
 * Inlined at the top of <head> by inc/consent.php, after
 * window.portfolioConsentConfig: { version, days, gtm, pageType, lang,
 * services }. A classic script, not a module: it must run before first paint
 * and before anything reads the dataLayer.
 *
 * The choice is stored in the `portfolio_consent` cookie as
 * "<version>.<1|0>.<unix time>": the version consented to, analytics
 * accepted or not, and when, which is the record GDPR asks to be able to
 * show. A cookie from an older version counts as no answer.
 *
 * <html data-consent="pending|granted|denied"> tells the banner (theme,
 * consent-a) whether to show. API for the theme: window.portfolioConsent.
 */
( function ( config ) {
	const COOKIE = 'portfolio_consent';
	const root = document.documentElement;

	window.dataLayer = window.dataLayer || [];
	function gtag() {
		// Consent Mode reads the arguments object, not an array.
		window.dataLayer.push( arguments );
	}
	window.gtag = window.gtag || gtag;

	// Everything off until the visitor says otherwise. Ads stay off for good.
	gtag( 'consent', 'default', {
		ad_storage: 'denied',
		ad_user_data: 'denied',
		ad_personalization: 'denied',
		analytics_storage: 'denied',
		functionality_storage: 'denied',
		personalization_storage: 'denied',
		security_storage: 'granted',
	} );

	window.dataLayer.push( {
		page_type: config.pageType,
		language: config.lang,
	} );

	/** @return {boolean|null} Analytics accepted, rejected, or no valid answer. */
	function read() {
		const match = document.cookie.match( /(?:^|;\s*)portfolio_consent=([^;]*)/ );
		if ( ! match ) {
			return null;
		}

		const [ version, analytics ] = decodeURIComponent( match[ 1 ] ).split( '.' );
		if ( version !== String( config.version ) || ( analytics !== '0' && analytics !== '1' ) ) {
			return null;
		}

		return analytics === '1';
	}

	function write( granted ) {
		const value = [ config.version, granted ? 1 : 0, Math.floor( Date.now() / 1000 ) ].join( '.' );
		const secure = window.location.protocol === 'https:' ? '; Secure' : '';
		document.cookie = `${ COOKIE }=${ value }; Max-Age=${ config.days * 86400 }; Path=/; SameSite=Lax${ secure }`;
	}

	let loaded = false;
	function loadGtm() {
		if ( loaded || ! config.gtm ) {
			return;
		}

		loaded = true;
		window.dataLayer.push( { 'gtm.start': Date.now(), event: 'gtm.js' } );
		const script = document.createElement( 'script' );
		script.async = true;
		script.src = `https://www.googletagmanager.com/gtm.js?id=${ encodeURIComponent( config.gtm ) }`;
		document.head.append( script );
	}

	/** Deletes Google Analytics cookies (_ga, _ga_<id>, _gid) on every domain they may use. */
	function clearAnalyticsCookies() {
		const labels = window.location.hostname.split( '.' );
		const domains = [ '' ];
		for ( let i = 0; i < labels.length - 1; i++ ) {
			domains.push( `; Domain=.${ labels.slice( i ).join( '.' ) }` );
		}

		for ( const pair of document.cookie.split( ';' ) ) {
			const name = pair.split( '=' )[ 0 ].trim();
			if ( /^_g(a|id)(_|$)/.test( name ) ) {
				for ( const domain of domains ) {
					document.cookie = `${ name }=; Max-Age=0; Path=/${ domain }`;
				}
			}
		}
	}

	function apply( granted ) {
		root.dataset.consent = granted ? 'granted' : 'denied';
		gtag( 'consent', 'update', { analytics_storage: granted ? 'granted' : 'denied' } );
		if ( granted ) {
			loadGtm();
		}
	}

	const stored = read();
	if ( stored === null ) {
		root.dataset.consent = 'pending';
	} else {
		apply( stored );
	}

	window.portfolioConsent = {
		/** @return {boolean} Whether analytics is accepted now. */
		granted: () => read() === true,

		/**
		 * Stores and applies the visitor's answer.
		 *
		 * @param {boolean} granted Analytics accepted.
		 */
		set( granted ) {
			const before = read();
			write( granted );
			apply( granted );
			window.dataLayer.push( {
				event: 'consent_update',
				analytics_storage: granted ? 'granted' : 'denied',
			} );

			// Withdrawn: remove what GA4 stored. The page reloads so the
			// loaded container stops too.
			if ( ! granted && before === true ) {
				clearAnalyticsCookies();
				if ( loaded ) {
					window.location.reload();
				}
			}
		},

		/** Shows the banner again ("Cookie settings"). */
		open() {
			root.dataset.consent = 'pending';
		},

		/**
		 * The default-language slug of a service, so both languages report
		 * as one.
		 *
		 * @param {string} slug Slug in the page's language.
		 * @return {string}
		 */
		service: ( slug ) => config.services[ slug ] ?? slug ?? '',
	};
} )( window.portfolioConsentConfig );
