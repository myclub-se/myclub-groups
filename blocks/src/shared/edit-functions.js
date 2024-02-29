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

export function setHeight ( ref, className ) {
    setTimeout(() => {
        const elements = Array.from( ref.current.getElementsByClassName( className ) );
        const maxHeight = Math.max( ...elements.map(( element ) => element.offsetHeight) );

        elements.forEach((element) => {
            element.style.height = `${maxHeight}px`;
        });
    });
}