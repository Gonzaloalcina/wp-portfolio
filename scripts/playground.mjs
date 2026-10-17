/**
 * Runs blueprint.json locally with WordPress Playground CLI, using the theme
 * from this checkout instead of the one on GitHub.
 *
 * The public blueprint installs the theme from the repository (git:directory),
 * which only works once the repo is public and only shows what is pushed. This
 * script swaps that step for a mount of ./theme, so local changes show up
 * live, and runs the same remaining steps.
 *
 * Usage (Playground CLI needs Node 24+):
 *   nvm exec 24 npm run playground
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

if ( Number( process.versions.node.split( '.' )[ 0 ] ) < 24 ) {
	console.error( `Playground CLI needs Node 24+ (this is ${ process.version }). Run: nvm exec 24 npm run playground` );
	process.exit( 1 );
}

const blueprint = JSON.parse( readFileSync( join( ROOT, 'blueprint.json' ), 'utf8' ) );
const php = blueprint.preferredVersions?.php ?? '8.0';

// Replace the GitHub install with activating the mounted local theme.
blueprint.steps = blueprint.steps.map( ( step ) =>
	'installTheme' === step.step ? { step: 'activateTheme', themeFolderName: THEME } : step
);

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
];

console.log( `Playground on http://127.0.0.1:${ PORT } (PHP ${ php }, theme mounted from ./theme)` );
spawn( 'npx', args, { stdio: 'inherit' } ).on( 'exit', ( code ) => process.exit( code ?? 0 ) );
