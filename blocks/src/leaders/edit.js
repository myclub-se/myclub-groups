import { useRef, useState, useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl, Spinner } from '@wordpress/components';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';
import {changeHostName, setHeight} from "../shared/edit-functions";

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
	const ref = useRef(null);

	useEffect(() => {
		setPostLeaders( {
			...postLeaders,
			loaded: false
		});

		if (attributes.postId) {
			apiFetch({ path: `/myclub/v1/groups/${attributes.postId}`})
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
	}, [attributes.postId]);

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
		apiFetch( { path: '/myclub/v1/options' } ).then( settings => {
			setLeaderTitle ( settings.myclub_groups_leaders_title );
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


	if (postLeaders && postLeaders.members && postLeaders.members.length) {
		memberOutput =
			<div className="myclub-groups-leaders-list">
				<h3 className="myclub-groups-header">{leaderTitle}</h3>
				<div ref={ref} className="leaders-list">
					{postLeaders.leaders.slice(0, 4).map((leader) => {
						return (
							<div className="leader">
								<div className="leader-picture">
									<img src={ changeHostName( leader.member_image.url ) } alt={ leader.name } />
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
	} else {
		memberOutput = <div>{ __( 'No groups with leaders selected', 'myclub-groups' ) }</div>
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
				{ attributes.postId && !postLeaders.loaded && <Spinner /> }
				{ (postLeaders.loaded || !attributes.postId) && memberOutput }
			</div>
		</>
	);
}
