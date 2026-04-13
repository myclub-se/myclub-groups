import {useEffect, useRef, useState, useCallback} from '@wordpress/element';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import './editor.scss';
import {__} from "@wordpress/i18n";

import {getMyClubGroups} from "../shared/edit-functions";
import {Calendar} from "@fullcalendar/core";
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
 * Pre-inject a <style data-fullcalendar> element so that FullCalendar's
 * ensureElHasStyles() finds it via querySelector instead of trying to
 * insertBefore the DOCTYPE node in the block-editor iframe.
 */
function ensureStyleElement(el) {
	if (!el || !el.isConnected) return;

	const rootNode = el.getRootNode();
	if (!rootNode || rootNode.querySelector('style[data-fullcalendar]')) return;

	const styleEl = document.createElement('style');
	styleEl.setAttribute('data-fullcalendar', '');

	const head = rootNode === document
		? document.head
		: (rootNode.head || rootNode.querySelector('head'));

	if (head) {
		head.appendChild(styleEl);
	}
}

export default function Edit( { attributes, setAttributes } ) {
	const [calendarTitle, setCalendarTitle] = useState('');
	const [calendarDesktopViews, setCalendarDesktopViews] = useState('');
	const [calendarDesktopViewsDefault, setCalendarDesktopViewsDefault] = useState('');
	const [calendarMobileViews, setCalendarMobileViews] = useState('');
	const [calendarMobileViewsDefault, setCalendarMobileViewsDefault] = useState('');
	const [calendarShowWeekNumbers, setCalendarShowWeekNumbers] = useState(true);
	const [noEventsContent, setNoEventsContent] = useState('');
	const [postEvents, setPostEvents] = useState({events: [], loaded: false});
	const [optionsLoaded, setOptionsLoaded] = useState(false);
	const [posts, setPosts] = useState([]);
	const {apiFetch} = wp;
	const {useSelect} = wp.data;
	let calendarRef = useRef(null);
	let calendarElRef = useRef();
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
			if (calendarRef.current) {
				calendarRef.current.setOption('firstDay', startOfWeek);
			}
			return startOfWeek;
		}

		return 1;
	});
	const selectPostLabel = {
		label: __( 'Select a group', 'myclub-groups' ),
		value: ''
	};
	const handleShowEvent = useCallback((arg) => {
		const item = arg.event;
		const modal = modalRef?.current;

		if (modal) {
			showDialog(item, modal, labels);
		}
	}, []);
	const resetPostEvents = (loaded = false) => {
		setPostEvents({
			events: [],
			loaded,
		});
	};

	const fetchEvents = async (post_id) => {
		try {
			const post = await apiFetch({ path: `/myclub/v1/groups/${post_id}` });
			const allActivities = JSON.parse(post.activities);
			const events = setupEvents(allActivities);

			setPostEvents({
				events,
				loaded: true,
			});
		} catch (error) {
			throw new Error(error.message);
		}
	};

	// Create/destroy the calendar instance
	useEffect(() => {
		const el = calendarElRef.current;
		if (!el || !optionsLoaded) return;

		ensureStyleElement(el);

		const options = getFullCalendarOptions({
			labels,
			events: postEvents.events || [],
			firstDay: startOfWeek,
			locale: getCalendarLocale(currentLocale),
			smallScreen: window.innerWidth < 960,
			desktopViews: calendarDesktopViews,
			desktopDefault: calendarDesktopViewsDefault,
			mobileViews: calendarMobileViews,
			mobileDefault: calendarMobileViewsDefault,
			showWeekNumbers: calendarShowWeekNumbers,
			plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
			showEvent: (arg) => handleShowEvent(arg),
			noEventsContent: noEventsContent,
		});

		const calendar = new Calendar(el, options);
		calendar.render();
		calendarRef.current = calendar;

		return () => {
			calendar.destroy();
			calendarRef.current = null;
		};
	}, [calendarDesktopViews, calendarDesktopViewsDefault, calendarMobileViews, calendarMobileViewsDefault, calendarShowWeekNumbers, postEvents.events, startOfWeek, currentLocale, optionsLoaded]);

	useEffect(() => {
		apiFetch( { path: '/myclub/v1/options' } ).then(options => {
			setCalendarTitle ( options.myclub_groups_calendar_title );
			setCalendarDesktopViews(options.myclub_groups_group_calendar_desktop_views);
			setCalendarDesktopViewsDefault(options.myclub_groups_group_calendar_desktop_views_default);
			setCalendarMobileViews(options.myclub_groups_group_calendar_mobile_views);
			setCalendarMobileViewsDefault(options.myclub_groups_group_calendar_mobile_views_default);
			setCalendarShowWeekNumbers(options.myclub_groups_group_calendar_show_week_numbers);
			setNoEventsContent(options.myclub_groups_no_activities_message);
			setOptionsLoaded(true);
		} );

		getMyClubGroups( setPosts, selectPostLabel );
	}, []);

	useEffect(() => {
		resetPostEvents();

		if (attributes.post_id) {
			fetchEvents(attributes.post_id).catch(error => {
				console.error('Error fetching events:', error);
				setPostEvents({
					events: [],
					loaded: true,
				});
			});
		} else {
			resetPostEvents(true);
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
					<div className="myclub-groups-calendar-container">
						<h3 className="myclub-groups-header">{ calendarTitle }</h3>
						{optionsLoaded ? (
							<div id="calendar-div" ref={ calendarElRef } />
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