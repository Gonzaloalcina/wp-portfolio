/**
 * Design token rules (see docs/design-brief.md).
 */

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { CSS_PATH, buildCss, flatten, readTokens } from '../../scripts/build-tokens.mjs';

const NS = 'com.gonzaloalcina';
const tokens = readTokens();
const items = flatten( tokens );
const unitPx = parseFloat( tokens.unit.$value );
const dimensions = items.filter( ( t ) => t.type === 'dimension' && t.path.join( '.' ) !== 'unit' );
const name = ( t ) => t.path.join( '.' );

test( 'unit is 8px', () => {
	assert.equal( tokens.unit.$value, '8px' );
} );

test( 'every dimension has a unit multiplier or a declared exception', () => {
	for ( const t of dimensions ) {
		const hasMultiplier = `${ NS }.unit` in t.extensions;
		const hasException = `${ NS }.unit-exception` in t.extensions;
		assert.ok( hasMultiplier !== hasException, `${ name( t ) } needs exactly one of unit / unit-exception` );
	}
} );

test( 'every dimension value equals multiplier × unit', () => {
	for ( const t of dimensions.filter( ( d ) => `${ NS }.unit` in d.extensions ) ) {
		const multiplier = t.extensions[ `${ NS }.unit` ];
		assert.equal( t.value, `${ multiplier * unitPx }px`, `${ name( t ) }: ${ t.value } ≠ ${ multiplier } × ${ unitPx }px` );
	}
} );

test( 'multipliers are steps of 0.5', () => {
	for ( const t of dimensions.filter( ( d ) => `${ NS }.unit` in d.extensions ) ) {
		const multiplier = t.extensions[ `${ NS }.unit` ];
		assert.ok( Number.isInteger( multiplier * 2 ), `${ name( t ) }: ${ multiplier } is not a step of 0.5` );
	}
} );

test( 'line heights are whole units', () => {
	for ( const t of dimensions.filter( ( d ) => d.path[ 1 ] === 'line-height' ) ) {
		assert.ok( Number.isInteger( t.extensions[ `${ NS }.unit` ] ), `${ name( t ) } is not a whole unit` );
	}
} );

test( 'light and dark define the same colours', () => {
	assert.deepEqual( Object.keys( tokens.color.light ).sort(), Object.keys( tokens.color.dark ).sort() );
} );

test( 'committed tokens.css is up to date', () => {
	assert.equal( readFileSync( CSS_PATH, 'utf8' ), buildCss( tokens ), 'Run: npm run build:tokens' );
} );
