/**
 * The editing form of each block: which fields it has, in what order, with what controls.
 * Field types: text, textarea, select, toggle, link, strings, image, video, icon, repeater.
 * Attribute names and defaults live in each blocks/<name>/block.json.
 */
import { __ } from '@wordpress/i18n';

const surface = ( fallback ) => ( {
	key: 'surface',
	type: 'select',
	label: __( 'Background', 'novidea-blocks' ),
	help:
		fallback === 'navy'
			? __( 'Navy by default for quotes.', 'novidea-blocks' )
			: undefined,
	options: [
		{ value: 'light', label: __( 'White', 'novidea-blocks' ) },
		{ value: 'subtle', label: __( 'Light grey', 'novidea-blocks' ) },
		{ value: 'tint', label: __( 'Light yellow', 'novidea-blocks' ) },
		{ value: 'navy', label: __( 'Navy', 'novidea-blocks' ) },
	],
} );

const anchor = {
	key: 'anchor',
	type: 'text',
	label: __( 'Section ID (anchor)', 'novidea-blocks' ),
	help: __(
		'Lets buttons jump to this section, e.g. #lead-form. English letters, numbers and dashes only.',
		'novidea-blocks'
	),
	ltr: true,
};

const title = ( label = __( 'Title', 'novidea-blocks' ) ) => ( {
	key: 'title',
	type: 'text',
	label,
} );

const picture = {
	key: 'picture',
	type: 'image',
	label: __( 'Picture', 'novidea-blocks' ),
};

const icon = {
	key: 'icon',
	type: 'icon',
	label: __( 'Icon', 'novidea-blocks' ),
};

