import { useRef, useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, Spinner } from '@wordpress/components';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import {changeHostName, setHeight} from "../shared/edit-functions";

/**
 * The edit function required to handle the members component. Adds a settings field to choose the post to render
 * members for. Loads members and renders them in the block.
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const [postMembers, setPostMembers] = useState({members: [], leaders: [], loaded: false});
	const [posts, setPosts] = useState([]);
	const [memberTitle, setMemberTitle] = useState(__( 'Members', 'myclub-groups' ) );
	let memberOutput;
	const {apiFetch} = wp;
	const ref = useRef(null);

	useEffect(() => {
		setPostMembers( {
			...postMembers,
			loaded: false
		});

		if (attributes.postId) {
			apiFetch({ path: `/myclub/v1/groups/${attributes.postId}`})
				.then((post) => {
					const allMembers = JSON.parse(post.meta.members);

					setPostMembers( {
						...allMembers,
						loaded: true
					} );
				});
		} else {
			setPostMembers({
				members: [],
				leaders: [],
				loaded: true
			});
		}
	}, [attributes.postId]);

	useEffect(() => {
		setTimeout(() => {
			if (ref.current) {
				// Begin by setting the max height of the image on all members.
				setHeight( ref, 'member-picture' );

				// Set the max height of the name on all members.
				setHeight( ref, 'member-name' );
			}
		}, 100);
	}, [postMembers]);

	useEffect(() => {
		apiFetch( { path: '/myclub/v1/options' } ).then( settings => {
			setMemberTitle ( settings.myclub_groups_members_title );
		} );

		apiFetch( { path: '/myclub/v1/groups' } ).then(
			fetchedItems => {
				const postOptions = fetchedItems.map( post => ({
					label: post.title,
					value: post.id
				}));

				postOptions.unshift({
					label: __( 'Select a group', 'myclub-groups' ),
					value: ''
				});

				setPosts( postOptions );
			}
		);
	}, []);


	if (postMembers && postMembers.members && postMembers.members.length) {
		memberOutput =
			<div className="myclub-groups-members-list">
				<h3 className="myclub-groups-header">{memberTitle}</h3>
				<div ref={ref} className="members-list">
					{postMembers.members.slice(0, 8).map((member) => {
						return (
							<div className="member">
								<div className="member-picture">
									<img src={ changeHostName( member.member_image.url ) } alt={ member.name } />
								</div>
								<div className="member-name">
									{ member.name }
								</div>
							</div>
						)
					})}
				</div>
			</div>
	} else {
		memberOutput = <div>{ __( 'No members found', 'myclub-groups' ) }</div>
	}

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
				{ attributes.postId && !postMembers.loaded && <Spinner /> }
				{ (postMembers.loaded || !attributes.postId) && memberOutput }
			</div>
		</>
	);
}
