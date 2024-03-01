function closeButtonListener() {
    const modal = document.getElementsByClassName('modal-open')[0];
    const close = modal.getElementsByClassName('close')[0];

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


document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar-div');
    const labels = JSON.parse(calendarEl.dataset.labels);
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
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        eventClick: (arg) => {
            const item = arg.event;

            const modal = document.getElementsByClassName('calendar-modal')[0];
            const content = modal.getElementsByClassName('modal-body')[0];
            const close = modal.getElementsByClassName('close')[0];

            let output = '<div class="name">' + item.extendedProps.type + '</div>';
            output += '<table>';

            output += `<tr><th>${labels.calendar}</th><td>${item.extendedProps.calendar_name}</td></tr>`;
            output += `<tr><th>${labels.name}</th><td>${item.title}</td></tr>`;
            output += `<tr><th>${labels.when}</th><td>${item.extendedProps.startTime.substring(0, 5)} - ${item.extendedProps.endTime.substring(0, 5)}</td></tr>`;
            output += `<tr><th>${labels.location}</th><td>${item.extendedProps.location}</td></tr>`;
            if ( item.extendedProps.meetUpTime && item.extendedProps.meetUpTime !== item.extendedProps.startTime ) {
                output += `<tr><th>${labels.meetUpTime}</th><td>${item.extendedProps.meetUpTime.substring(0, 5)}</td></tr>`;
            }
            if ( item.extendedProps.meetUpPlace ) {
                output += `<tr><th>${labels.meetUpLocation}</th><td>${item.extendedProps.meetUpPlace}</td></tr>`;
            }
            if ( item.extendedProps.description ) {
                output += `<tr><th>${labels.description}</th><td>${item.extendedProps.description}</td></tr>`;
            }
            output += '</table>'

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
        events: JSON.parse(calendarEl.dataset.events),
        firstDay: 1,
        initialView: 'dayGridMonth',
        locale: 'sv',
        timeZone: 'Europe/Stockholm',
        weekNumbers: true,
        weekText: labels.weekText,
        weekTextLong: labels.weekTextLong
    });
    calendar.render();
});