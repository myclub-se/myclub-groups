import {escapeHtml} from './escape';

function closeButtonListener() {
    const modal = document.getElementsByClassName('modal-open')[0];
    const close = modal.getElementsByClassName('close')[0];

    modal.classList.remove('modal-open');
    close.removeEventListener('click', closeButtonListener);
    modal.removeEventListener( 'click', closeModalListener );
}

function closeModalListener(event) {
    const modal = document.getElementsByClassName('modal-open')[0];
    const close = modal.getElementsByClassName('close')[0];

    if (event.target !== modal) {
        return;
    }

    modal.classList.remove('modal-open');
    close.removeEventListener('click', closeButtonListener);
    modal.removeEventListener( 'click', closeModalListener );
}

function showModal ( modalClassName, labels, data ) {
    const modal = document.getElementsByClassName(modalClassName)[0];
    const image = modal.getElementsByClassName('image')[0];
    const information = modal.getElementsByClassName('information')[0];
    const close = modal.getElementsByClassName('close')[0];
    if (data.image_id) {
        const picture = document.createElement('img');
        picture.src = data.image_url;
        picture.alt = data.name;
        image.replaceChildren(picture);
    }
    let output = '<div class="name">' + escapeHtml(data.name) + '</div>';

    if ( data.role || data.phone || data.email || data.age || (data.dynamic_fields && data.dynamic_fields.length ) ) {
        output += '<table>';

        if ( data.role ) {
            output += `<tr><th>${labels.role}</th><td>${escapeHtml(data.role)}</td></tr>`;
        }

        if ( data.age ) {
            output += `<tr><th>${labels.age}</th><td>${escapeHtml(data.age)}</td></tr>`;
        }

        if ( data.email ) {
            output += `<tr><th>${labels.email}</th><td><a href="mailto:${escapeHtml(data.email)}">${escapeHtml(data.email)}</a></td></tr>`;
        }

        if ( data.phone ) {
            output += `<tr><th>${labels.phone}</th><td><a href="tel:${escapeHtml(data.phone)}">${escapeHtml(data.phone)}</a></td></tr>`;
        }

        if ( data.dynamic_fields && data.dynamic_fields.length ) {
            data.dynamic_fields.forEach( ( field ) => {
                output += `<tr><th>${escapeHtml(field.name)}</th><td>${escapeHtml(field.value)}</td></tr>`;
            });
        }

        output += '</table>';
    }

    information.innerHTML = output;

    modal.classList.add('modal-open');
    modal.addEventListener( 'click', closeModalListener );
    close.addEventListener( 'click', closeButtonListener );
}

export function addModalFunction( type ) {
    const elements = document.querySelectorAll(`.${type}`);
    const labels = JSON.parse( document.getElementsByClassName( `${type}s-list`)[0].dataset.labels );
    const modalContentElements = document.querySelectorAll('.modal-content');

    modalContentElements.forEach((element) => {
        element.addEventListener( 'click', (event) => event.stopPropagation() );
    });

    elements.forEach((element) => {
        element.addEventListener('click', function() {
            showModal( `${type}-modal`, labels, JSON.parse( this.dataset[`${type}`] ) );
        });
    });
}

export function addShowMoreListener( type ) {
    const elements = document.getElementsByClassName( `${type}-show-more` );
    Array.from( elements ).forEach( ( element ) => {
        element.addEventListener('click', function() {
            const list = element.closest( `.${ type }s-list` );
            list.getElementsByClassName( 'extended-list' )[0].classList.remove('hidden');
            element.classList.add('hidden');
            list.getElementsByClassName( `${type}-show-less` )[0].classList.remove('hidden');
            setElementHeight( type );
        });
    });
}

export function addShowLessListener( type ) {
    const elements = document.getElementsByClassName( `${type}-show-less` );
    Array.from( elements ).forEach( ( element ) => {
        element.addEventListener('click', function() {
            const list = element.closest( `.${ type }s-list` );
            list.getElementsByClassName( 'extended-list' )[0].classList.add('hidden');
            element.classList.add('hidden');
            list.getElementsByClassName( `${type}-show-more` )[0].classList.remove('hidden');
            setElementHeight( type );
        });
    });
}

export function setElementHeight ( className ) {
    setTimeout(() => {
        const elements = document.getElementsByClassName( className );
        let maxHeight = 0;

        for(let i = 0; i < elements.length; i++) {
            elements[i].style.height = '';
        }

        // Find the max height
        for(let i = 0; i < elements.length; i++) {
            const element_height = elements[i].offsetHeight;
            maxHeight = (element_height > maxHeight) ? element_height : maxHeight;
        }

        // Apply max height to all elements
        for(let i = 0; i < elements.length; i++) {
            elements[i].style.height = `${maxHeight}px`;
        }
    }, 100);
}
