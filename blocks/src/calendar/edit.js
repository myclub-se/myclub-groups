import { useState, useEffect, useRef } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, PanelRow, SelectControl } from '@wordpress/components';
import './editor.scss';
import {__} from "@wordpress/i18n";

import {getMyClubGroups} from "../shared/edit-functions";
import FullCalendar from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {
	const [calendarTitle, setCalendarTitle] = useState('');
	const [postEvents, setPostEvents] = useState({events: [], loaded: false});
	const [posts, setPosts] = useState([]);
	const {apiFetch} = wp;
	const {useSelect} = wp.data;
	let calendarRef = useRef();
	let outerRef = useRef();
	const currentLocale = useSelect((select) => {
		if (select("core").getSite()) {
			const language = select('core').getSite().language.substring(0, 2);
			if (calendarRef && calendarRef.current) {
				const api = calendarRef.current.getApi();
				api.setOption('locale', language);
			}
			return language;
		}

		return 'sv';
	});
	const startOfWeek = useSelect((select) => {
		if (select("core").getSite()) {
			const startOfWeek = select('core').getSite().start_of_week;
			if (calendarRef && calendarRef.current) {
				const api = calendarRef.current.getApi();
				api.setOption('firstDay', startOfWeek);
			}
			return startOfWeek;
		}

		return 1;
	});
	const selectPostLabel = {
		label: __( 'Select a group', 'myclub-groups' ),
		value: ''
	};

	const options = {
		allDaySlot: false,
		buttonText: {
			today: __( 'today', 'myclub-groups' ),
			month: __( 'month', 'myclub-groups' ),
			week: __( 'week', 'myclub-groups' ),
			day: __( 'day', 'myclub-groups' ),
			list: __( 'list', 'myclub-groups' )
		},
		plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
		initialView: 'dayGridMonth',
		headerToolbar: {
			left: 'prev,next today',
			center: 'title',
			right: 'dayGridMonth,timeGridWeek,listMonth'
		},
		events: postEvents,
		locale: currentLocale ? currentLocale.substring(0, 2) : 'sv',
		firstDay: startOfWeek,
		eventClick: (arg) => {
			const item = arg.event;

			const modal = outerRef.current.getElementsByClassName('calendar-modal')[0];
			const content = modal.getElementsByClassName('modal-body')[0];
			const close = modal.getElementsByClassName('close')[0];

			let output = '<div class="name">' + item.extendedProps.type + '</div>';
			output += '<table>';

			output += `<tr><th>${ __( 'Calendar', 'myclub-groups' ) }</th><td>${item.extendedProps.calendar_name}</td></tr>`;
			output += `<tr><th>${ __( 'Name', 'myclub-groups' ) }</th><td>${item.title}</td></tr>`;
			output += `<tr><th>${ __( 'When', 'myclub-groups' ) }</th><td>${item.extendedProps.startTime.substring(0, 5)} - ${item.extendedProps.endTime.substring(0, 5)}</td></tr>`;
			output += `<tr><th>${ __( 'Location', 'myclub-groups' ) }</th><td>${item.extendedProps.location}</td></tr>`;
			if ( item.extendedProps.meetUpTime && item.extendedProps.meetUpTime !== item.extendedProps.startTime ) {
				output += `<tr><th>${ __( 'Gathering time', 'myclub-groups' ) }</th><td>${item.extendedProps.meetUpTime.substring(0, 5)}</td></tr>`;
			}
			if ( item.extendedProps.meetUpPlace ) {
				output += `<tr><th>${ __( 'Gathering location', 'myclub-groups' ) }</th><td>${item.extendedProps.meetUpPlace}</td></tr>`;
			}
			if ( item.extendedProps.description ) {
				output += `<tr><th>${ __( 'Information', 'myclub-groups' ) }</th><td>${item.extendedProps.description}</td></tr>`;
			}
			output += '</table>'

			content.innerHTML = output;

			modal.classList.add( 'modal-open' );
			modal.addEventListener( 'click', closeButtonListener );
			close.addEventListener( 'click', closeButtonListener );
		},
		eventContent: (arg) => {
			const item = arg.event;
			const element = document.createElement('div');
			let timeText = arg.timeText;
			element.classList.add('fc-event-title');
			element.classList.add( getColorClass ( item.extendedProps.base_type ) );

			if ( item.extendedProps.meetUpTime && item.extendedProps.meetUpTime !== item.extendedProps.startTime ) {
				if ( !timeText ) {
					timeText = item.extendedProps.startTime.substring(0, 5);
				}

				timeText += ` (${item.extendedProps.meetUpTime.substring(0, 5)})`;
			}

			element.innerHTML = '<div class="myclub-groups-event-time">' +
				timeText + '</div><div class="myclub-groups-event-title">' +
				item.title +
				'</div>';

			let arrayOfDomNodes = [
				element
			];

			return { domNodes: arrayOfDomNodes };
		},
		timeZone: 'Europe/Stockholm',
		weekNumbers: true,
		weekText: __( 'W', 'myclub-groups' ),
		weekTextLong: __( 'Week', 'myclub-groups' )
	}

	const closeButtonListener = () => {
		const modal = outerRef.current.getElementsByClassName('modal-open')[0];
		const close = modal.getElementsByClassName('close')[0];

		modal.classList.remove('modal-open');
		close.removeEventListener('click', closeButtonListener);
		modal.removeEventListener( 'click', closeButtonListener );
	}

	const getColorClass = ( baseType ) => {
		switch ( baseType ) {
			case 'match':
				return 'red';
			case 'training':
				return 'green';
			case 'meeting':
				return 'blue';
			default:
				return 'yellow';
		}
	}


	const subtractMinutes = (time, minutes) => {
		let parts = time.split(':');
		let date = new Date();
		date.setHours(parts[0]);
		date.setMinutes(parts[1]);
		date.setSeconds(parts[2]);

		date.setMinutes(date.getMinutes() - minutes);

		let hrs = ("0" + date.getHours()).slice(-2);
		let mins = ("0" + date.getMinutes()).slice(-2);
		let secs = ("0" + date.getSeconds()).slice(-2);

		return `${hrs}:${mins}:${secs}`;
	}

	useEffect(() => {
		apiFetch( { path: '/myclub/v1/options' } ).then(options => {
			setCalendarTitle ( options.myclub_groups_calendar_title );
		} );

		getMyClubGroups( setPosts, selectPostLabel );
	}, []);

	useEffect(() => {
		if (calendarRef && calendarRef.current) {
			const api = calendarRef.current.getApi();
			api.removeAllEvents();
			api.addEventSource(postEvents.events);
		}
	}, [postEvents])

	useEffect(() => {
		setPostEvents({
			...postEvents,
			loaded: false
		});

		if (attributes.post_id) {
			apiFetch({ path: `/myclub/v1/groups/${attributes.post_id}`})
				.then((post) => {
					const allActivities = JSON.parse(post.activities);
					const events = allActivities.map((activity) => {
						let backgroundColor = '#9e8c39';
						let meetUpTime = parseInt(activity.meet_up_time);
						let meetUpTimeString = activity.start_time;

						if (meetUpTime) {
							meetUpTimeString = subtractMinutes(activity.start_time, meetUpTime);
						}

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
							title: activity.title.replaceAll('u0022', '\"'),
							start: `${activity.day} ${activity.start_time}`,
							end: `${activity.day} ${activity.end_time}`,
							backgroundColor,
							borderColor: backgroundColor,
							color: '#fff',
							display: 'block',
							extendedProps: {
								base_type: activity.base_type,
								calendar_name: activity.calendar_name,
								location: activity.location,
								description: activity.description.replaceAll('<br /><br />', '<br />'),
								endTime: activity.end_time,
								startTime: activity.start_time,
								meetUpPlace: activity.meet_up_place,
								meetUpTime: meetUpTimeString,
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
	}, [attributes.post_id]);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Content settings', 'myclub-groups' ) }>
					<PanelRow>
						<SelectControl
							label={ __('Group', 'myclub-groups') }
							value={ attributes.post_id }
							options={ posts }
							onChange={ ( value ) => {
								setAttributes({post_id: value});
							} }
						/>
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<div {...useBlockProps()}>
				<div className="myclub-groups-calendar" ref={ outerRef }>
					<div class="myclub-groups-calendar-container">
						<h3 class="myclub-groups-header">{ calendarTitle }</h3>
						<FullCalendar ref={ calendarRef } { ...options } />
					</div>
					<div className="calendar-modal">
						<div className="modal-content">
							<span className="close">&times;</span>
							<div className="modal-body">
							</div>
						</div>
					</div>
				</div>
			</div>
		</>
	);
}