export const SCHEMAS = {
	'novidea/hero': {
		required: 'titleStrong',
		fields: [
			{
				key: 'titleStrong',
				type: 'text',
				label: __( 'Title: bold part', 'novidea-blocks' ),
			},
			{
				key: 'titleRest',
				type: 'text',
				label: __( 'Title: regular part', 'novidea-blocks' ),
			},
			{
				key: 'lead',
				type: 'textarea',
				label: __( 'Intro text', 'novidea-blocks' ),
			},
			{
				key: 'primaryCta',
				type: 'link',
				label: __( 'Main button', 'novidea-blocks' ),
			},
			{
				key: 'secondaryCta',
				type: 'link',
				label: __( 'Second button (optional)', 'novidea-blocks' ),
			},
			picture,
			{
				key: 'redLine',
				type: 'toggle',
				label: __( 'Red line under the yellow panel', 'novidea-blocks' ),
				help: __( 'As on internal pages. Off on the home page.', 'novidea-blocks' ),
			},
		],
	},

	'novidea/page-header': {
		required: 'title',
		fields: [
			title( __( 'Page title (h1)', 'novidea-blocks' ) ),
			{
				key: 'intro',
				type: 'strings',
				label: __( 'Intro paragraphs', 'novidea-blocks' ),
				addLabel: __( 'Add paragraph', 'novidea-blocks' ),
			},
			{
				...picture,
				help: __( 'Without a picture, a navy block with yellow stripes is shown on desktop.', 'novidea-blocks' ),
			},
		],
	},

	'novidea/text': {
		required: 'paragraphs',
		fields: [
			title(),
			{
				key: 'paragraphs',
				type: 'strings',
				label: __( 'Paragraphs', 'novidea-blocks' ),
				help: __( 'The first paragraph is shown slightly larger.', 'novidea-blocks' ),
				addLabel: __( 'Add paragraph', 'novidea-blocks' ),
			},
			{
				...picture,
				help: __( 'A picture or a video beside the text, not both.', 'novidea-blocks' ),
			},
			{
				key: 'video',
				type: 'video',
				label: __( 'Video', 'novidea-blocks' ),
			},
			surface(),
			anchor,
		],
	},

	'novidea/features': {
		required: 'items',
		fields: [
			title(),
			{
				key: 'intro',
				type: 'textarea',
				label: __( 'Intro text', 'novidea-blocks' ),
			},
			{
				key: 'columns',
				type: 'select',
				number: true,
				label: __( 'Cards per row on desktop', 'novidea-blocks' ),
				options: [
					{ value: 3, label: '3' },
					{ value: 2, label: '2' },
				],
			},
			{
				key: 'items',
				type: 'repeater',
				label: __( 'Cards', 'novidea-blocks' ),
				addLabel: __( 'Add card', 'novidea-blocks' ),
				itemLabel: ( item ) => item.title || item.body,
				newItem: { icon: '', title: '', body: '', soon: false, soonNote: '' },
				fields: [
					icon,
					title(),
					{
						key: 'body',
						type: 'textarea',
						label: __( 'Text', 'novidea-blocks' ),
					},
					{
						key: 'soon',
						type: 'toggle',
						label: __( 'The whole card is "coming soon"', 'novidea-blocks' ),
					},
					{
						key: 'soonNote',
						type: 'text',
						label: __( 'Coming soon addition (optional)', 'novidea-blocks' ),
						help: __( 'An upcoming addition to an available feature, shown with a "coming soon" label.', 'novidea-blocks' ),
					},
				],
			},
			picture,
			surface(),
			anchor,
		],
	},

	'novidea/names': {
		required: 'names',
		fields: [
			title(),
			{
				key: 'names',
				type: 'repeater',
				label: __( 'Customers', 'novidea-blocks' ),
				addLabel: __( 'Add customer', 'novidea-blocks' ),
				itemLabel: ( item ) => item.name,
				newItem: { name: '', logo: {} },
				fields: [
					{
						key: 'name',
						type: 'text',
						label: __( 'Name', 'novidea-blocks' ),
						help: __( 'Also the logo\'s alternative text: write the text that appears in the logo.', 'novidea-blocks' ),
					},
					{
						key: 'logo',
						type: 'image',
						alt: false,
						label: __( 'Logo (optional)', 'novidea-blocks' ),
					},
				],
			},
			{
				key: 'more',
				type: 'text',
				label: __( 'Text after the list (e.g. "and more")', 'novidea-blocks' ),
			},
			surface(),
			anchor,
		],
	},

	'novidea/links': {
		required: 'items',
		fields: [
			title( __( 'Title (optional)', 'novidea-blocks' ) ),
			{
				key: 'items',
				type: 'repeater',
				label: __( 'Cards', 'novidea-blocks' ),
				addLabel: __( 'Add card', 'novidea-blocks' ),
				itemLabel: ( item ) => item.title,
				newItem: { icon: '', title: '', body: '', href: '' },
				fields: [
					icon,
					title(),
					{
						key: 'body',
						type: 'textarea',
						label: __( 'Summary', 'novidea-blocks' ),
					},
					{
						key: 'href',
						type: 'text',
						ltr: true,
						label: __( 'Link', 'novidea-blocks' ),
						help: __( 'A page address, e.g. /solution', 'novidea-blocks' ),
					},
				],
			},
			surface(),
			anchor,
		],
	},

	'novidea/quotes': {
		required: 'items',
		fields: [
			title(),
			{
				key: 'items',
				type: 'repeater',
				label: __( 'Quotes', 'novidea-blocks' ),
				addLabel: __( 'Add quote', 'novidea-blocks' ),
				itemLabel: ( item ) => item.name,
				newItem: { quote: '', name: '', role: '', company: '' },
				fields: [
					{
						key: 'quote',
						type: 'textarea',
						label: __( 'Quote', 'novidea-blocks' ),
						help: __( 'Without quotation marks: they are added automatically.', 'novidea-blocks' ),
					},
					{ key: 'name', type: 'text', label: __( 'Name', 'novidea-blocks' ) },
					{ key: 'role', type: 'text', label: __( 'Role', 'novidea-blocks' ) },
					{ key: 'company', type: 'text', label: __( 'Company', 'novidea-blocks' ) },
				],
			},
			surface( 'navy' ),
			anchor,
		],
	},

	'novidea/location': {
		required: 'place',
		fields: [
			title(),
			{ key: 'place', type: 'text', label: __( 'Building', 'novidea-blocks' ) },
			{ key: 'address', type: 'text', label: __( 'Address', 'novidea-blocks' ) },
			{
				key: 'directions',
				type: 'textarea',
				label: __( 'Directions (optional)', 'novidea-blocks' ),
			},
			{
				key: 'mapQuery',
				type: 'text',
				label: __( 'Search text for Waze and Google Maps', 'novidea-blocks' ),
				help: __( 'Leave empty to use the building and address.', 'novidea-blocks' ),
			},
			surface(),
			anchor,
		],
	},

	'novidea/prose': {
		required: 'sections',
		help: __(
			'Links: [text](/page) or [text](https://…). [text](#cookie-settings) opens the cookie settings. "[לעדכון: …]" is highlighted until filled in.',
			'novidea-blocks'
		),
		fields: [
			{
				key: 'sections',
				type: 'repeater',
				label: __( 'Sections (numbered automatically)', 'novidea-blocks' ),
				addLabel: __( 'Add section', 'novidea-blocks' ),
				itemLabel: ( item ) => item.title,
				newItem: { title: '', paragraphs: [], bullets: [], after: [] },
				fields: [
					title(),
					{
						key: 'paragraphs',
						type: 'strings',
						label: __( 'Paragraphs', 'novidea-blocks' ),
						addLabel: __( 'Add paragraph', 'novidea-blocks' ),
					},
					{
						key: 'bullets',
						type: 'strings',
						label: __( 'Bullet list', 'novidea-blocks' ),
						addLabel: __( 'Add bullet', 'novidea-blocks' ),
					},
					{
						key: 'after',
						type: 'strings',
						label: __( 'Paragraphs after the list', 'novidea-blocks' ),
						addLabel: __( 'Add paragraph', 'novidea-blocks' ),
					},
				],
			},
			anchor,
		],
	},

	'novidea/lead-form': {
		required: null,
		help: __(
			'The form loads only after the visitor allows marketing cookies. Its fields and labels are edited in HubSpot.',
			'novidea-blocks'
		),
		fields: [
			title( __( 'Title (default: "Get in touch")', 'novidea-blocks' ) ),
			{ key: 'intro', type: 'textarea', label: __( 'Intro text', 'novidea-blocks' ) },
			{
				key: 'portalId',
				type: 'text',
				ltr: true,
				label: __( 'HubSpot portal ID', 'novidea-blocks' ),
			},
			{
				key: 'formId',
				type: 'text',
				ltr: true,
				label: __( 'HubSpot form ID', 'novidea-blocks' ),
			},
			{
				key: 'region',
				type: 'text',
				ltr: true,
				label: __( 'HubSpot region', 'novidea-blocks' ),
				help: __( 'Usually na1.', 'novidea-blocks' ),
			},
			{
				key: 'fields',
				type: 'strings',
				label: __( 'Placeholder field list', 'novidea-blocks' ),
				help: __( 'Shown only while the HubSpot IDs are empty.', 'novidea-blocks' ),
				addLabel: __( 'Add field', 'novidea-blocks' ),
			},
			anchor,
		],
	},
};

/** True when a new block still needs its main content (it then opens in the form). */
export function isEmpty( name, attributes ) {
	const key = SCHEMAS[ name ]?.required;
	if ( ! key ) {
		return false;
	}
	const value = attributes[ key ];
	return Array.isArray( value ) ? value.length === 0 : ! value;
}
