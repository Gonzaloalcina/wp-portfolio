/**
 * Pre-publish check for notes: the sitemap rule is that every note links to
 * one service. A warning in the publish panel, not a lock: the editor stays
 * in charge.
 */

import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { PluginPrePublishPanel, store as editorStore } from '@wordpress/editor';

export default function RelatedCheck() {
	const missing = useSelect( ( select ) => {
		if ( 'post' !== select( editorStore ).getCurrentPostType() ) {
			return false;
		}

		const { getBlocksByName, getBlockAttributes } = select( blockEditorStore );
		const [ clientId ] = getBlocksByName( 'portfolio-core/related' );

		return ! clientId || ! getBlockAttributes( clientId )?.serviceId;
	}, [] );

	if ( ! missing ) {
		return null;
	}

	return (
		<PluginPrePublishPanel title={ __( 'Related service', 'portfolio-core' ) } initialOpen>
			<p>
				{ __( 'This note does not link to a service yet. Add a Related block and choose one.', 'portfolio-core' ) }
			</p>
		</PluginPrePublishPanel>
	);
}
