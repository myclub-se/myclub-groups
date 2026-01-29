import {useEffect, useRef, useState, useMemo} from '@wordpress/element';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import './editor.scss';
import {__} from "@wordpress/i18n";

import {getMyClubGroups} from "../shared/edit-functions";
import FullCalendar from "@fullcalendar/react";
import {getCalendarLocale, getFullCalendarOptions, setupEvents, showDialog} from "../shared/calendar-functions";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";

const labels = {
	calendar: __('Calendar', 'myclub-groups'),
	name: __('Name', 'myclub-groups'),
	when: __('When', 'myclub-groups'),
	location: __('Location', 'myclub-groups'),
	meetUpTime: __('Gathering time', 'myclub-groups'),
	meetUpLocation: __('Gathering location', 'myclub-groups'),
	description: __('Description', 'myclub-groups'),
	weekText: __('W', 'myclub-groups'),
	weekTextLong: __('Week', 'myclub-groups'),
};

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
	const [calendarDesktopViews, setCalendarDesktopViews] = useState('');
	const [calendarDesktopViewsDefault, setCalendarDesktopViewsDefault] = useState('');
	const [calendarMobileViews, setCalendarMobileViews] = useState('');
	const [calendarMobileViewsDefault, setCalendarMobileViewsDefault] = useState('');
	const [calendarShowWeekNumbers, setCalendarShowWeekNumbers] = useState(true);
	const [postEvents, setPostEvents] = useState({events: [], loaded: false});
	const [optionsLoaded, setOptionsLoaded] = useState(false);
	const [posts, setPosts] = useState([]);
	const {apiFetch} = wp;
	const {useSelect} = wp.data;
	let calendarRef = useRef();
	let outerRef = useRef();
	let modalRef = useRef();
	const currentLocale = useSelect((select) => {
		if (select("core").getSite()) {
			return select('core').getSite().language;
		}

		return 'sv_SE';
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
	const handleShowEvent = (arg) => {
		const item = arg.event;
		const modal = modalRef?.current;

		if (modal) {
			showDialog(item, modal, labels);
		}
	};
	const resetPostEvents = (loaded = false) => {
		setPostEvents({
			events: [],
			loaded,
		});
	};

	const options = useMemo(() => {
		if (!optionsLoaded) return null;

		return getFullCalendarOptions({
			labels,
			events: postEvents.events || [], // Provide events
			startOfWeek,
			locale: getCalendarLocale(currentLocale),
			smallScreen: window.innerWidth < 960,
			desktopViews: calendarDesktopViews,
			desktopDefault: calendarDesktopViewsDefault,
			mobileViews: calendarMobileViews,
			mobileDefault: calendarMobileViewsDefault,
			showWeekNumbers: calendarShowWeekNumbers,
			plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
			showEvent: (arg) => handleShowEvent(arg)
		})
	}, [calendarDesktopViews, calendarDesktopViewsDefault, calendarMobileViews, calendarMobileViewsDefault, calendarShowWeekNumbers, postEvents.events, startOfWeek, currentLocale]);

	const fetchEvents = async (post_id) => {
		try {
			const post = await apiFetch({ path: `/myclub/v1/groups/${post_id}` });
			const allActivities = JSON.parse(post.activities); // Parse activities data
			const events = setupEvents(allActivities);

			setPostEvents({
				events,
				loaded: true, // Mark as successfully loaded
			});
		} catch (error) {
			// Handle errors and reset state
			throw new Error(error.message);
		}
	};

	useEffect(() => {
		apiFetch( { path: '/myclub/v1/options' } ).then(options => {
			setCalendarTitle ( options.myclub_groups_calendar_title );
			setCalendarDesktopViews(options.myclub_groups_group_calendar_desktop_views);
			setCalendarDesktopViewsDefault(options.myclub_groups_group_calendar_desktop_views_default);
			setCalendarMobileViews(options.myclub_groups_group_calendar_mobile_views);
			setCalendarMobileViewsDefault(options.myclub_groups_group_calendar_mobile_views_default);
			setCalendarShowWeekNumbers(options.myclub_groups_group_calendar_show_week_numbers);
			setOptionsLoaded(true);
		} );

		getMyClubGroups( setPosts, selectPostLabel );
	}, []);

	useEffect(() => {
		// Ensure the calendar reference exists before attempting to update events
		if (calendarRef && calendarRef.current) {
			const api = calendarRef.current.getApi();

			// Only update the calendar if there are new events and they are loaded
			if (postEvents.loaded) {
				api.removeAllEvents(); // Clear previous events
				api.addEventSource(postEvents.events); // Add the new event source
			}
		}
	}, [postEvents]);

	useEffect(() => {
		// Reset the postEvents state whenever the post_id changes
		resetPostEvents();

		// Fetch events if a valid post_id is provided
		if (attributes.post_id) {
			fetchEvents(attributes.post_id).catch(error => {
				console.error('Error fetching events:', error); // Log fetch errors
				setPostEvents({
					events: [],
					loaded: true, // Mark as loaded to avoid infinite effect calls
				});
			});
		} else {
			resetPostEvents(true);
		}
		// Depend on post_id so it executes correctly when attributes.post_id changes
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
						{options ? (
							<FullCalendar ref={calendarRef} {...options} />
						) : (
							<p>{__('Loading...', 'myclub-groups')}</p>
						)}
					</div>
					<div className="calendar-modal" ref={ modalRef }>
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
