/**
 * Renders one social image (1200×630 PNG) per page: the mark on the left and
 * the page title, on the design of the default image in the brand kit.
 *
 * Chromium from Playwright (already a dev dependency) draws an HTML template
 * with the site's own fonts and colours, so the images match the site and no
 * image library is needed. Colours come from tokens/tokens.json (light theme,
 * the reference theme). Pixel sizes are fine here: this is an image, not the
 * site's CSS.
 *
 * The list comes from scripts/og-images.php (export); the PNGs go back into
 * WordPress with the same script (import). Rerun when a title changes.
 *
 * Usage:
 *   node scripts/og-images.mjs <list.json> <out-dir>
 *
 * list.json: [ { "id": 42, "title": "Ramón Esteve", "file": "og-ramon-esteve-en.png" } ]
 */

import { readFileSync, writeFileSync, mkdirSync, mkdtempSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve, dirname } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { chromium } from '@playwright/test';

const ROOT = resolve( dirname( fileURLToPath( import.meta.url ) ), '..' );
const [ listPath, outDir ] = process.argv.slice( 2 );

if ( ! listPath || ! outDir ) {
	console.error( 'Usage: node scripts/og-images.mjs <list.json> <out-dir>' );
	process.exit( 1 );
}

const tokens = JSON.parse( readFileSync( join( ROOT, 'tokens/tokens.json' ), 'utf8' ) );
const [ paper, ink, muted ] = [ 'paper', 'ink', 'muted' ].map( ( name ) => tokens.color.light[ name ].$value );
const lineOpacity = tokens.line.opacity.$value;
const font = ( path ) => pathToFileURL( join( ROOT, 'theme/assets/fonts', path ) ).href;

const escape = ( text ) =>
	text.replace( /[&<>"]/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' } )[ c ] );

const page = ( title ) => `<!doctype html>
<html><head><meta charset="utf-8"><style>
@font-face { font-family: Newsreader; src: url(${ font( 'newsreader/newsreader-latin-wght-normal.woff2' ) }) format('woff2'); font-weight: 200 800; }
@font-face { font-family: Newsreader; src: url(${ font( 'newsreader/newsreader-latin-ext-wght-normal.woff2' ) }) format('woff2'); font-weight: 200 800; unicode-range: U+0100-024F, U+1E00-1EFF; }
@font-face { font-family: Inter; src: url(${ font( 'inter/inter-latin-wght-normal.woff2' ) }) format('woff2'); font-weight: 100 900; }
* { margin: 0; box-sizing: border-box; }
html, body { width: 1200px; height: 630px; }
body { background: ${ paper }; color: ${ ink }; position: relative; overflow: hidden; }
.line { position: absolute; left: 0; right: 0; height: 1px; background: ${ muted }; opacity: ${ lineOpacity }; }
.tick { position: absolute; left: 72px; width: 6px; height: 6px; background: ${ ink }; }
.mark { position: absolute; left: 72px; top: 267px; width: 96px; height: 96px; }
.title { position: absolute; left: 206px; right: 72px; top: 0; bottom: 0; display: flex; align-items: center; }
.title h1 { font: 500 var(--size)/1.1 Newsreader, Georgia, serif; letter-spacing: -0.02em; text-wrap: balance; }
.domain { position: absolute; left: 72px; top: 500px; font: 400 24px/1 Inter, system-ui, sans-serif; color: ${ muted }; }
</style></head><body>
<div class="line" style="top:72px"></div><div class="tick" style="top:69px"></div>
<div class="line" style="top:557px"></div><div class="tick" style="top:554px"></div>
<svg class="mark" viewBox="0 0 64 64" aria-hidden="true"><rect x="2" y="2" width="60" height="60" fill="none" stroke="${ ink }" stroke-width="4"/><rect x="16" y="32" width="16" height="16" fill="${ ink }"/></svg>
<div class="title"><h1>${ escape( title ) }</h1></div>
<div class="domain">gonzaloalcina.dev</div>
</body></html>`;

const list = JSON.parse( readFileSync( listPath, 'utf8' ) );
mkdirSync( outDir, { recursive: true } );
const work = mkdtempSync( join( tmpdir(), 'og-' ) );

const browser = await chromium.launch();
const tab = await browser.newPage( { viewport: { width: 1200, height: 630 } } );

for ( const item of list ) {
	const html = join( work, 'og.html' );
	writeFileSync( html, page( item.title ) );
	await tab.goto( pathToFileURL( html ).href );
	await tab.evaluate( () => document.fonts.ready );

	// Largest size at which the title fits in three lines between the hairlines.
	await tab.evaluate( () => {
		const h1 = document.querySelector( 'h1' );
		for ( const size of [ 80, 72, 64, 56, 48 ] ) {
			h1.style.setProperty( '--size', `${ size }px` );
			const lines = Math.round( h1.offsetHeight / ( size * 1.1 ) );
			if ( lines <= ( size > 64 ? 2 : 3 ) && h1.scrollWidth <= h1.clientWidth ) {
				return;
			}
		}
	} );

	await tab.screenshot( { path: join( outDir, item.file ) } );
	console.log( `${ item.file }  ${ item.title }` );
}

await browser.close();
