/**
 * Design token rules (see docs/design-brief.md).
 */

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { CSS_PATH, DATA_PATH, buildCss, buildData, flatten, readTokens } from '../../scripts/build-tokens.mjs';

const NS = 'com.gonzaloalcina';
const tokens = readTokens();
const items = flatten( tokens );
const unitRem = parseFloat( tokens.unit.$value );
const dimensions = items.filter( ( t ) => t.type === 'dimension' && t.path.join( '.' ) !== 'unit' );
const name = ( t ) => t.path.join( '.' );

test( 'unit is 0.5rem (8px at the default root size)', () => {
	assert.equal( tokens.unit.$value, '0.5rem' );
} );

test( 'only hairlines use px; every other dimension uses rem', () => {
	for ( const t of dimensions ) {
		const unit = `${ NS }.unit-exception` in t.extensions ? 'px' : 'rem';
		assert.ok( t.value.endsWith( unit ), `${ name( t ) }: ${ t.value } should be in ${ unit }` );
	}
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
		assert.equal( parseFloat( t.value ), multiplier * unitRem, `${ name( t ) }: ${ t.value } ≠ ${ multiplier } × ${ unitRem }rem` );
	}
} );

test( 'multipliers are steps of 0.5 (font sizes: 0.25)', () => {
	for ( const t of dimensions.filter( ( d ) => `${ NS }.unit` in d.extensions ) ) {
		const multiplier = t.extensions[ `${ NS }.unit` ];
		const step = t.path[ 1 ] === 'size' ? 0.25 : 0.5;
		assert.ok( Number.isInteger( multiplier / step ), `${ name( t ) }: ${ multiplier } is not a step of ${ step }` );
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

test( 'committed tokens.json for the theme is up to date', () => {
	assert.equal( readFileSync( DATA_PATH, 'utf8' ), buildData( tokens ), 'Run: npm run build:tokens' );
} );
