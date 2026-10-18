/**
 * Code with file name: a static block (the markup is saved in the post), so
 * it needs no PHP and no JavaScript on the front end. Converts to and from
 * core's Code block.
 */

import { registerBlockType, createBlock } from '@wordpress/blocks';
import { create, toHTMLString } from '@wordpress/rich-text';

import metadata from './block.json';
import Edit from './edit.js';
import save from './save.js';
import './style.css';
import './editor.css';

registerBlockType( metadata.name, {
	edit: Edit,
	save,
	transforms: {
		// core/code stores HTML (entities, line breaks); this block stores plain text.
		from: [
			{
				type: 'block',
				blocks: [ 'core/code' ],
				transform: ( { content } ) =>
					createBlock( metadata.name, { content: create( { html: String( content ?? '' ) } ).text } ),
			},
		],
		to: [
			{
				type: 'block',
				blocks: [ 'core/code' ],
				transform: ( { content } ) =>
					createBlock( 'core/code', { content: toHTMLString( { value: create( { text: content } ) } ) } ),
			},
		],
	},
} );
