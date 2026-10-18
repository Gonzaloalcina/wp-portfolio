/**
 * Related: one service and, optionally, one project, rendered in PHP from
 * the current data (render.php). Also registers the pre-publish check for
 * the sitemap rule "every note links to one service".
 */

import { registerBlockType } from '@wordpress/blocks';
import { registerPlugin } from '@wordpress/plugins';

import metadata from './block.json';
import Edit from './edit.js';
import RelatedCheck from './check.js';
import './style.css';
import './editor.css';

registerBlockType( metadata.name, {
	edit: Edit,
	// Dynamic block: nothing is saved but the attributes.
	save: () => null,
} );

registerPlugin( 'portfolio-core-related-check', { render: RelatedCheck } );
