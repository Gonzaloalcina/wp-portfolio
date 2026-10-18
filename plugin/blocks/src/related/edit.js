/**
 * Editor for the Related block: two searchable selects fed by the plugin's
 * REST route, which lists only what the site may show (published services,
 * published projects whose client may be named).
 */

import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { ComboboxControl, Notice, Spinner } from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const toOptions = ( items ) =>
	items.map( ( item ) => ( { value: String( item.id ), label: item.title } ) );

export default function Edit( { attributes, setAttributes } ) {
	const { serviceId, projectId } = attributes;
	const [ data, setData ] = useState( null );
	const [ failed, setFailed ] = useState( false );

	useEffect( () => {
		apiFetch( { path: '/portfolio-core/v1/related-options' } )
			.then( setData )
			.catch( () => setFailed( true ) );
	}, [] );

	const blockProps = useBlockProps( { className: 'related-a' } );

	return (
		<div { ...blockProps }>
			<p className="related-a__label">{ __( 'Related', 'portfolio-core' ) }</p>

			{ failed && (
				<Notice status="error" isDismissible={ false }>
					{ __( 'Services and projects could not be loaded.', 'portfolio-core' ) }
				</Notice>
			) }

			{ ! data && ! failed && <Spinner /> }

			{ data && (
				<>
					<ComboboxControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Service', 'portfolio-core' ) }
						help={ __( 'Every note links to one service.', 'portfolio-core' ) }
						value={ serviceId ? String( serviceId ) : null }
						options={ toOptions( data.services ) }
						onChange={ ( value ) => setAttributes( { serviceId: value ? Number( value ) : 0 } ) }
					/>
					<ComboboxControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						label={ __( 'Project (optional)', 'portfolio-core' ) }
						help={ __( 'Only projects whose client may be named are listed.', 'portfolio-core' ) }
						value={ projectId ? String( projectId ) : null }
						options={ toOptions( data.projects ) }
						onChange={ ( value ) => setAttributes( { projectId: value ? Number( value ) : 0 } ) }
					/>
				</>
			) }
		</div>
	);
}
