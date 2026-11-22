/**
 * "Novidea" panel in the page settings sidebar: search title and description, noindex, and the lead
 * banner switch (post meta registered in includes/meta.php).
 */
import { __, sprintf } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel, store as editorStore } from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { TextControl, TextareaControl, ToggleControl } from '@wordpress/components';

function counter( text, max ) {
	const length = ( text || '' ).length;
	/* translators: 1: characters typed, 2: recommended maximum. */
	const message = sprintf( __( '%1$d of %2$d characters', 'novidea-blocks' ), length, max );
	return length > max ? `${ message } — ${ __( 'too long, search engines will cut it', 'novidea-blocks' ) }` : message;
}

function NovideaPanel() {
	const { postType, postId } = useSelect(
		( select ) => ( {
			postType: select( editorStore ).getCurrentPostType(),
			postId: select( editorStore ).getCurrentPostId(),
		} ),
		[]
	);
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta', postId );

	if ( postType !== 'page' || ! meta ) {
		return null;
	}
	const set = ( key ) => ( value ) => setMeta( { ...meta, [ key ]: value } );

	return (
		<PluginDocumentSettingPanel name="novidea-page" title={ __( 'Novidea: search and page', 'novidea-blocks' ) }>
			<TextControl
				__nextHasNoMarginBottom
				label={ __( 'Search title', 'novidea-blocks' ) }
				help={ `${ counter( meta._novidea_seo_title, 60 ) }. ${ __( 'Shown in Google and in the browser tab. Empty = the page title.', 'novidea-blocks' ) }` }
				value={ meta._novidea_seo_title || '' }
				onChange={ set( '_novidea_seo_title' ) }
			/>
			<TextareaControl
				__nextHasNoMarginBottom
				label={ __( 'Search description', 'novidea-blocks' ) }
				help={ counter( meta._novidea_seo_description, 155 ) }
				value={ meta._novidea_seo_description || '' }
				onChange={ set( '_novidea_seo_description' ) }
			/>
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Keep out of search results (noindex)', 'novidea-blocks' ) }
				help={ __( 'For campaign landing pages. Also left out of sitemap.xml.', 'novidea-blocks' ) }
				checked={ !! meta._novidea_noindex }
				onChange={ set( '_novidea_noindex' ) }
			/>
			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Hide the lead banner at the bottom', 'novidea-blocks' ) }
				help={ __( 'For pages that already have the form. The banner text is edited in Appearance → Customize.', 'novidea-blocks' ) }
				checked={ !! meta._novidea_hide_lead_cta }
				onChange={ set( '_novidea_hide_lead_cta' ) }
			/>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'novidea-page-settings', { render: NovideaPanel } );
