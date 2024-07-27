import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';
import metadata from './block.json';

/**
 * Registers the title block
 *
 */
registerBlockType( metadata.name, {
	/**
	 * @see ./edit.js
	 */
	edit: Edit,
} );
