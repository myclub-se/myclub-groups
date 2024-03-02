function menuButtonToggle () {
    const menuElement = document.querySelector( '.myclub-groups-menu' );
    if (menuElement) {
        menuElement.classList.toggle('show');
    }
}

function linkToggle (event) {
    event.preventDefault();

    const subMenuElement = event.target.nextElementSibling;

    if (subMenuElement && !subMenuElement.classList.contains('show')) {
        const openMenu = document.querySelector('.myclub-groups-menu .sub-menu.show');
        if (openMenu) {
            openMenu.classList.remove('show')
        }
    }

    if (subMenuElement && subMenuElement.classList.contains('sub-menu')) {
        subMenuElement.classList.toggle('show');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('.myclub-groups-menu .mobile-menu-button');
    const links = document.querySelectorAll('.myclub-groups-menu .menu-item-has-children > a');

    links.forEach((link) => {
        link.addEventListener('click', linkToggle);
    });

    menuButton.addEventListener('click', menuButtonToggle);
});