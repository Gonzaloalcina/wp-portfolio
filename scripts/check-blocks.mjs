/**
 * Checks the committed block build (plugin/blocks/build/) against a fresh
 * build of plugin/blocks/src/, and the block CSS against the unit rule.
 *
 * The fresh build goes to a temporary folder, so the check never touches the
 * repository. Exit 1 if any file differs, is missing or is extra, or if a
 * source stylesheet hard-codes px (1px hairlines excepted).
 *
 * Usage:
 *   npm run check:blocks
 */

import { execFileSync } from 'node:child_process';
import { mkdtempSync, readFileSync, readdirSync, rmSync, statSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, relative, resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = resolve( dirname( fileURLToPath( import.meta.url ) ), '..' );
const SRC = join( ROOT, 'plugin/blocks/src' );
const BUILD = join( ROOT, 'plugin/blocks/build' );
const BIN = join( ROOT, 'node_modules/.bin/wp-scripts' );
const PX = /(?<![\w.-])(?!1px)\d*\.?\d+px\b/g;

const files = ( dir ) =>
	readdirSync( dir, { recursive: true } )
		.map( ( file ) => join( dir, file ) )
		.filter( ( file ) => statSync( file ).isFile() )
		.map( ( file ) => relative( dir, file ) )
		.sort();

const problems = [];

// Unit rule: same check as tests/css for the theme.
for ( const file of files( SRC ).filter( ( name ) => name.endsWith( '.css' ) ) ) {
	const offenders = readFileSync( join( SRC, file ), 'utf8' ).match( PX );
	if ( offenders ) {
		problems.push( `plugin/blocks/src/${ file }: hard-coded ${ offenders.join( ', ' ) }` );
	}
}

// Fresh build, compared file by file. The manifest's paths are relative to
// its folder, so it is identical wherever it is built.
const tmp = mkdtempSync( join( tmpdir(), 'wp-portfolio-blocks-' ) );
try {
	const run = ( ...args ) => execFileSync( BIN, args, { cwd: ROOT, stdio: [ 'ignore', 'ignore', 'inherit' ] } );
	run( 'build', '--webpack-src-dir=plugin/blocks/src', `--output-path=${ tmp }` );
	run( 'build-blocks-manifest', `--input=${ tmp }`, `--output=${ join( tmp, 'blocks-manifest.php' ) }` );

	const fresh = files( tmp );
	const committed = files( BUILD );

	for ( const file of new Set( [ ...fresh, ...committed ] ) ) {
		if ( ! committed.includes( file ) ) {
			problems.push( `plugin/blocks/build/${ file }: missing` );
		} else if ( ! fresh.includes( file ) ) {
			problems.push( `plugin/blocks/build/${ file }: not produced by the build` );
		} else if ( ! readFileSync( join( tmp, file ) ).equals( readFileSync( join( BUILD, file ) ) ) ) {
			problems.push( `plugin/blocks/build/${ file }: out of date` );
		}
	}
} finally {
	rmSync( tmp, { recursive: true, force: true } );
}

if ( problems.length ) {
	console.error( `${ problems.join( '\n' ) }\n\nRun npm run build:blocks and commit plugin/blocks/.` );
	process.exit( 1 );
}

console.log( 'Block build up to date; block CSS follows the unit rule.' );
