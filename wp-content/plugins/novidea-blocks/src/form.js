/**
 * Form controls for the block fields (see schemas.js).
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	BaseControl,
	Button,
	ComboboxControl,
	PanelBody,
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { arrowDown, arrowUp, trash } from '@wordpress/icons';

const ICONS = window.novideaBlocks?.icons ?? [];
const ICON_BASE = window.novideaBlocks?.iconBase ?? '';

/** Move item i by d places in a copy of list. */
function moved( list, i, d ) {
	const next = [ ...list ];
	const j = i + d;
	if ( j < 0 || j >= next.length ) {
		return next;
	}
	[ next[ i ], next[ j ] ] = [ next[ j ], next[ i ] ];
	return next;
}

function ItemActions( { index, count, onMove, onRemove } ) {
	return (
		<div className="nv-item-actions">
			<Button
				icon={ arrowUp }
				size="small"
				label={ __( 'Move up', 'novidea-blocks' ) }
				disabled={ index === 0 }
				onClick={ () => onMove( -1 ) }
			/>
			<Button
				icon={ arrowDown }
				size="small"
				label={ __( 'Move down', 'novidea-blocks' ) }
				disabled={ index === count - 1 }
				onClick={ () => onMove( 1 ) }
			/>
			<Button
				icon={ trash }
				size="small"
				isDestructive
				label={ __( 'Remove', 'novidea-blocks' ) }
				onClick={ onRemove }
			/>
		</div>
	);
}

/** A list of paragraphs (or bullets, or field names). */
function StringsField( { field, value, onChange } ) {
	const list = Array.isArray( value ) ? value : [];
	return (
		<BaseControl __nextHasNoMarginBottom label={ field.label } help={ field.help }>
			<div className="nv-strings">
				{ list.map( ( text, i ) => (
					<div className="nv-strings__row" key={ i }>
						<TextareaControl
							__nextHasNoMarginBottom
							label={ sprintf( '%1$s %2$d', field.label, i + 1 ) }
							hideLabelFromVision
							value={ text }
							rows={ 3 }
							onChange={ ( v ) => onChange( list.map( ( t, j ) => ( j === i ? v : t ) ) ) }
						/>
						<ItemActions
							index={ i }
							count={ list.length }
							onMove={ ( d ) => onChange( moved( list, i, d ) ) }
							onRemove={ () => onChange( list.filter( ( _, j ) => j !== i ) ) }
						/>
					</div>
				) ) }
				<Button variant="secondary" size="small" onClick={ () => onChange( [ ...list, '' ] ) }>
					{ field.addLabel || __( 'Add', 'novidea-blocks' ) }
				</Button>
			</div>
		</BaseControl>
	);
}

/** A list of objects, each with its own sub-form, collapsible. */
function RepeaterField( { field, value, onChange } ) {
	const list = Array.isArray( value ) ? value : [];
	return (
		<BaseControl __nextHasNoMarginBottom label={ field.label } help={ field.help }>
			<div className="nv-repeater">
				{ list.map( ( item, i ) => (
					<div className="nv-repeater__item" key={ i }>
						<PanelBody
							title={ `${ i + 1 }. ${ field.itemLabel?.( item ) || __( '(empty)', 'novidea-blocks' ) }` }
							initialOpen={ ! field.itemLabel?.( item ) }
						>
							<Fields
								fields={ field.fields }
								values={ item }
								onChange={ ( patch ) =>
									onChange( list.map( ( it, j ) => ( j === i ? { ...it, ...patch } : it ) ) )
								}
							/>
						</PanelBody>
						<ItemActions
							index={ i }
							count={ list.length }
							onMove={ ( d ) => onChange( moved( list, i, d ) ) }
							onRemove={ () => onChange( list.filter( ( _, j ) => j !== i ) ) }
						/>
					</div>
				) ) }
				<Button
					variant="secondary"
					onClick={ () => onChange( [ ...list, { ...( field.newItem || {} ) } ] ) }
				>
					{ field.addLabel || __( 'Add', 'novidea-blocks' ) }
				</Button>
			</div>
		</BaseControl>
	);
}

