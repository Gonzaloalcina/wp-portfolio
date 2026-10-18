/**
 * Saved markup for Aside. Changing it later needs a deprecation entry, or
 * posts saved with the old markup turn invalid.
 *
 * Inside the note's <article>, an <aside> is not a page landmark, so it
 * needs no accessible name; the label is read first anyway.
 */

import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { label } = attributes;

	return (
		<aside { ...useBlockProps.save( { className: 'aside-a' } ) }>
			{ label && <p className="aside-a__label">{ label }</p> }
			<div { ...useInnerBlocksProps.save( { className: 'aside-a__content' } ) } />
		</aside>
	);
}
