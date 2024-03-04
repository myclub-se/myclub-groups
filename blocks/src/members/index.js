import { registerBlockType } from '@wordpress/blocks';
import './style.scss';

import Edit from './edit';
import metadata from './block.json';

/**
 * Registers the members block. Translates the title and description.
 *
 */
registerBlockType( metadata.name, {
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
} );
