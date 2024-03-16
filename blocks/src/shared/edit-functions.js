export function changeHostName ( oldUrl ) {
    // Create a new URL object with the old URL
    let url = new URL(oldUrl);

    // Update the protocol with the current page's protocol
    url.protocol = window.location.protocol;

    // Update the host with the current page's hostname
    url.host = window.location.hostname;

    // Return the new URL
    return url.href;
}

export function closeModal(ref) {
    if (ref.current) {
        ref.current.classList.remove('modal-open');
    }
}

export function getMyClubGroups( setPosts, selectPostLabel ) {
    const { apiFetch } = wp;

    apiFetch( { path: '/myclub/v1/groups' } ).then(
        fetchedItems => {
            const postOptions = fetchedItems.map( post => ({
                label: post.title,
                value: post.id
            }));

            postOptions.unshift( selectPostLabel );

            setPosts( postOptions );
        }
    );
}

export function setHeight ( ref, className ) {
    setTimeout(() => {
        const elements = Array.from( ref.current.getElementsByClassName( className ) );
        const maxHeight = Math.max( ...elements.map(( element ) => element.offsetHeight) );

        elements.forEach((element) => {
            element.style.height = `${maxHeight}px`;
        });
    });
}

export function showMemberModal( ref, member, labels ) {
    if (ref.current) {
        const imageElement = ref.current.getElementsByClassName('image')[0];
        const informationElement = ref.current.getElementsByClassName('information')[0];
        let output = '<div class="name">' + member.name + '</div>';

        imageElement.innerHTML = '<img src="' + changeHostName(member.member_image.url) + '" alt="' + member.name + '" />';

        if ( member.role || member.phone || member.email || member.age ) {
            output += '<table>';

            if ( member.role ) {
                output += `<tr><th>${labels.role}</th><td>${member.role}</td></tr>`;
            }

            if ( member.age ) {
                output += `<tr><th>${labels.age}</th><td>${member.age}</td></tr>`;
            }

            if ( member.email ) {
                output += `<tr><th>${labels.email}</th><td><a href="mailto:${member.email}">${member.email}</a></td></tr>`;
            }

            if ( member.phone ) {
                output += `<tr><th>${labels.phone}</th><td><a href="tel:${member.phone}">${member.phone}</a></td></tr>`;
            }

            output += '</table>';
        }

        informationElement.innerHTML = output;
        ref.current.classList.add('modal-open');
    }
}