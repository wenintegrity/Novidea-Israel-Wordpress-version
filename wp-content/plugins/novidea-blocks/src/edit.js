/**
 * Editor for every Novidea block: a live preview rendered by the server (the same PHP as the site),
 * and a form for the block's fields. Toggle between them with the toolbar button, or double-click
 * the preview. A new, empty block opens in the form.
 */
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { BlockControls, useBlockProps } from '@wordpress/block-editor';
import { Button, Disabled, Notice, ToolbarButton, ToolbarGroup } from '@wordpress/components';
import { getBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import { pencil, seen } from '@wordpress/icons';
import { Fields } from './form';
import { SCHEMAS, isEmpty } from './schemas';

function EmptyPreview() {
	return (
		<Notice status="info" isDismissible={ false }>
			{ __( 'This section is empty. Click "Edit content" to fill it in.', 'novidea-blocks' ) }
		</Notice>
	);
}

export default function Edit( { name, attributes, setAttributes } ) {
	const schema = SCHEMAS[ name ];
	const [ editing, setEditing ] = useState( () => isEmpty( name, attributes ) );
	const blockProps = useBlockProps( { className: 'nv-block' } );

	return (
		<div { ...blockProps }>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon={ editing ? seen : pencil }
						label={ editing ? __( 'Preview', 'novidea-blocks' ) : __( 'Edit content', 'novidea-blocks' ) }
						showTooltip
						onClick={ () => setEditing( ! editing ) }
					>
						{ editing ? __( 'Preview', 'novidea-blocks' ) : __( 'Edit content', 'novidea-blocks' ) }
					</ToolbarButton>
				</ToolbarGroup>
			</BlockControls>

			{ editing ? (
				<div className="nv-form">
					<div className="nv-form__head">
						<strong>{ getBlockType( name )?.title }</strong>
						<Button variant="primary" size="compact" onClick={ () => setEditing( false ) }>
							{ __( 'Done', 'novidea-blocks' ) }
						</Button>
					</div>
					{ schema.help && <p className="nv-help">{ schema.help }</p> }
					<Fields fields={ schema.fields } values={ attributes } onChange={ setAttributes } />
				</div>
			) : (
				<div
					className="nv-preview"
					onDoubleClick={ () => setEditing( true ) }
					title={ __( 'Double-click to edit', 'novidea-blocks' ) }
				>
					<Disabled>
						<ServerSideRender
							block={ name }
							attributes={ attributes }
							httpMethod="POST"
							EmptyResponsePlaceholder={ EmptyPreview }
						/>
					</Disabled>
				</div>
			) }
		</div>
	);
}