/** A button label + address. */
function LinkField( { field, value, onChange } ) {
	const link = value && typeof value === 'object' ? value : {};
	return (
		<fieldset className="nv-link">
			<legend>{ field.label }</legend>
			<TextControl
				__nextHasNoMarginBottom
				label={ __( 'Button text', 'novidea-blocks' ) }
				value={ link.label || '' }
				onChange={ ( v ) => onChange( { ...link, label: v } ) }
			/>
			<TextControl
				__nextHasNoMarginBottom
				className="nv-ltr"
				label={ __( 'Link', 'novidea-blocks' ) }
				help={ __( 'A page address (/contact) or a section on this page (#lead-form).', 'novidea-blocks' ) }
				value={ link.href || '' }
				onChange={ ( v ) => onChange( { ...link, href: v } ) }
			/>
		</fieldset>
	);
}

/** A media-library file: pick, replace, remove. */
function MediaPicker( { label, allowedTypes, id, url, preview, onSelect, onRemove } ) {
	return (
		<BaseControl __nextHasNoMarginBottom label={ label }>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ allowedTypes }
					value={ id }
					onSelect={ onSelect }
					render={ ( { open } ) => (
						<div className="nv-media">
							{ url && preview === 'image' && <img className="nv-media__preview" src={ url } alt="" /> }
							{ url && preview !== 'image' && (
								<code className="nv-media__file" dir="ltr">
									{ url.split( '/' ).pop() }
								</code>
							) }
							<div className="nv-media__buttons">
								<Button variant="secondary" size="small" onClick={ open }>
									{ url ? __( 'Replace', 'novidea-blocks' ) : __( 'Choose from the media library', 'novidea-blocks' ) }
								</Button>
								{ url && (
									<Button variant="tertiary" size="small" isDestructive onClick={ onRemove }>
										{ __( 'Remove', 'novidea-blocks' ) }
									</Button>
								) }
							</div>
						</div>
					) }
				/>
			</MediaUploadCheck>
		</BaseControl>
	);
}

function ImageField( { field, value, onChange } ) {
	const image = value && typeof value === 'object' && ! Array.isArray( value ) ? value : {};
	return (
		<div className="nv-image">
			<MediaPicker
				label={ field.label }
				allowedTypes={ [ 'image' ] }
				id={ image.id }
				url={ image.url }
				preview="image"
				onSelect={ ( media ) =>
					onChange( {
						id: media.id,
						url: media.sizes?.medium?.url || media.url,
						alt: image.alt || media.alt || '',
					} )
				}
				onRemove={ () => onChange( {} ) }
			/>
			{ field.help && <p className="nv-help">{ field.help }</p> }
			{ field.alt !== false && image.url && (
				<TextControl
					__nextHasNoMarginBottom
					label={ __( 'Alternative text', 'novidea-blocks' ) }
					help={ __( 'Describe the picture for blind visitors. Leave empty only if it is purely decorative.', 'novidea-blocks' ) }
					value={ image.alt || '' }
					onChange={ ( alt ) => onChange( { ...image, alt } ) }
				/>
			) }
		</div>
	);
}

