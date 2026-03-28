import { useBlockProps } from '@wordpress/block-editor';
import { useEffect, useRef, useState, useCallback } from '@wordpress/element';
import './editor.scss';
import {__} from "@wordpress/i18n";
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
    const [optionsLoaded, setOptionsLoaded] = useState(false);
    const [events, setEvents] = useState([]);
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
    const handleShowEvent = useCallback((arg) => {
        const item = arg.event;
        const modal = modalRef?.current;

        if (modal) {
            showDialog(item, modal, labels);
        }
    }, []);

    const getClubEvents = () => {
        apiFetch( { path: '/myclub/v1/club-activities' } ).then (activities => {
            setEvents ( setupEvents(activities) );
        });
    }

    // Create/destroy the calendar instance
    useEffect(() => {
        const el = calendarElRef.current;
        if (!el || !optionsLoaded) return;

        ensureStyleElement(el);

        const options = getFullCalendarOptions({
            labels,
            events,
            firstDay: startOfWeek,
            locale: getCalendarLocale(currentLocale),
            smallScreen: window.innerWidth < 960,
            desktopViews: calendarDesktopViews,
            desktopDefault: calendarDesktopViewsDefault,
            mobileViews: calendarMobileViews,
            mobileDefault: calendarMobileViewsDefault,
            showWeekNumbers: calendarShowWeekNumbers,
            plugins: [dayGridPlugin, timeGridPlugin, listPlugin],
            showEvent: (arg) => handleShowEvent(arg)
        });

        const calendar = new Calendar(el, options);
        calendar.render();
        calendarRef.current = calendar;

        return () => {
            calendar.destroy();
            calendarRef.current = null;
        };
    }, [calendarDesktopViews, calendarDesktopViewsDefault, calendarMobileViews, calendarMobileViewsDefault, calendarShowWeekNumbers, events, startOfWeek, currentLocale, optionsLoaded]);

    useEffect(() => {
        apiFetch( { path: '/myclub/v1/options' } ).then(options => {
            setCalendarTitle ( options.myclub_groups_club_calendar_title );
            setCalendarDesktopViews(options.myclub_groups_club_calendar_desktop_views);
            setCalendarDesktopViewsDefault(options.myclub_groups_club_calendar_desktop_views_default);
            setCalendarMobileViews(options.myclub_groups_club_calendar_mobile_views);
            setCalendarMobileViewsDefault(options.myclub_groups_club_calendar_mobile_views_default);
            setCalendarShowWeekNumbers(options.myclub_groups_club_calendar_show_week_numbers === '1');
            setOptionsLoaded(true);
            getClubEvents();
        } );
    }, []);

    return (
        <>
            <div {...useBlockProps()}>
                <div className="myclub-groups-club-calendar" ref={ outerRef }>
                    <div className="myclub-groups-club-calendar-container">
                        <h3 className="myclub-groups-header">{ calendarTitle }</h3>
                        {optionsLoaded ? (
                            <div id="club-calendar-div" ref={ calendarElRef } />
                        ) : (
                            <p>{__('Loading...', 'myclub-groups')}</p>
                        )}
                    </div>
                    <div className="club-calendar-modal" ref={ modalRef }>
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