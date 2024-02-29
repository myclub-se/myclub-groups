/**
 * Functionality required for the members component on the frontend.
 */

import {addModalFunction, addShowLessListener, addShowMoreListener, setElementHeight} from "../shared/shared-functions";

document.addEventListener('DOMContentLoaded', () => {
    // Set element heights and width when rendering the page
    setElementHeight( 'member-picture' );
    setElementHeight( 'member-name' );

    // Add click functionality to display member modal.
    addModalFunction( 'member' );
    addShowMoreListener( 'member' );
    addShowLessListener( 'member' );
});

window.addEventListener("resize", (event) => {
    // Set element heights and width when resizing the page
    setElementHeight('member-picture');
    setElementHeight('member-name');
});