function VideoField( { field, value, onChange } ) {
	const video = value && typeof value === 'object' && ! Array.isArray( value ) ? value : {};
	const set = ( patch ) => onChange( { ...video, ...patch } );
	return (
		<fieldset className="nv-link">
			<legend>{ field.label }</legend>
			<TextControl
				__nextHasNoMarginBottom
				label={ __( 'Video title (its accessible name)', 'novidea-blocks' ) }
				help={ __( 'With a title but no file, a "video coming soon" placeholder is shown.', 'novidea-blocks' ) }
				value={ video.title || '' }
				onChange={ ( title ) => set( { title } ) }
			/>
			<MediaPicker
				label={ __( 'Video file (MP4 or WebM)', 'novidea-blocks' ) }
				allowedTypes={ [ 'video' ] }
				id={ video.id }
				url={ video.url }
				onSelect={ ( media ) => set( { id: media.id, url: media.url } ) }
				onRemove={ () => set( { id: undefined, url: undefined } ) }
			/>
			<MediaPicker
				label={ __( 'Poster picture (before playback)', 'novidea-blocks' ) }
				allowedTypes={ [ 'image' ] }
				id={ video.posterId }
				url={ video.posterUrl }
				preview="image"
				onSelect={ ( media ) =>
					set( { posterId: media.id, posterUrl: media.sizes?.medium?.url || media.url } )
				}
				onRemove={ () => set( { posterId: undefined, posterUrl: undefined } ) }
			/>
			<MediaPicker
				label={ __( 'Captions (WebVTT .vtt file)', 'novidea-blocks' ) }
				allowedTypes={ [ 'text/vtt' ] }
				id={ video.captionsId }
				url={ video.captionsUrl }
				onSelect={ ( media ) => set( { captionsId: media.id, captionsUrl: media.url } ) }
				onRemove={ () => set( { captionsId: undefined, captionsUrl: undefined } ) }
			/>
			<p className="nv-help">
				{ __( 'A video with speech needs captions (Israeli Standard 5568, WCAG 1.2.2).', 'novidea-blocks' ) }
			</p>
		</fieldset>
	);
}

function IconField( { field, value, onChange } ) {
	return (
		<div className="nv-icon">
			{ value && (
				<img className="nv-icon__preview" src={ `${ ICON_BASE }${ value }.png` } alt="" width="40" height="40" />
			) }
			<ComboboxControl
				__nextHasNoMarginBottom
				label={ field.label }
				value={ value || '' }
				options={ ICONS.map( ( icon ) => ( {
					value: icon.id,
					label: icon.en ? `${ icon.label } · ${ icon.en }` : icon.label,
				} ) ) }
				onChange={ ( v ) => onChange( v || '' ) }
			/>
		</div>
	);
}

function Field( { field, value, onChange } ) {
	switch ( field.type ) {
		case 'textarea':
			return (
				<TextareaControl
					__nextHasNoMarginBottom
					label={ field.label }
					help={ field.help }
					value={ value || '' }
					onChange={ onChange }
				/>
			);
		case 'select':
			return (
				<SelectControl
					__nextHasNoMarginBottom
					label={ field.label }
					help={ field.help }
					value={ value === undefined ? undefined : String( value ) }
					options={ field.options.map( ( o ) => ( { ...o, value: String( o.value ) } ) ) }
					onChange={ ( v ) => onChange( field.number ? Number( v ) : v ) }
				/>
			);
		case 'toggle':
			return (
				<ToggleControl
					__nextHasNoMarginBottom
					label={ field.label }
					help={ field.help }
					checked={ !! value }
					onChange={ onChange }
				/>
			);
		case 'link':
			return <LinkField field={ field } value={ value } onChange={ onChange } />;
		case 'strings':
			return <StringsField field={ field } value={ value } onChange={ onChange } />;
		case 'repeater':
			return <RepeaterField field={ field } value={ value } onChange={ onChange } />;
		case 'image':
			return <ImageField field={ field } value={ value } onChange={ onChange } />;
		case 'video':
			return <VideoField field={ field } value={ value } onChange={ onChange } />;
		case 'icon':
			return <IconField field={ field } value={ value } onChange={ onChange } />;
		default:
			return (
				<TextControl
					__nextHasNoMarginBottom
					className={ field.ltr ? 'nv-ltr' : undefined }
					label={ field.label }
					help={ field.help }
					value={ value || '' }
					onChange={ onChange }
				/>
			);
	}
}

/** The fields of a block (or of one repeater item). onChange receives a partial update. */
export function Fields( { fields, values, onChange } ) {
	return (
		<div className="nv-fields">
			{ fields.map( ( field ) => (
				<Field
					key={ field.key }
					field={ field }
					value={ values?.[ field.key ] }
					onChange={ ( v ) => onChange( { [ field.key ]: v } ) }
				/>
			) ) }
		</div>
	);
}
