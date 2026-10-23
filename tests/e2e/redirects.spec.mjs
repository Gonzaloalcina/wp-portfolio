/**
 * Addresses of the previous gonzaloalcina.dev (Spanish only, no /es/) answer
 * with a 301 to the page that replaces them (plugin/inc/redirects.php,
 * docs/decisions/12-deploy.md). /contacto/ and /proyectos/ are redirected by
 * WordPress itself, and are checked here too.
 */

import { test, expect } from '@playwright/test';

const REMOVED = [
	'al-traves',
	'colegio-esclavas-de-maria',
	'faltan-gatos',
	'flora-veiga',
	'gusto-por-lo-nuestro',
	'h-arquitectos',
	'hana-lole',
	'opalo',
	'plan-proa',
	'the-sybarite-flamingo',
];

const PORTFOLIO_CATEGORIES = [ 'agencias', 'arquitectura', 'arte', 'corporativo', 'fotografia', 'gastronomia', 'ocio', 'tienda-online', 'viajes', 'woocommerce' ];

const REDIRECTS = [
	[ 'contacto/', 'es/contacto/' ],
	[ 'proyectos/', 'es/proyectos/' ],
	[ 'proyectos/jalea-real-photography/', 'es/proyectos/jalea-real-photography/' ],
	[ 'proyectos/ramon-esteve-estudio/', 'es/proyectos/ramon-esteve/' ],
	[ 'proyectos/xufa-es/', 'es/proyectos/xufa/' ],
	// The old slug ends in U+FE0F, sent percent-encoded.
	[ 'proyectos/relyon-relocation%ef%b8%8f/', 'es/proyectos/relyon-relocation/' ],
	[ 'category/blog/', 'es/notas/' ],
	[ 'category/wordpress/', 'es/notas/' ],
	...REMOVED.map( ( slug ) => [ `proyectos/${ slug }/`, 'es/proyectos/' ] ),
	...PORTFOLIO_CATEGORIES.map( ( slug ) => [ `category/${ slug }/`, 'es/proyectos/' ] ),
];

/**
 * Returns an address as origin + path, without the query.
 *
 * @param {string} url
 * @return {string}
 */
function clean( url ) {
	const parsed = new URL( url );
	return parsed.origin + parsed.pathname;
}

for ( const [ old, now ] of REDIRECTS ) {
	test( `/${ old } redirects to /${ now }`, async ( { page, baseURL } ) => {
		const response = await page.request.get( old, { maxRedirects: 0 } );
		expect( response.status(), old ).toBe( 301 );
		expect( clean( response.headers().location ), old ).toBe( clean( new URL( now, baseURL ).href ) );
	} );
}

test( 'the query string is kept', async ( { page } ) => {
	const response = await page.request.get( 'proyectos/opalo/?utm_source=old', { maxRedirects: 0 } );
	expect( response.status() ).toBe( 301 );
	expect( new URL( response.headers().location ).search ).toBe( '?utm_source=old' );
} );

test( 'the new Spanish pages are not redirected', async ( { page } ) => {
	for ( const path of [ 'es/proyectos/', 'es/proyectos/xufa/', 'es/notas/' ] ) {
		const response = await page.request.get( path, { maxRedirects: 0 } );
		expect( response.status(), path ).toBe( 200 );
	}
} );
