import { useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, Spinner } from '@wordpress/components';
import {__} from "@wordpress/i18n";
import {getMyClubGroups} from "../shared/edit-functions";

/**
 * The edit function required to handle the title component.
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const [posts, setPosts] = useState([]);
	const [postData, setPostData] = useState({
		contactPerson: '',
		email: '',
		infoText: '',
		phone: '',
		title: ''
	});
	const [ showImage, setShowImage ] = useState(true);
	const {apiFetch} = wp;
	const {useSelect} = wp.data;
	const featuredImageId = useSelect((select) => {
		return attributes.postId && select('core').getEntityRecord('postType', 'myclub-groups', attributes.postId)?.featured_media;
	}, [ attributes.postId ]);

	const featuredImage = useSelect((select) => {
		return featuredImageId && select('core').getMedia(featuredImageId);
	}, [ featuredImageId ]);
	const mediumImage = featuredImage?.media_details?.sizes?.medium?.source_url;
	const selectPostLabel = {
		label: __( 'Select a group', 'myclub-groups' ),
		value: ''
	};

	useEffect(() => {
		if (attributes.postId) {
			apiFetch( { path: `/myclub/v1/groups/${attributes.postId}` } )
				.then( ( post ) => {
					setPostData( {
						contactName: post.contactName,
						email: post.email,
						infoText: post.infoText,
						phone: post.phone,
						title: post.title
					} );
				} );
		}
	}, [ attributes.postId ])

	useEffect(() => {
		getMyClubGroups( setPosts, selectPostLabel );
		apiFetch( { path: '/myclub/v1/options' } )
			.then( options => {
				setShowImage( options.myclub_groups_page_picture === '1' );
			} );
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
				{postData?.title ?
					<div className="myclub-groups-title">
						<div className="myclub-groups-title-box">
							{ showImage && featuredImage !== 0 && featuredImage?.source_url &&
							<div className="myclub-groups-title-image">
								<img src={mediumImage ? mediumImage : featuredImage?.source_url} alt={postData?.title}/>
							</div>}
							<div className="myclub-groups-title-information">
								<div className={`myclub-groups-title-name ${postData?.infoText ? 'with-info-text' : ''}`}>{postData?.title}</div>
								{postData?.infoText && <div className="myclub-groups-info-text">
									{postData.infoText}
								</div>}
								{postData?.contactName && <div className="myclub-groups-information">
									<div className="label">{__('Contact person', 'myclub-groups')}</div>
									<div className="value">{postData.contactName}</div>
								</div>}
								{postData?.phone && <div className="myclub-groups-information">
									<div className="label">{__('Telephone', 'myclub-groups')}</div>
									<div className="value"><a href={`tel:${postData.phone}`}>{postData.phone}</a></div>
								</div>}
								{postData?.email && <div className="myclub-groups-information">
									<div className="label">{__('E-mail', 'myclub-groups')}</div>
									<div className="value"><a href={`mailto:${postData.email}`}>{postData.email}</a>
									</div>
								</div>}
							</div>
						</div>
					</div> : <div className="myclub-groups-title">
						<div className="myclub-groups-title-box">{__('No group selected', 'myclub-groups')}</div>
					</div>
				}
			</div>
		</>
	);
}
