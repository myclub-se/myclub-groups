import { useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, Spinner } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render'
import {__} from "@wordpress/i18n";
import {getMyClubGroups} from "../shared/edit-functions";

/**
 * The edit function required to handle the navigation component. Adds the ability to select a post and renders the navigation bar.
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const [posts, setPosts] = useState([]);
	const selectPostLabel = {
		label: __( 'Select a group', 'myclub-groups' ),
		value: ''
	};

	useEffect(() => {
		getMyClubGroups( setPosts, selectPostLabel );
	}, []);

	console.log(attributes);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Content settings', 'myclub-groups' ) }>
					<PanelRow>
						{ posts.length ?
							<SelectControl
								label={ __('Group', 'myclub-groups') }
								value={ attributes.postId }
								options={ posts }
								onChange={ ( value ) => {
									setAttributes({postId: value});
								} }
							/> : <Spinner /> }
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				{ attributes.postId ? <ServerSideRender block="myclub-groups/navigation" attributes={attributes} /> : <div className="myclub-groups-navigation">
					<div className="no-group-selected">
						{__( 'No group selected', 'myclub-groups' )}
					</div>
				</div> }
			</div>
		</>
	);
}
