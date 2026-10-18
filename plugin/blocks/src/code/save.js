/**
 * Saved markup for Code with file name. Changing it later needs a
 * deprecation entry, or posts saved with the old markup turn invalid.
 *
 * The code scrolls sideways instead of wrapping; tabIndex lets keyboard users
 * scroll it too.
 */

import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const { filename, content, caption } = attributes;

	return (
		<figure { ...useBlockProps.save( { className: 'code-a' } ) }>
			{ filename && <div className="code-a__filename">{ filename }</div> }
			<pre className="code-a__pre" tabIndex="0">
				<code>{ content }</code>
			</pre>
			{ ! RichText.isEmpty( caption ) && (
				<RichText.Content tagName="figcaption" className="code-a__caption" value={ caption } />
			) }
		</figure>
	);
}
