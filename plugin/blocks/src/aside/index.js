/**
 * Aside: a label and a few blocks set apart from the text, between two 1px
 * lines. Static block: no PHP and no JavaScript on the front end.
 */

import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit.js';
import save from './save.js';
import './style.css';
import './editor.css';

registerBlockType( metadata.name, {
	edit: Edit,
	save,
} );
