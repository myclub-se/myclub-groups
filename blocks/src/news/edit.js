import { useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, Spinner } from '@wordpress/components';
import ServerSideRender from "@wordpress/server-side-render";
import {__} from "@wordpress/i18n";
import {getMyClubGroups} from "../shared/edit-functions";

/**
 * The edit function required to handle the news component. Adds a post chooser to the settings and updates the block
 * which is rendered by the backend.
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
		// Get all myclub group posts.
		getMyClubGroups( setPosts, selectPostLabel );
	}, []);

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
			<div { ...useBlockProps() }>
				<ServerSideRender block="myclub-groups/news" attributes={{ postId: attributes.postId }} />
			</div>
		</>
	);
}
