import { useRef, useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { changeHostName, closeModal, getMyClubGroups, setHeight, showMemberModal } from "../shared/edit-functions";

/**
 * The edit function required to handle the leaders component. Adds a settings field to choose the post to render
 * members for. Loads leaders and renders them in the block.
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const [postLeaders, setPostLeaders] = useState({members: [], leaders: [], loaded: false});
	const [posts, setPosts] = useState([]);
	const [leaderTitle, setLeaderTitle] = useState(__( 'Leaders', 'myclub-groups' ) );
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
		setPostLeaders( {
			...postLeaders,
			loaded: false
		});

		if (attributes.post_id) {
			apiFetch({ path: `/myclub/v1/groups/${attributes.post_id}`})
				.then((post) => {
					const allLeaders = JSON.parse(post.members);

					setPostLeaders( {
						...allLeaders,
						loaded: true
					} );
				});
		} else {
			setPostLeaders({
				members: [],
				leaders: [],
				loaded: true
			});
		}
	}, [attributes.post_id]);

	useEffect(() => {
		setTimeout(() => {
			if (ref.current) {
				// Begin by setting the max height of the image on all leaders.
				setHeight( ref, 'leader-picture' );

				// Set the max height of the name on all leaders.
				setHeight( ref, 'leader-name' );
			}
		}, 100);
	}, [postLeaders]);

	useEffect(() => {
		apiFetch( { path: '/myclub/v1/options' } ).then(options => {
			setLeaderTitle ( options.myclub_groups_leaders_title );
		} );

		getMyClubGroups( setPosts, selectPostLabel );
	}, []);

	if (postLeaders && postLeaders.members && postLeaders.members.length) {
		memberOutput =
			<div className="myclub-groups-leaders-list">
				<div className="myclub-groups-leaders-container">
					<h3 className="myclub-groups-header">{leaderTitle}</h3>
					<div ref={ref} className="leaders-list">
						{postLeaders.leaders.slice(0, 4).map((leader) => {
							leader.name = leader.name.replaceAll('u0022', '\"');
							if (leader.role) {
								leader.role = leader.role.replaceAll('u0022', '\"');
							}
							return (
								<div className="leader" onClick={() => showMemberModal(modalRef, leader, labels)}>
									<div className="leader-picture">
										{ leader.member_image && <img src={changeHostName(leader.member_image.url)} alt={leader.name}/> }
									</div>
									<div className="leader-name">
										{leader.name}
										<div className="leader-role">
											{leader.role}
										</div>
									</div>
								</div>
							)
						})}
					</div>
				</div>
				<div className="leader-modal" ref={modalRef}>
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
		memberOutput = <div>{__('No groups with leaders selected', 'myclub-groups')}</div>
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Content settings', 'myclub-groups')}>
					<PanelRow>
						{posts.length ?
							<SelectControl
								label={__('Group', 'myclub-groups')}
								value={ attributes.post_id }
								options={ posts }
								onChange={ ( value ) => {
									setAttributes({post_id: value});
								} }
							/> : <Spinner /> }
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				{ attributes.post_id && !postLeaders.loaded && <Spinner /> }
				{ (postLeaders.loaded || !attributes.post_id) && memberOutput }
			</div>
		</>
	);
}
