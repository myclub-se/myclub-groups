import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render'

/**
 * The edit function required to handle the menu component. Just renders the menu via the backend render function.
 *
 * @return {Element} Element to render.
 */
export default function Edit() {
	return (
		<div { ...useBlockProps() }>
			<ServerSideRender block="myclub-groups/menu" />
		</div>
	);
}
