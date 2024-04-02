import { useRef, useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {changeHostName, closeModal, getMyClubGroups, setHeight, showMemberModal} from "../shared/edit-functions";

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
	const modalRef = useRef(null);
	const ref = useRef(null);
	const selectPostLabel = {
		label: __( 'Select a group', 'myclub-groups' ),
		value: ''
	};
	const labels = {
		role: __( 'Role', 'myclub-groups' ),
		age: __( 'Age', 'myclub-groups' ),
		email: __( 'E-mail', 'myclub-groups' ),
		phone: __( 'Phone', 'myclub-groups' )
	};

	useEffect(() => {
		setPostMembers( {
			...postMembers,
			loaded: false
		});

		if (attributes.post_id) {
			apiFetch({ path: `/myclub/v1/groups/${attributes.post_id}`})
				.then((post) => {
					const allMembers = JSON.parse(post.members);

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
	}, [attributes.post_id]);

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
		apiFetch( { path: '/myclub/v1/options' } ).then(options => {
			setMemberTitle ( options.myclub_groups_members_title );
		} );

		getMyClubGroups( setPosts, selectPostLabel );
	}, []);

	if (postMembers && postMembers.members && postMembers.members.length) {
		memberOutput =
			<div className="myclub-groups-members-list">
				<div className="myclub-groups-members-container">
					<h3 className="myclub-groups-header">{memberTitle}</h3>
					<div ref={ref} className="members-list">
						{postMembers.members.slice(0, 8).map((member) => {
							return (
								<div className="member" onClick={() => showMemberModal(modalRef, member, labels)}>
									<div className="member-picture">
										{ member.member_image && <img src={changeHostName(member.member_image.url)} alt={member.name}/> }
									</div>
									<div className="member-name">
										{member.name}
									</div>
								</div>
							)
						})}
					</div>
				</div>
				<div className="member-modal" ref={modalRef}>
					<div className="modal-content">
						<span className="close" onClick={() => closeModal(modalRef)}>&times;</span>
						<div className="modal-body">
							<div className="image"></div>
							<div className="information"></div>
						</div>
					</div>
				</div>
			</div>
	} else {
		memberOutput = <div>{__('No members found', 'myclub-groups')}</div>
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Content settings', 'myclub-groups')}>
					<PanelRow>
						{posts.length ?
							<SelectControl
								label={__('Group', 'myclub-groups')}
								value={attributes.post_id }
							options={ posts }
							onChange={ ( value ) => {
								setAttributes({post_id: value});
							} }
						/> : <Spinner /> }
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				{ attributes.post_id && !postMembers.loaded && <Spinner /> }
				{ (postMembers.loaded || !attributes.post_id) && memberOutput }
			</div>
		</>
	);
}
