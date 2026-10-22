/**
 * i18n (Polylang): Spanish URLs under /es/, the 301s from the old addresses,
 * hreflang with x-default, canonical, the language switcher in the header and
 * the English ↔ Spanish correspondence of every page.
 *
 * Pages are checked against the table in docs/sitemap.md, so the spec runs on
 * the local install and on Playground with SEED=1 (it needs both languages
 * and the pages of the sitemap). Notes, projects and services are taken from
 * their hubs, whatever their slugs are.
 */

import { test, expect } from '@playwright/test';

// English path => Spanish path, from the sitemap. Paths are relative to the
// base URL, so the install can live in a subfolder (/portfolio/).
const PAGES = [
	[ '', 'es/inicio/' ],
	[ 'about/', 'es/sobre-mi/' ],
	[ 'work/', 'es/trabajo/' ],
	[ 'services/', 'es/servicios/' ],
	[ 'services/wordpress-development/', 'es/servicios/desarrollo-wordpress/' ],
	[ 'notes/', 'es/notas/' ],
	[ 'resume/', 'es/cv/' ],
	[ 'contact/', 'es/contacto/' ],
	[ 'how-this-site-is-built/', 'es/como-esta-hecha-esta-web/' ],
	[ 'privacy/', 'es/privacidad/' ],
	[ 'cookies/', 'es/cookies/' ],
	[ 'legal-notice/', 'es/aviso-legal/' ],
];

// Hubs whose rows lead to a post type with a translated base:
// English hub, English base, Spanish hub, Spanish base, old English base under /es/.
const TYPES = [
	{ name: 'note', hub: 'notes/', base: 'notes', esHub: 'es/notas/', esBase: 'notas', oldBase: 'es/notes' },
	{ name: 'project', hub: 'work/', base: 'work', esHub: 'es/trabajo/', esBase: 'trabajo', oldBase: 'es/work' },
	{ name: 'service', hub: 'services/', base: 'services', esHub: 'es/servicios/', esBase: 'servicios', oldBase: 'es/services' },
];

/**
 * Returns an address as origin + path with one trailing slash: the query and
 * the hash do not matter for what is compared here.
 *
 * @param {string} url
 * @return {string}
 */
function clean( url ) {
	const parsed = new URL( url );

	return parsed.origin + parsed.pathname.replace( /\/?$/, '/' );
}

/**
 * Returns the last segment of an address: the slug of a post.
 *
 * @param {string} url
 * @return {string}
 */
function slugOf( url ) {
	return new URL( url ).pathname.split( '/' ).filter( Boolean ).pop();
}

/**
 * Reads what the page says about its language versions.
 *
 * @param {import('@playwright/test').Page} page
 */
async function readHead( page ) {
	return page.evaluate( () => ( {
		lang: document.documentElement.lang,
		canonical: document.querySelector( 'link[rel="canonical"]' )?.href ?? '',
		hreflang: Object.fromEntries(
			[ ...document.querySelectorAll( 'link[rel="alternate"][hreflang]' ) ].map( ( link ) => [ link.hreflang, link.href ] )
		),
		switcherLabel: document.querySelector( 'nav.lang-switch' )?.getAttribute( 'aria-label' ) ?? '',
		switcher: [ ...document.querySelectorAll( '.lang-switch__link' ) ].map( ( link ) => ( {
			lang: link.getAttribute( 'lang' ),
			hreflang: link.getAttribute( 'hreflang' ),
			href: link.href,
			current: link.getAttribute( 'aria-current' ),
			name: link.querySelector( '.screen-reader-text' )?.textContent ?? '',
		} ) ),
	} ) );
}

/**
 * Checks one language version of a page: its language, canonical, hreflang
 * (both versions and x-default), the switcher, and that it is a normal page.
 *
 * @param {import('@playwright/test').Page} page
 * @param {string} language  Language of this version: 'en' or 'es'.
 * @param {string} url       Address of this version.
 * @param {string} otherUrl  Address of the other version.
 */
