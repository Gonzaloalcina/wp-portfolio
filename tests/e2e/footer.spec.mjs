/**
 * Footer: the reduced main menu plus Resume and "How this site is built" and
 * the LinkedIn icon link, then a row with the legal pages and
 * "Cookie settings", in English and in Spanish.
 * WordPress maintenance is not a footer link: the Services hub and every
 * other service page link to it (docs/decisions/07-information-architecture.md).
 *
 * Runs on the local install and on Playground with SEED=1 (both seed the
 * footer menus and a LinkedIn profile in Site settings).
 */

import { test, expect } from '@playwright/test';

const LANGUAGES = [
	{
		path: '',
		links: [ 'About', 'Projects', 'Services', 'Notes', 'Contact', 'Resume', 'How this site is built' ],
		legal: [ 'Privacy', 'Legal notice', 'Cookies' ],
		services: 'services/',
		maintenance: /\/services\/wordpress-maintenance\/$/,
	},
	{
		path: 'es/inicio/',
		links: [ 'Sobre mí', 'Proyectos', 'Servicios', 'Notas', 'Contacto', 'CV', 'Cómo está hecha esta web' ],
		legal: [ 'Privacidad', 'Aviso legal', 'Cookies' ],
		services: 'es/servicios/',
		maintenance: /\/es\/servicios\/mantenimiento-wordpress\/$/,
	},
];

for ( const language of LANGUAGES ) {
	test( `footer links and LinkedIn in /${ language.path }`, async ( { page } ) => {
		await page.goto( language.path );
		const footer = page.locator( '.site__footer' );

		// The menu items, in order, then the LinkedIn icon.
		const main = footer.locator( '.site__footer-links:not(.site__footer-legal)' );
		const titles = await main.locator( '.link-row-a__item a:not(.link-row-a__icon)' ).allTextContents();
		expect( titles.map( ( title ) => title.trim() ) ).toEqual( language.links );

		// The legal pages on their own row, then the consent link (when consent is on).
		const legal = await footer.locator( '.site__footer-legal a:not([data-consent-open])' ).allTextContents();
		expect( legal.map( ( title ) => title.trim() ) ).toEqual( language.legal );
		await expect( main.locator( '[data-consent-open]' ) ).toHaveCount( 0 );

		await expect( footer.locator( `a[href*="wordpress-maintenance"], a[href*="mantenimiento-wordpress"]` ) ).toHaveCount( 0 );

		const linkedin = main.locator( 'a.link-row-a__icon' );
		await expect( linkedin ).toHaveCount( 1 );
		await expect( linkedin ).toHaveAttribute( 'href', /^https:\/\/www\.linkedin\.com\/in\// );
		await expect( linkedin ).toHaveAttribute( 'aria-label', 'LinkedIn' );
		await expect( linkedin ).toHaveAttribute( 'target', '_blank' );
		await expect( linkedin ).toHaveAttribute( 'rel', 'me noopener' );
		await expect( linkedin ).toHaveAttribute( 'aria-describedby', 'new-tab-note' );
		await expect( linkedin.locator( 'svg[aria-hidden="true"]' ) ).toHaveCount( 1 );
	} );

	test( `maintenance is linked from the hub and the other services in /${ language.services }`, async ( { page } ) => {
		await page.goto( language.services );
		const rows = page.locator( '#which-one ~ .list-a .row-a__title' );
		const urls = await rows.evaluateAll( ( links ) => links.map( ( link ) => link.href ) );
		const maintenance = urls.find( ( url ) => language.maintenance.test( url ) );
		expect( maintenance ).toBeTruthy();

		// Every other service page lists it under "Other services".
		const services = [ ...new Set( urls.filter( ( url ) => url !== maintenance && url.includes( language.services ) ) ) ];
		expect( services.length ).toBeGreaterThan( 0 );
		for ( const url of services ) {
			await page.goto( url );
			await expect( page.locator( `#other-services ~ .list-a .row-a__title[href="${ maintenance }"]` ) ).toHaveCount( 1 );
		}
	} );
}
