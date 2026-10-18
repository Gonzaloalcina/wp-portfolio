/**
 * Editor for Aside: an editable label (Note and Decision are variations of
 * it) and inner blocks limited to text, lists and code.
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps, useInnerBlocksProps, PlainText } from '@wordpress/block-editor';

const ALLOWED_BLOCKS = [ 'core/paragraph', 'core/list', 'core/code', 'portfolio-core/code' ];
const TEMPLATE = [ [ 'core/paragraph' ] ];

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps( { className: 'aside-a' } );
	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'aside-a__content' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE }
	);

	return (
		<aside { ...blockProps }>
			<div className="aside-a__label">
				<PlainText
					className="aside-a__input"
					value={ attributes.label }
					onChange={ ( value ) => setAttributes( { label: value.replace( /\s*\n\s*/g, ' ' ) } ) }
					placeholder={ __( 'Label', 'portfolio-core' ) }
					aria-label={ __( 'Label', 'portfolio-core' ) }
				/>
			</div>
			<div { ...innerBlocksProps } />
		</aside>
	);
}
