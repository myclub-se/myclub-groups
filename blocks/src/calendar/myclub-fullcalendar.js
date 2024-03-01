import { Component } from '@wordpress/element';
import {__} from "@wordpress/i18n";

import FullCalendar from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";


export default class MyClubFullcalendar extends Component {
    constructor( props ) {
        super( props );
        this.outerRef = React.createRef();
        this.closeButtonListener = this.closeButtonListener.bind(this);
        this.calendarRef = props.calendarRef ? props.calendarRef : React.createRef();
        this.options = {
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
            events: props.events,
            locale: props.locale,
            firstDay: props.firstDay ? props.firstDay : 1,
            eventClick: (arg) => {
                const item = arg.event;

                const modal = this.outerRef.current.getElementsByClassName('calendar-modal')[0];
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
                modal.addEventListener( 'click', this.closeButtonListener );
                close.addEventListener( 'click', this.closeButtonListener );
            },
            eventContent: (arg) => {
                const item = arg.event;
                const element = document.createElement('div');
                let timeText = arg.timeText;
                element.classList.add('fc-event-title');
                element.classList.add( this.getColorClass ( item.extendedProps.base_type ) );

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
        }
    }

    closeButtonListener() {
        const modal = this.outerRef.current.getElementsByClassName('modal-open')[0];
        const close = modal.getElementsByClassName('close')[0];

        modal.classList.remove('modal-open');
        close.removeEventListener('click', this.closeButtonListener);
        modal.removeEventListener( 'click', this.closeButtonListener );
    }

    getColorClass( baseType ) {
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

    render() {
        return (
            <div style={ { position: 'relative' } } ref={this.outerRef}>
                <FullCalendar ref={ this.calendarRef } { ...this.options } />
                <div className="calendar-modal">
                    <div className="modal-content">
                        <span className="close">&times;</span>
                        <div className="modal-body">
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}