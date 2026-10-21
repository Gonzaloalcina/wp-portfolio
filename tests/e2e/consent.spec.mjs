/**
 * Consent banner and dataLayer (Consent Mode v2, basic mode): nothing is
 * granted or recorded before the visitor answers, the answer is kept in the
 * portfolio_consent cookie, "Cookie settings" changes it, and events reach
 * the dataLayer only with analytics accepted.
 *
 * GTM is not configured locally, so no request to Google is expected in any
 * case; the dataLayer is what GTM would read.
 */

import { test, expect } from '@playwright/test';

const PAGE = 'services/';

/**
 * The dataLayer, with gtag() calls (arguments objects) as arrays.
 *
 * @param {import('@playwright/test').Page} page
 * @return {Promise<Array>}
 */
function dataLayer( page ) {
	return page.evaluate( () =>
		window.dataLayer.map( ( entry ) =>
			typeof entry === 'object' && 'length' in entry ? Array.from( entry ) : entry
		)
	);
}

/**
 * Events pushed to the dataLayer.
 *
 * @param {import('@playwright/test').Page} page
 * @param {string} name Event name.
 */
async function events( page, name ) {
	return ( await dataLayer( page ) ).filter( ( entry ) => entry.event === name );
}

/**
 * Clicks a Services row without leaving the page.
 *
 * @param {import('@playwright/test').Page} page
 */
async function clickRow( page ) {
	const row = page.locator( 'a[data-cta-location="services_row"]' ).first();
	await row.evaluate( ( link ) => link.addEventListener( 'click', ( event ) => event.preventDefault(), { once: true } ) );
	await row.click();
}

test.beforeEach( async ( { page } ) => {
	// Fail the test if anything tries to reach Google.
	await page.route( /googletagmanager\.com|google-analytics\.com/, ( route ) => {
		throw new Error( `Unexpected request to ${ route.request().url() }` );
	} );
} );

test( 'before an answer: banner shown, everything denied, nothing recorded', async ( { page } ) => {
	await page.goto( PAGE );

	const banner = page.getByRole( 'region', { name: 'Cookie consent' } );
	await expect( banner ).toBeVisible();
	await expect( banner.getByRole( 'link', { name: 'Cookie policy' } ) ).toHaveAttribute( 'href', /\/cookies\/$/ );

	const [ defaults, context ] = await dataLayer( page );
	expect( defaults.slice( 0, 2 ) ).toEqual( [ 'consent', 'default' ] );
	expect( defaults[ 2 ] ).toMatchObject( { analytics_storage: 'denied', ad_storage: 'denied' } );
	expect( context ).toEqual( { page_type: 'services', language: 'en' } );

	await clickRow( page );
	expect( await events( page, 'cta_click' ) ).toEqual( [] );
} );

test( 'accept and reject weigh the same', async ( { page } ) => {
	await page.goto( PAGE );

	const banner = page.getByRole( 'region', { name: 'Cookie consent' } );
	const style = ( name ) =>
		banner
			.getByRole( 'button', { name } )
			.evaluate( ( button ) => {
				const { backgroundColor, color, fontWeight, borderColor } = getComputedStyle( button );
				return { backgroundColor, color, fontWeight, borderColor };
			} );

	expect( await style( 'Reject' ) ).toEqual( await style( 'Accept' ) );
} );

test( 'accepting is stored, grants analytics and records events', async ( { page, context } ) => {
	await page.goto( PAGE );
	await page.getByRole( 'button', { name: 'Accept' } ).click();

	await expect( page.getByRole( 'region', { name: 'Cookie consent' } ) ).toBeHidden();
	const cookie = ( await context.cookies() ).find( ( { name } ) => name === 'portfolio_consent' );
	expect( cookie?.value ).toMatch( /^1\.1\.\d{10}$/ );

	expect( await dataLayer( page ) ).toContainEqual( [ 'consent', 'update', { analytics_storage: 'granted' } ] );
	expect( await events( page, 'consent_update' ) ).toEqual( [ { event: 'consent_update', analytics_storage: 'granted' } ] );

	await clickRow( page );
	const [ click ] = await events( page, 'cta_click' );
	expect( click ).toMatchObject( { cta_location: 'services_row' } );
	expect( Object.keys( click ).sort() ).toEqual( [ 'cta_location', 'event', 'link_url', 'service' ] );

	// Next page: no banner, granted from the start.
	await page.reload();
	await expect( page.locator( 'html' ) ).toHaveAttribute( 'data-consent', 'granted' );
	await expect( page.getByRole( 'region', { name: 'Cookie consent' } ) ).toBeHidden();
} );

test( 'rejecting is stored and records nothing', async ( { page, context } ) => {
	await page.goto( PAGE );
	await page.getByRole( 'button', { name: 'Reject' } ).click();

	const cookie = ( await context.cookies() ).find( ( { name } ) => name === 'portfolio_consent' );
	expect( cookie?.value ).toMatch( /^1\.0\.\d{10}$/ );

	await clickRow( page );
	expect( await events( page, 'cta_click' ) ).toEqual( [] );
} );

test( 'cookie settings opens the banner again and gives focus back', async ( { page } ) => {
	await page.goto( PAGE );
	await page.getByRole( 'button', { name: 'Accept' } ).click();

	const settings = page.getByRole( 'link', { name: 'Cookie settings' } );
	await settings.click();

	await expect( page.getByRole( 'region', { name: 'Cookie consent' } ) ).toBeVisible();
	await expect( page.getByRole( 'button', { name: 'Accept' } ) ).toBeFocused();

	await page.getByRole( 'button', { name: 'Reject' } ).click();
	await expect( settings ).toBeFocused();
	await expect( page.locator( 'html' ) ).toHaveAttribute( 'data-consent', 'denied' );
} );

test( 'an answer to an older version counts as none', async ( { page, context, baseURL } ) => {
	await context.addCookies( [ { name: 'portfolio_consent', value: '0.1.1700000000', url: baseURL } ] );
	await page.goto( PAGE );

	await expect( page.getByRole( 'region', { name: 'Cookie consent' } ) ).toBeVisible();
} );

test( 'the banner speaks the page language', async ( { page } ) => {
	await page.goto( 'es/servicios/' );

	const banner = page.getByRole( 'region', { name: 'Consentimiento de cookies' } );
	await expect( banner.getByRole( 'button', { name: 'Aceptar' } ) ).toBeVisible();
	await expect( banner.getByRole( 'button', { name: 'Rechazar' } ) ).toBeVisible();
	await expect( page.getByRole( 'link', { name: 'Ajustes de cookies' } ) ).toBeVisible();
	expect( ( await dataLayer( page ) )[ 1 ] ).toEqual( { page_type: 'services', language: 'es' } );
} );
