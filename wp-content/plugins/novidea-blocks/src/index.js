/**
 * Novidea Blocks: editor entry. Block metadata (title, attributes, …) comes from each block.json via
 * the server; here we only add the editing UI. Blocks are dynamic, so save() stores nothing but the
 * attributes.
 */
import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import { SCHEMAS } from './schemas';
import './seo-panel';
import './editor.css';

Object.keys( SCHEMAS ).forEach( ( name ) => {
	registerBlockType( name, {
		edit: Edit,
		save: () => null,
	} );
} );
