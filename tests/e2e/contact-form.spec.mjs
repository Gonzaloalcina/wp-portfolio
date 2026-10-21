/**
 * Contact form: a real submission through Turnstile (test keys on local
 * installs) and the endpoint, then the error display with the endpoint's
 * answer mocked, so it costs nothing against the rate limit (5 an hour).
 *
 * Skipped where the form is not connected (no portfolio-core, or no
 * Turnstile keys outside local and development installs).
 */

import { test, expect } from '@playwright/test';

/**
 * Opens a page and skips the test when its form is a placeholder.
 *
 * @param {import('@playwright/test').Page} page
 * @param {string} path
 */
async function openForm( page, path ) {
	await page.goto( path );
	const form = page.locator( 'main form' );
	test.skip( ( await form.getAttribute( 'data-form-a' ) ) === null, 'Form not connected here.' );

	await expect( form.locator( 'button[type="submit"]' ) ).toBeEnabled();
	return form;
}

test( 'the contact form sends a message', async ( { page } ) => {
	const form = await openForm( page, 'contact/' );

	// The honeypot is in the form but not on screen.
	await expect( form.locator( 'input[name="website"]' ) ).not.toBeInViewport();

	await form.getByLabel( 'Name' ).fill( 'E2E test' );
	await form.getByLabel( 'Email' ).fill( 'e2e@example.com' );
	await form.getByLabel( 'Message' ).fill( 'Sent by tests/e2e/contact-form.spec.mjs.' );

	const sent = page.waitForResponse( ( response ) => response.url().includes( 'portfolio/v1/contact' ) );
	await form.locator( 'button[type="submit"]' ).click();
	expect( ( await sent ).status() ).toBe( 200 );

	await expect( form.getByRole( 'status' ) ).toHaveText( 'Thank you. Your message has been sent.' );
	await expect( form.getByLabel( 'Name' ) ).toHaveValue( '' );
} );

test( 'field errors from the endpoint show under their field', async ( { page } ) => {
	await page.route( '**/portfolio/v1/contact', ( route ) =>
		route.fulfill( {
			status: 400,
			contentType: 'application/json',
			body: JSON.stringify( {
				code: 'invalid_fields',
				data: { status: 400, fields: { site: 'invalid' } },
			} ),
		} )
	);

	const form = await openForm( page, 'services/?service=wordpress-maintenance' );

	await form.getByLabel( 'Name' ).fill( 'E2E test' );
	await form.getByLabel( 'Email' ).fill( 'e2e@example.com' );
	await form.getByLabel( 'Site URL' ).fill( 'nodot' );
	await form.getByLabel( 'Message' ).fill( 'Never sent: the endpoint is mocked.' );
	await form.locator( 'button[type="submit"]' ).click();

	const site = form.getByLabel( 'Site URL' );
	await expect( site ).toHaveAttribute( 'aria-invalid', 'true' );
	await expect( site ).toBeFocused();
	await expect( site ).toHaveAccessibleDescription( /Enter a web address/ );
	await expect( form.getByRole( 'status' ) ).toContainText( 'Some fields need a change' );

	// The service chosen stays, so the visitor only fixes what is wrong.
	await expect( form.getByLabel( 'What do you need?' ) ).toHaveValue( 'wordpress-maintenance' );
} );
