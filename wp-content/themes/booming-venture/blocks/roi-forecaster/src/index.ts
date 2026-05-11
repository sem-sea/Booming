import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from '../block.json';

registerBlockType(metadata as never, {
	edit: Edit,
	save: () => null,
});
