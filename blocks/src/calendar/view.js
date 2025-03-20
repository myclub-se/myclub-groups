import svLocale from '@fullcalendar/core/locales/sv';
import enLocale from '@fullcalendar/core/locales/en-gb';

function closeButtonListener() {
    const modal = document.querySelector('.modal-open');
    const close = modal.querySelector('.close');

    modal.classList.remove('modal-open');
    close.removeEventListener('click', closeButtonListener);
    modal.removeEventListener( 'click', closeButtonListener );
}

function getColorClass( baseType ) {
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


document.addEventListener('DOMContentLoaded', () => {
    const smallScreen = document.documentElement.clientWidth < 960;
    const calendarEl = document.getElementById('calendar-div');
    const labels = JSON.parse(calendarEl.dataset.labels);
    const calendarLocale = calendarEl.dataset.locale === 'sv_SE' ? svLocale : enLocale;
    const rightToolbar = smallScreen ? 'timeGridDay,listMonth' : 'dayGridMonth,timeGridWeek,listMonth';
    const initialView = smallScreen ? 'listMonth' : 'dayGridMonth';

    const calendar = new FullCalendar.Calendar(calendarEl, {
        allDaySlot: false,
        buttonText: {
            today: labels.today,
            month: labels.month,
            week: labels.week,
            day: labels.day,
            list: labels.list
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: rightToolbar
        },
        eventClick: (arg) => {
            const item = arg.event;

            const modal = document.querySelector('.calendar-modal');
            const content = modal.querySelector('.modal-body');
            const close = modal.querySelector('.close');

            const { type, calendar_name, startTime, endTime, location, meetUpTime, meetUpPlace, description } = item.extendedProps;
            let output = `<div class="name">${type}</div>`;
            output += '<table>';
            output += `<tr><th>${labels.calendar}</th><td>${calendar_name}</td></tr>`;
            output += `<tr><th>${labels.name}</th><td>${item.title.replaceAll('u0022', '\"')}</td></tr>`;
            output += `<tr><th>${labels.when}</th><td>${startTime.substring(0, 5)} - ${endTime.substring(0, 5)}</td></tr>`;
            output += `<tr><th>${labels.location}</th><td>${location}</td></tr>`;
            if ( meetUpTime && meetUpTime !== startTime ) {
                output += `<tr><th>${labels.meetUpTime}</th><td>${meetUpTime.substring(0, 5)}</td></tr>`;
            }
            if ( meetUpPlace ) {
                output += `<tr><th>${labels.meetUpLocation}</th><td>${meetUpPlace}</td></tr>`;
            }
            if ( description ) {
                output += `<tr><th>${labels.description}</th><td>${description.replaceAll('u0022', '\"')}</td></tr>`;
            }
            output += '</table>';

            content.innerHTML = output;

            modal.classList.add('modal-open');
            modal.addEventListener( 'click', closeButtonListener );
            close.addEventListener( 'click', closeButtonListener );
        },
        eventContent: (arg) => {
            const item = arg.event;
            const element = document.createElement('div');
            let timeText = arg.timeText;
            element.classList.add('fc-event-title');
            element.classList.add(getColorClass(item.extendedProps.base_type));

            if ( item.extendedProps.meetUpTime && item.extendedProps.meetUpTime !== item.extendedProps.startTime ) {
                if ( !timeText ) {
                    timeText = item.extendedProps.startTime.substring(0, 5);
                }

                timeText += ` (${item.extendedProps.meetUpTime.substring(0, 5)})`;
            }

            element.innerHTML = '<div class="myclub-groups-event-time">' +
                timeText + '</div><div class="myclub-groups-event-title">' +
                item.title.replaceAll('u0022', '\"') +
                '</div>';

            let arrayOfDomNodes = [
                element
            ];

            return { domNodes: arrayOfDomNodes };
        },
        events: JSON.parse(calendarEl.dataset.events),
        firstDay: 1,
        initialView,
        locale: calendarLocale,
        timeZone: 'Europe/Stockholm',
        weekNumbers: true,
        weekText: labels.weekText,
        weekTextLong: labels.weekTextLong
    });
    calendar.render();
});