async function expectVersion( page, language, url, otherUrl ) {
	const other = 'en' === language ? 'es' : 'en';
	const enUrl = 'en' === language ? url : otherUrl;
	const esUrl = 'es' === language ? url : otherUrl;

	const response = await page.goto( url );
	expect( response.status() ).toBe( 200 );
	expect( clean( page.url() ), 'no redirect' ).toBe( clean( url ) );
	await expect( page.locator( 'h1' ) ).toHaveCount( 1 );

	const head = await readHead( page );
	expect( head.lang.slice( 0, 2 ) ).toBe( language );

	// Canonical: the page itself, the same address its hreflang gives.
	expect( clean( head.canonical ) ).toBe( clean( url ) );
	expect( clean( head.hreflang[ language ] ) ).toBe( clean( url ) );

	// hreflang: both versions, and x-default is English.
	expect( Object.keys( head.hreflang ).sort() ).toEqual( [ 'en', 'es', 'x-default' ] );
	expect( clean( head.hreflang.en ) ).toBe( clean( enUrl ) );
	expect( clean( head.hreflang.es ) ).toBe( clean( esUrl ) );
	expect( clean( head.hreflang[ 'x-default' ] ) ).toBe( clean( enUrl ) );

	// Switcher: EN then ES, this language marked, each link to its version.
	expect( head.switcherLabel ).toBe( 'en' === language ? 'Language' : 'Idioma' );
	expect( head.switcher.map( ( link ) => link.lang ) ).toEqual( [ 'en', 'es' ] );
	expect( head.switcher.map( ( link ) => link.hreflang ) ).toEqual( [ 'en', 'es' ] );
	expect( head.switcher.map( ( link ) => link.name ) ).toEqual( [ 'English', 'Español' ] );
	expect( head.switcher.map( ( link ) => link.current ) ).toEqual( 'en' === language ? [ 'true', null ] : [ null, 'true' ] );
	expect( clean( head.switcher.find( ( link ) => link.lang === language ).href ) ).toBe( clean( url ) );
	expect( clean( head.switcher.find( ( link ) => link.lang === other ).href ) ).toBe( clean( otherUrl ) );
}

/**
 * Returns the address of the first row of a hub that leads to the given base
 * (the Work hub also has a row that points to another page).
 *
 * @param {import('@playwright/test').Page} page
 * @param {string} hub   Hub path.
 * @param {string} base  First path segment(s) the row must start with.
 * @return {Promise<string>}
 */
async function firstRow( page, hub, base ) {
	await page.goto( hub );

	const hrefs = await page.locator( 'a.row-a__title' ).evaluateAll( ( links ) => links.map( ( link ) => link.href ) );
	const row = hrefs.find( ( href ) => new URL( href ).pathname.split( '/' ).includes( base ) );
	expect( row, `${ hub } has a row under /${ base }/` ).toBeTruthy();

	return row;
}

test.describe( 'pages of the sitemap', () => {
	for ( const [ en, es ] of PAGES ) {
		test( `${ en || 'home' } ↔ ${ es }`, async ( { page, baseURL } ) => {
			const enUrl = new URL( en, baseURL ).href;
			const esUrl = new URL( es, baseURL ).href;

			await expectVersion( page, 'en', enUrl, esUrl );
			await expectVersion( page, 'es', esUrl, enUrl );
		} );
	}
} );

test.describe( 'notes, projects and services', () => {
	for ( const type of TYPES ) {
		test( `a ${ type.name } and its translation link to each other under /${ type.esBase }/`, async ( { page, baseURL } ) => {
			const enUrl = await firstRow( page, type.hub, type.base );

			// The switcher of the English page leads to the Spanish one.
			await page.goto( enUrl );
			const esUrl = ( await readHead( page ) ).switcher.find( ( link ) => 'es' === link.lang ).href;
			const esHub = clean( new URL( type.esHub, baseURL ).href );
			expect( clean( esUrl ).startsWith( esHub ), `${ esUrl } is under ${ esHub }` ).toBe( true );
			expect( clean( esUrl ).slice( esHub.length ).split( '/' ).filter( Boolean ) ).toHaveLength( 1 );

			// A shared slug is not shown with the -es Polylang adds in the database.
			expect( slugOf( esUrl ) ).not.toBe( `${ slugOf( enUrl ) }-es` );

			await expectVersion( page, 'en', enUrl, esUrl );
			await expectVersion( page, 'es', esUrl, enUrl );
		} );
	}

	test( 'Spanish pages do not link to an address with the -es suffix', async ( { page } ) => {
		for ( const path of [ 'es/inicio/', ...TYPES.map( ( type ) => type.esHub ) ] ) {
			await page.goto( path );

			const suffixed = await page.locator( 'a[href]' ).evaluateAll( ( links ) =>
				links
					.map( ( link ) => new URL( link.href ) )
					.filter( ( url ) => url.origin === window.location.origin && /-es\/?$/.test( url.pathname ) )
					.map( ( url ) => url.pathname )
			);
			expect( suffixed, path ).toEqual( [] );
		}
	} );
} );

