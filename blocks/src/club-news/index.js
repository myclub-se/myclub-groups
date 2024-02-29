/**
 * Registers the news block.
 */
import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import './style.scss';

import Edit from './edit';
import metadata from './block.json';

/**
 * Register the news block and add translations for the titel and description.
 */
registerBlockType( metadata.name, {
	title: __(metadata.title, 'myclub-groups'),
	description: __(metadata.description, 'myclub-groups'),

	/**
	 * @see ./edit.js
	 */
	edit: Edit,
} );
