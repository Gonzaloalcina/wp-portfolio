/**
 * Builds theme/assets/css/tokens.css from tokens/tokens.json.
 *
 * Usage:
 *   node scripts/build-tokens.mjs           Write the CSS file.
 *   node scripts/build-tokens.mjs --check   Exit 1 if the CSS file is out of date.
 *
 * Rules (see docs/design-brief.md):
 * - CSS name = token path joined with "-" (font.size.sm → --font-size-sm).
 * - Dimensions with a unit multiplier become calc(var(--unit) * n).
 * - Colours live under color.<mode>; the mode is dropped from the name. Light
 *   goes in :root, dark in the prefers-color-scheme block and [data-theme="dark"].
 * - --color-line is derived from color.muted and line.opacity.
 */

import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = resolve( dirname( fileURLToPath( import.meta.url ) ), '..' );
export const TOKENS_PATH = resolve( ROOT, 'tokens/tokens.json' );
export const CSS_PATH = resolve( ROOT, 'theme/assets/css/tokens.css' );

const NS = 'com.gonzaloalcina';
const MODES = [ 'light', 'dark' ];

export function readTokens() {
	return JSON.parse( readFileSync( TOKENS_PATH, 'utf8' ) );
}

/**
 * Flattens the token tree into { path, type, value, extensions } items.
 * A group's $type applies to every token inside it, as in the W3C format.
 */
export function flatten( node, path = [], inheritedType ) {
	const type = node.$type ?? inheritedType;

	if ( '$value' in node ) {
		return [ { path, type, value: node.$value, extensions: node.$extensions ?? {} } ];
	}

	return Object.entries( node )
		.filter( ( [ key ] ) => ! key.startsWith( '$' ) )
		.flatMap( ( [ key, child ] ) => flatten( child, [ ...path, key ], type ) );
}

function formatNumber( n ) {
	return String( Number( n.toFixed( 4 ) ) );
}

function formatFontFamily( families ) {
	return families.map( ( f ) => ( /\s/.test( f ) ? `"${ f }"` : f ) ).join( ', ' );
}

function formatValue( token ) {
	const multiplier = token.extensions[ `${ NS }.unit` ];

	switch ( token.type ) {
		case 'dimension':
			return multiplier === undefined
				? token.value
				: `calc(var(--unit) * ${ formatNumber( multiplier ) })`;
		case 'fontFamily':
			return formatFontFamily( token.value );
		case 'number':
			return `${ token.value }${ token.extensions[ `${ NS }.css-unit` ] ?? '' }`;
		default:
			return String( token.value );
	}
}

function declaration( name, value ) {
	return `\t--${ name }: ${ value };`;
}

/**
 * JavaScript lists integer-like keys ("1", "2") before others ("0-5"), so
 * sibling tokens that all have a multiplier are re-sorted by it. Groups keep
 * their order of first appearance.
 */
function sortByMultiplier( items ) {
	const groups = new Map();
	for ( const t of items ) {
		const parent = t.path.slice( 0, -1 ).join( '.' );
		groups.set( parent, [ ...( groups.get( parent ) ?? [] ), t ] );
	}

	const multiplier = ( t ) => t.extensions[ `${ NS }.unit` ];

	return [ ...groups.values() ].flatMap( ( siblings ) =>
		siblings.every( ( t ) => multiplier( t ) !== undefined )
			? [ ...siblings ].sort( ( a, b ) => multiplier( a ) - multiplier( b ) )
			: siblings
	);
}

export function buildCss( tokens ) {
	const items = flatten( tokens );
	const base = sortByMultiplier( items.filter( ( t ) => t.path[ 0 ] !== 'color' ) );
	const colors = Object.fromEntries(
		MODES.map( ( mode ) => [
			mode,
			items.filter( ( t ) => t.path[ 0 ] === 'color' && t.path[ 1 ] === mode ),
		] )
	);

	const colorBlock = ( mode ) => [
		...colors[ mode ].map( ( t ) => declaration( [ 'color', ...t.path.slice( 2 ) ].join( '-' ), t.value ) ),
		`\tcolor-scheme: ${ mode };`,
	];

	const lineOpacity = tokens.line.opacity.$value;
	const lineColor = `color-mix(in srgb, var(--color-muted) ${ formatNumber( lineOpacity * 100 ) }%, transparent)`;

	const dark = colorBlock( 'dark' ).map( ( l ) => `\t${ l }` ).join( '\n' );

	return `/* Generated from tokens/tokens.json by scripts/build-tokens.mjs. Do not edit. */

:root {
${ base.map( ( t ) => declaration( t.path.join( '-' ), formatValue( t ) ) ).join( '\n' ) }
${ declaration( 'color-line', lineColor ) }
${ colorBlock( 'light' ).join( '\n' ) }
}

@media (prefers-color-scheme: dark) {
	:root:not([data-theme="light"]) {
${ dark }
	}
}

:root[data-theme="dark"] {
${ colorBlock( 'dark' ).join( '\n' ) }
}
`;
}

function main() {
	const css = buildCss( readTokens() );

	if ( process.argv.includes( '--check' ) ) {
		let current = '';
		try {
			current = readFileSync( CSS_PATH, 'utf8' );
		} catch {}
		if ( current !== css ) {
			console.error( 'tokens.css is out of date. Run: npm run build:tokens' );
			process.exit( 1 );
		}
		console.log( 'tokens.css is up to date.' );
		return;
	}

	mkdirSync( dirname( CSS_PATH ), { recursive: true } );
	writeFileSync( CSS_PATH, css );
	console.log( `Wrote ${ CSS_PATH.replace( `${ ROOT }/`, '' ) }` );
}

if ( process.argv[ 1 ] === fileURLToPath( import.meta.url ) ) {
	main();
}