test.describe( 'links of the Spanish pages', () => {
	/**
	 * Returns every internal link of the page except the language switcher.
	 *
	 * @param {import('@playwright/test').Page} page
	 * @return {Promise<string[]>}
	 */
	async function links( page ) {
		return page.locator( 'a[href]:not(.lang-switch__link)' ).evaluateAll( ( anchors ) => anchors.map( ( anchor ) => anchor.href ) );
	}

	// Header, footer and content build their links from the translation of each
	// page, not from the English path (/work/ instead of /es/trabajo/).
	test( 'only lead to Spanish pages', async ( { page, baseURL } ) => {
		const root = new URL( 'es/', baseURL );

		// The 404 page has links of its own.
		for ( const path of [ ...PAGES.map( ( [ , es ] ) => es ), 'es/no-existe/' ] ) {
			await page.goto( path );

			const stray = ( await links( page ) )
				.map( ( href ) => new URL( href ) )
				// The design system page is English only (docs/decisions/11-i18n.md).
				.filter( ( url ) => url.origin === root.origin && ! url.pathname.startsWith( root.pathname ) && ! /\/(wp-|feed\/|design-system\/$)/.test( url.pathname ) )
				.map( ( url ) => url.pathname );

			expect( stray, path ).toEqual( [] );
		}
	} );

	test( 'the Spanish home links to the translation of each page', async ( { page, baseURL } ) => {
		await page.goto( 'es/inicio/' );

		const title = await page.locator( 'a.site__title' ).evaluate( ( anchor ) => anchor.href );
		expect( clean( title ), 'site title' ).toBe( clean( new URL( 'es/inicio/', baseURL ).href ) );

		const hrefs = ( await links( page ) ).map( clean );
		for ( const path of [ 'es/sobre-mi/', 'es/trabajo/', 'es/servicios/', 'es/notas/', 'es/cv/', 'es/contacto/' ] ) {
			expect( hrefs, path ).toContain( clean( new URL( path, baseURL ).href ) );
		}
	} );
} );

test.describe( 'redirects (301)', () => {
	// Requests go through page.request, which shares the cookies of the page:
	// Playground logs in on any request without cookies, and answers that first
	// one with a 302 to the same address, which would hide the 301 under test.
	test( '/es/cookies-es/ goes to /es/cookies/', async ( { page, baseURL } ) => {
		await page.goto( 'es/cookies/' );
		await expect( page.locator( 'h1' ) ).toHaveCount( 1 );

		const response = await page.request.get( 'es/cookies-es/', { maxRedirects: 0 } );
		expect( response.status() ).toBe( 301 );
		expect( clean( response.headers().location ) ).toBe( clean( new URL( 'es/cookies/', baseURL ).href ) );
	} );

	for ( const type of TYPES ) {
		test( `the old English base of a ${ type.name } under /es/ goes to /${ type.esBase }/`, async ( { page } ) => {
			const esUrl = await firstRow( page, type.esHub, type.esBase );

			await page.goto( esUrl );
			const enUrl = ( await readHead( page ) ).hreflang.en;

			// Polylang keeps the English base with the slug the post has in the
			// database: its public one, plus -es when the English post has it too.
			const slug = slugOf( esUrl ) === slugOf( enUrl ) ? `${ slugOf( esUrl ) }-es` : slugOf( esUrl );
			const old = `${ type.oldBase }/${ slug }/`;

			const response = await page.request.get( old, { maxRedirects: 0 } );
			expect( response.status(), old ).toBe( 301 );
			expect( clean( response.headers().location ), old ).toBe( clean( esUrl ) );
		} );
	}
} );

test.describe( 'language switcher', () => {
	test( 'a page without translation leads to the Spanish home', async ( { page } ) => {
		// The design system is the one page that exists in English only.
		await page.goto( 'design-system/' );
		const head = await readHead( page );

		expect( head.switcher.map( ( link ) => link.lang ) ).toEqual( [ 'en', 'es' ] );
		expect( head.hreflang.es, 'no hreflang for a missing translation' ).toBeUndefined();

		await page.goto( head.switcher.find( ( link ) => 'es' === link.lang ).href );
		expect( new URL( page.url() ).pathname ).toMatch( /\/es\/(inicio\/)?$/ );
		expect( ( await readHead( page ) ).lang.slice( 0, 2 ) ).toBe( 'es' );
	} );

	test( 'the switcher fits a 360px screen in both languages', async ( { page } ) => {
		await page.setViewportSize( { width: 360, height: 800 } );

		for ( const path of [ '', 'es/inicio/' ] ) {
			await page.goto( path );

			expect( await page.evaluate( () => document.documentElement.scrollWidth - window.innerWidth ), path ).toBeLessThanOrEqual( 0 );
			for ( const link of await page.locator( '.lang-switch__link' ).all() ) {
				await expect( link ).toBeVisible();
			}
		}
	} );
} );
