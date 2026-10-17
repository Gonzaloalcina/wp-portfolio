/**
 * Builds the theme stylesheets: resolves @import and writes one file per
 * entry to theme/assets/build/. No dependencies.
 *
 * Sources live in theme/assets/css/: one file per component in components/,
 * plus fonts, tokens and base. An entry lists what a stylesheet contains with
 * @import; the build inlines them, so the browser makes one request.
 *
 * Entries:
 * - site.css: everything every page uses, cached once for the whole site.
 * - design-system.css: rules only the /design-system page needs.
 *
 * Usage:
 *   node scripts/build-css.mjs           Write the generated files.
 *   node scripts/build-css.mjs --check   Exit 1 if any generated file is out of
 *                                        date, or a component is not imported.
 *
 * Rules:
 * - Only local @import "file.css"; is supported, at the top of a file. Each
 *   file is inlined once, the first time it is imported.
 * - Relative url() paths are rewritten so they still point to the same file
 *   from the build folder.
 * - Comments and indentation are dropped; gzip does the rest.
 */

import { existsSync, readFileSync, writeFileSync, mkdirSync, readdirSync } from 'node:fs';
import { dirname, relative, resolve, posix, sep } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = resolve( dirname( fileURLToPath( import.meta.url ) ), '..' );
export const SRC_DIR = resolve( ROOT, 'theme/assets/css' );
export const BUILD_DIR = resolve( ROOT, 'theme/assets/build' );
export const COMPONENTS_DIR = resolve( SRC_DIR, 'components' );
export const ENTRIES = [ 'site.css', 'design-system.css' ];

const IMPORT = /@import\s+(?:url\()?\s*["']([^"']+)["']\s*\)?\s*;/g;
const URL = /url\(\s*(["']?)([^"')]+)\1\s*\)/g;

const toPosix = ( path ) => path.split( sep ).join( posix.sep );

/**
 * Inlines a file and its imports, depth first. Returns the CSS and the list
 * of files it contains.
 */
function inline( file, seen ) {
	if ( seen.has( file ) ) {
		return '';
	}
	seen.add( file );

	const css = readFileSync( file, 'utf8' );

	const withUrls = css.replace( URL, ( match, quote, url ) => {
		if ( /^(?:[a-z]+:|\/|#)/i.test( url ) ) {
			return match;
		}
		const target = resolve( dirname( file ), url );
		return `url(${ quote }${ toPosix( relative( BUILD_DIR, target ) ) }${ quote })`;
	} );

	return withUrls.replace( IMPORT, ( _, path ) => inline( resolve( dirname( file ), path ), seen ) );
}

function minify( css ) {
	return css
		.replace( /\/\*[\s\S]*?\*\//g, '' )
		.split( '\n' )
		.map( ( line ) => line.trim() )
		.filter( Boolean )
		.join( '\n' );
}

/**
 * Builds every entry. Returns [{ entry, path, css, files }].
 */
export function build() {
	return ENTRIES.map( ( entry ) => {
		const seen = new Set();
		const source = resolve( SRC_DIR, entry );
		const body = minify( inline( source, seen ) );
		const header = `/* Generated from theme/assets/css/${ entry } by scripts/build-css.mjs. Do not edit. */`;

		return {
			entry,
			path: resolve( BUILD_DIR, entry ),
			css: `${ header }\n${ body }\n`,
			files: [ ...seen ],
		};
	} );
}

/**
 * Component files that no entry imports. A component that is not in a
 * stylesheet renders unstyled, so this must be empty.
 */
export function missingComponents( results = build() ) {
	const included = new Set( results.flatMap( ( result ) => result.files ) );

	if ( ! existsSync( COMPONENTS_DIR ) ) {
		return [];
	}

	return readdirSync( COMPONENTS_DIR )
		.filter( ( name ) => name.endsWith( '.css' ) )
		.map( ( name ) => resolve( COMPONENTS_DIR, name ) )
		.filter( ( file ) => ! included.has( file ) )
		.map( ( file ) => toPosix( relative( ROOT, file ) ) );
}

if ( process.argv[ 1 ] === fileURLToPath( import.meta.url ) ) {
	const results = build();
	const missing = missingComponents( results );

	if ( missing.length ) {
		console.error( `Components not imported by any entry:\n  ${ missing.join( '\n  ' ) }` );
		process.exit( 1 );
	}

	if ( process.argv.includes( '--check' ) ) {
		const stale = results.filter( ( result ) => {
			try {
				return readFileSync( result.path, 'utf8' ) !== result.css;
			} catch {
				return true;
			}
		} );

		if ( stale.length ) {
			console.error( `Out of date: ${ stale.map( ( result ) => result.entry ).join( ', ' ) }. Run npm run build:css.` );
			process.exit( 1 );
		}
		console.log( 'CSS build up to date.' );
	} else {
		mkdirSync( BUILD_DIR, { recursive: true } );
		for ( const result of results ) {
			writeFileSync( result.path, result.css );
			console.log( `Wrote ${ toPosix( relative( ROOT, result.path ) ) } (${ result.files.length } files).` );
		}
	}
}
