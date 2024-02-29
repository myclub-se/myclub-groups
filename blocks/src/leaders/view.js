/**
 * Functionality required for the members component on the frontend.
 */

import {addModalFunction, addShowLessListener, addShowMoreListener, setElementHeight} from "../shared/shared-functions";

document.addEventListener('DOMContentLoaded', (event) => {
    // Set element heights and width when rendering the page
    setElementHeight('leader-picture');
    setElementHeight('leader-name');

    // Add click functionality to display member modal.
    addModalFunction( 'leader' );
    addShowMoreListener( 'leader' );
    addShowLessListener( 'leader' );
});

window.addEventListener("resize", (event) => {
    // Set element heights and width when resizing the page
    setElementHeight('leader-picture');
    setElementHeight('leader-name');
});