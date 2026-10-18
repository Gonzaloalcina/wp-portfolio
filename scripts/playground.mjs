/**
 * Runs blueprint.json locally with WordPress Playground CLI, using the theme
 * and plugin from this checkout instead of the ones on GitHub.
 *
 * The public blueprint installs the theme and the plugin from the repository
 * (git:directory), which only works once the repo is public and only shows
 * what is pushed. This script swaps those steps for mounts of ./theme and
 * ./plugin, so local changes show up live, and runs the same remaining steps.
 *
 * SEED=1 adds scripts/playground-seed.php as a last step: the phase-1 pages
 * and Lorem content, to preview the page templates. The public blueprint
 * does not include it.
 *
 * Usage (Playground CLI needs Node 24+):
 *   nvm exec 24 npm run playground
 *   SEED=1 PORT=9401 nvm exec 24 npm run playground
 */

import { readFileSync, writeFileSync, mkdtempSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { spawn } from 'node:child_process';

const CLI = '@wp-playground/cli@3.1.58';
const PORT = process.env.PORT ?? '9400';
const ROOT = resolve( dirname( fileURLToPath( import.meta.url ) ), '..' );
const THEME = 'portfolio-theme';
const PLUGIN = 'portfolio-core';
const SEED = '1' === process.env.SEED;

if ( Number( process.versions.node.split( '.' )[ 0 ] ) < 24 ) {
	console.error( `Playground CLI needs Node 24+ (this is ${ process.version }). Run: nvm exec 24 npm run playground` );
	process.exit( 1 );
}

const blueprint = JSON.parse( readFileSync( join( ROOT, 'blueprint.json' ), 'utf8' ) );
const php = blueprint.preferredVersions?.php ?? '8.0';

// Replace the GitHub installs with activating the mounted local copies.
blueprint.steps = blueprint.steps.map( ( step ) => {
	if ( 'installTheme' === step.step ) {
		return { step: 'activateTheme', themeFolderName: THEME };
	}
	if ( 'installPlugin' === step.step && 'plugin' === step.pluginData?.path ) {
		return { step: 'activatePlugin', pluginPath: `${ PLUGIN }/${ PLUGIN }.php` };
	}
	return step;
} );

if ( SEED ) {
	blueprint.steps.push( { step: 'runPHP', code: readFileSync( join( ROOT, 'scripts/playground-seed.php' ), 'utf8' ) } );
	blueprint.landingPage = '/';
}

const file = join( mkdtempSync( join( tmpdir(), 'wp-portfolio-playground-' ) ), 'blueprint.json' );
writeFileSync( file, JSON.stringify( blueprint, null, 2 ) );

// The CLI does not read preferredVersions, so the PHP version is passed explicitly.
const args = [
	'-y',
	CLI,
	'server',
	`--port=${ PORT }`,
	`--php=${ php }`,
	`--blueprint=${ file }`,
	`--mount=${ join( ROOT, 'theme' ) }:/wordpress/wp-content/themes/${ THEME }`,
	`--mount=${ join( ROOT, 'plugin' ) }:/wordpress/wp-content/plugins/${ PLUGIN }`,
];

console.log( `Playground on http://127.0.0.1:${ PORT } (PHP ${ php }, theme and plugin mounted from ./theme and ./plugin${ SEED ? ', seeded' : '' })` );
spawn( 'npx', args, { stdio: 'inherit' } ).on( 'exit', ( code ) => process.exit( code ?? 0 ) );
