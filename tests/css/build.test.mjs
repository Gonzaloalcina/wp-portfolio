import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';

import { build, missingComponents } from '../../scripts/build-css.mjs';

const results = build();

test( 'every component is imported by an entry', () => {
	assert.deepEqual( missingComponents( results ), [] );
} );

test( 'committed build files are up to date', () => {
	for ( const result of results ) {
		assert.equal( readFileSync( result.path, 'utf8' ), result.css, `${ result.entry } is out of date: run npm run build:css` );
	}
} );

test( 'build output has no @import left', () => {
	for ( const result of results ) {
		assert.doesNotMatch( result.css, /@import/, result.entry );
	}
} );

test( 'source CSS uses no hard-coded px except 1px hairlines', () => {
	for ( const result of results ) {
		const offenders = result.css.match( /(?<![\w.-])(?!1px)\d*\.?\d+px\b/g ) ?? [];
		assert.deepEqual( offenders, [], result.entry );
	}
} );
