import { useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, Spinner } from '@wordpress/components';
import {__} from "@wordpress/i18n";
import ServerSideRender from "@wordpress/server-side-render";
import {getMyClubGroups} from "../shared/edit-functions";

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
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

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Content settings', 'myclub-groups' ) }>
					<PanelRow>
						{ posts.length ?
						<SelectControl
							label={ __('Group', 'myclub-groups') }
							value={ attributes.post_id }
							options={ posts }
							onChange={ ( value ) => {
								setAttributes({post_id: value});
							} }
						/> : <Spinner />
						}
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>
				{ attributes.post_id ? <ServerSideRender block="myclub-groups/coming-games" attributes={attributes} /> : <div className="myclub-groups-coming-games">
					<div className="no-group-selected">
						{__( 'No group selected', 'myclub-groups' )}
					</div>
				</div> }
			</div>
		</>
	);
}
