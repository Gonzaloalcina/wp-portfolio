/**
 * Builds theme/assets/css/tokens.css from tokens/tokens.json.
 *
 * Also writes theme/assets/data/tokens.json, a flat list the /design-system
 * page renders, so the page never goes out of sync with the tokens.
 *
 * Usage:
 *   node scripts/build-tokens.mjs           Write the generated files.
 *   node scripts/build-tokens.mjs --check   Exit 1 if any generated file is out of date.
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
export const DATA_PATH = resolve( ROOT, 'theme/assets/data/tokens.json' );

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

function cssName( token ) {
	return token.path[ 0 ] === 'color'
		? [ 'color', ...token.path.slice( 2 ) ].join( '-' )
		: token.path.join( '-' );
}

function lineColor( tokens ) {
	const opacity = tokens.line.opacity.$value;
	return `color-mix(in srgb, var(--color-muted) ${ formatNumber( opacity * 100 ) }%, transparent)`;
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
		...colors[ mode ].map( ( t ) => declaration( cssName( t ), t.value ) ),
		`\tcolor-scheme: ${ mode };`,
	];

	const dark = colorBlock( 'dark' ).map( ( l ) => `\t${ l }` ).join( '\n' );

	return `/* Generated from tokens/tokens.json by scripts/build-tokens.mjs. Do not edit. */

:root {
${ base.map( ( t ) => declaration( cssName( t ), formatValue( t ) ) ).join( '\n' ) }
${ declaration( 'color-line', lineColor( tokens ) ) }
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

/**
 * Flat data for the /design-system page. Colours are paired by name with
 * their light and dark values.
 */
export function buildData( tokens ) {
	const items = flatten( tokens );
	const base = sortByMultiplier( items.filter( ( t ) => t.path[ 0 ] !== 'color' ) );
	const multiplier = ( t ) => t.extensions[ `${ NS }.unit` ] ?? null;

	const data = {
		$description: 'Generated from tokens/tokens.json by scripts/build-tokens.mjs. Do not edit.',
		tokens: base.map( ( t ) => ( {
			path: t.path.join( '.' ),
			var: `--${ cssName( t ) }`,
			type: t.type,
			value: t.value,
			css: formatValue( t ),
			multiplier: multiplier( t ),
		} ) ),
		colors: Object.keys( tokens.color.light ).map( ( name ) => ( {
			name,
			var: `--color-${ name }`,
			light: tokens.color.light[ name ].$value,
			dark: tokens.color.dark[ name ].$value,
		} ) ),
		derived: [ { var: '--color-line', css: lineColor( tokens ) } ],
	};

	return `${ JSON.stringify( data, null, 2 ) }\n`;
}

const OUTPUTS = [
	{ path: CSS_PATH, build: buildCss },
	{ path: DATA_PATH, build: buildData },
];

function main() {
	const tokens = readTokens();
	const check = process.argv.includes( '--check' );
	let stale = false;

	for ( const { path, build } of OUTPUTS ) {
		const content = build( tokens );
		const relative = path.replace( `${ ROOT }/`, '' );

		if ( check ) {
			let current = '';
			try {
				current = readFileSync( path, 'utf8' );
			} catch {}
			if ( current !== content ) {
				console.error( `${ relative } is out of date. Run: npm run build:tokens` );
				stale = true;
			}
			continue;
		}

		mkdirSync( dirname( path ), { recursive: true } );
		writeFileSync( path, content );
		console.log( `Wrote ${ relative }` );
	}

	if ( stale ) {
		process.exit( 1 );
	}
	if ( check ) {
		console.log( 'Generated files are up to date.' );
	}
}

if ( process.argv[ 1 ] === fileURLToPath( import.meta.url ) ) {
	main();
}
