/**
 * Editor for Code with file name: the file name and the code as plain text
 * (no formatting inside code), the caption as rich text.
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps, PlainText, RichText } from '@wordpress/block-editor';

export default function Edit( { attributes, setAttributes } ) {
	const { filename, content, caption } = attributes;
	const blockProps = useBlockProps( { className: 'code-a' } );

	return (
		<figure { ...blockProps }>
			<PlainText
				className="code-a__filename"
				value={ filename }
				onChange={ ( value ) => setAttributes( { filename: value.replace( /\s*\n\s*/g, ' ' ) } ) }
				placeholder={ __( 'File name (optional)', 'portfolio-core' ) }
				aria-label={ __( 'File name', 'portfolio-core' ) }
			/>
			<pre className="code-a__pre">
				<PlainText
					className="code-a__input"
					value={ content }
					onChange={ ( value ) => setAttributes( { content: value } ) }
					placeholder={ __( 'Write code…', 'portfolio-core' ) }
					aria-label={ __( 'Code', 'portfolio-core' ) }
				/>
			</pre>
			<RichText
				tagName="figcaption"
				className="code-a__caption"
				value={ caption }
				onChange={ ( value ) => setAttributes( { caption: value } ) }
				placeholder={ __( 'Caption (optional)', 'portfolio-core' ) }
				aria-label={ __( 'Caption', 'portfolio-core' ) }
				allowedFormats={ [ 'core/bold', 'core/italic', 'core/link', 'core/code' ] }
			/>
		</figure>
	);
}
