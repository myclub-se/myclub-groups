import { useState, useEffect, useRef } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl } from '@wordpress/components';
import { addQueryArgs } from '@wordpress/url';
import './editor.scss';
import {__} from "@wordpress/i18n";

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const calendarRef = useRef(null);
	const [postEvents, setPostEvents] = useState({events: [], loaded: false});
	const [posts, setPosts] = useState([]);
	const {apiFetch} = wp;

	const renderEventContent = (eventInfo) => {
		return (
			<>
			<div className="myclub-groups-event-time">{eventInfo.timeText}</div>
			<div className="myclub-groups-event-title">{eventInfo.event.title}</div>
			</>
		)
	}

	useEffect(() => {
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

	useEffect(() => {
		setPostEvents({
			...postEvents,
			loaded: false
		});

		if (attributes.postId) {
			apiFetch({ path: `/myclub/v1/groups/${attributes.postId}`})
				.then((post) => {
					const allActivities = JSON.parse(post.activities);
					let backgroundColor = '#9e8c39';
					const events = allActivities.map((activity) => {
						switch (activity.base_type) {
							case 'match':
								backgroundColor = '#c1272d';
								break;
							case 'training':
								backgroundColor = '#009245';
								break;
							case 'meeting':
								backgroundColor = '#396b9e';
								break;
						}

						return {
							title: activity.title,
							start: `${activity.day} ${activity.start_time}`,
							end: `${activity.day} ${activity.end_time}`,
							backgroundColor,
							borderColor: backgroundColor,
							color: '#fff',
							display: 'block',
							extendedProps: {
								base_type: activity.base_type,
								location: activity.location,
								description: activity.description,
								endTime: activity.end_time,
								startTime: activity.start_time,
								type: activity.type
							}
						}
					});

					setPostEvents({
						events,
						loaded: true
					});
				});
		} else {
			setPostEvents({
				events: [],
				loaded: true
			});
		}
	}, [attributes.postId]);

	useEffect(() => {
		setTimeout(() => {
			const calendarEl = calendarRef?.current;

			if ( !calendarEl ) {
				return;
			}

			let calendar = new Calendar(calendarEl, {
				plugins: [ dayGridPlugin, timeGridPlugin, listPlugin ],
				initialView: 'dayGridMonth',
				headerToolbar: {
					left: 'prev,next today',
					center: 'title',
					right: 'dayGridMonth,timeGridWeek,listMonth'
				},
				events: postEvents.events,
			});
			calendar.render();

		}, 1)
	}, [postEvents]);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Content settings', 'myclub-groups' ) }>
					<PanelRow>
						<SelectControl
							label={ __('Group', 'myclub-groups') }
							value={ attributes.postId }
							options={ posts }
							onChange={ ( value ) => {
								setAttributes({postId: value});
							} }
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<div ref={calendarRef}></div>
			</div>
		</>
	);
}
