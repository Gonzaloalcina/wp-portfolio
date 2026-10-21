/**
 * Services hub and service pages: one H1 and one request form, the service
 * preselected from the page or ?service=<slug>, and no empty section or
 * blank row.
 */

import { test, expect } from '@playwright/test';

const HUB = 'services/';

/**
 * Checks what every page with the request form shares.
 *
 * @param {import('@playwright/test').Page} page
 */
async function expectPageShape( page ) {
	await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
	// main: the admin bar (Playground logs in) has a search form of its own.
	await expect( page.locator( 'main form' ) ).toHaveCount( 1 );
	await expect( page.locator( '#request' ) ).toHaveCount( 1 );
	await expect( page.locator( 'a[href="#request"]' ) ).toHaveCount( 1 );

	// A section renders only with something under its title.
	for ( const section of await page.locator( 'section.section-a' ).all() ) {
		await expect( section.locator( ':scope > :not(h2)' ) ).not.toHaveCount( 0 );
	}
}

test( 'the hub has one H1, one form and a row per service', async ( { page } ) => {
	await page.goto( HUB );
	await expectPageShape( page );

	const rows = page.locator( '#which-one ~ .list-a .row-a' );
	expect( await rows.count() ).toBeGreaterThan( 0 );

	// Every row has a name and a line: the situation, or the problem line.
	for ( const row of await rows.all() ) {
		await expect( row.locator( '.row-a__title' ) ).not.toBeEmpty();
		await expect( row.locator( '.row-a__text' ) ).not.toBeEmpty();
	}

	// One option per row, plus the empty choice and "Not sure".
	await expect( page.locator( 'select[name="service"] option' ) ).toHaveCount( ( await rows.count() ) + 2 );
	await expect( page.locator( 'select[name="service"]' ) ).toHaveValue( '' );
} );

test( '?service=<slug> preselects the service', async ( { page } ) => {
	await page.goto( HUB );
	const slug = await page.locator( '[data-cta-location="services_row"]' ).first().getAttribute( 'data-service' );

	await page.goto( `${ HUB }?service=${ slug }` );
	await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
	await expect( page.locator( 'select[name="service"]' ) ).toHaveValue( slug );
} );

test( 'an unknown ?service= preselects nothing', async ( { page } ) => {
	await page.goto( `${ HUB }?service=not-a-service` );
	await expectPageShape( page );
	await expect( page.locator( 'select[name="service"]' ) ).toHaveValue( '' );
} );

test( 'a service page ends with the form, its service preselected', async ( { page } ) => {
	await page.goto( HUB );
	const row = page.locator( '[data-cta-location="services_row"]' ).first();
	const slug = await row.getAttribute( 'data-service' );

	await row.click();
	await expectPageShape( page );
	await expect( page.locator( 'select[name="service"]' ) ).toHaveValue( slug );
	await expect( page.locator( '#contact' ) ).toHaveCount( 0 );
} );

test( 'the hub fits a 360px screen', async ( { page } ) => {
	await page.setViewportSize( { width: 360, height: 800 } );
	await page.goto( HUB );

	const overflow = await page.evaluate( () => document.documentElement.scrollWidth - window.innerWidth );
	expect( overflow ).toBeLessThanOrEqual( 0 );
} );
