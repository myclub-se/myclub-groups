import { useEffect, useRef, useState } from '@wordpress/element';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render'

/**
 * The edit function required to handle the menu component. Just renders the menu via the backend render function.
 *
 * @return {Element} Element to render.
 */
export default function Edit() {
	const ref = useRef();

	const menuButtonToggle = () => {
		const menuElement = ref?.current?.querySelector( '.myclub-groups-menu' );
		if (menuElement) {
			menuElement.classList.toggle('show');
		}
	}

	const linkToggle = (event) => {
		event.preventDefault();

		const subMenuElement = event.target.nextElementSibling;

		if (subMenuElement && !subMenuElement.classList.contains('show')) {
			const openMenu = ref?.current?.querySelector('.myclub-groups-menu .sub-menu.show');
			if (openMenu) {
				openMenu.classList.remove('show')
			}
		}

		if (subMenuElement && subMenuElement.classList.contains('sub-menu')) {
			subMenuElement.classList.toggle('show');
		}
	}

	useEffect(() => {
		const interval = setInterval( () => {
			const menuElement = ref?.current?.querySelector( '.myclub-groups-menu' );
			if ( menuElement ) {
				clearInterval( interval );
				const menuButton = menuElement.querySelector('.mobile-menu-button');
				const links = menuElement.querySelectorAll('.menu-item-has-children > a');

				links.forEach((link) => {
					link.addEventListener('click', linkToggle);
				});

				menuButton.addEventListener('click', menuButtonToggle);
			}
		}, 100 );

		return () => {
			clearInterval( interval );
			if (ref?.current) {
				const menuButton = ref.current.querySelector('.mobile-menu-button');
				const links = ref.current.querySelectorAll('.menu-item-has-children > a');

				links.forEach((link) => {
					link.removeEventListener('click', linkToggle);
				});
				menuButton.removeEventListener('click', menuButtonToggle);
			}
		}
	}, []);

	return (
		<div { ...useBlockProps() } ref={ref}>
			<ServerSideRender block="myclub-groups/menu" />
		</div>
	);
}
