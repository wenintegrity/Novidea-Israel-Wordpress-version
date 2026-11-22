<?php
/**
 * Block registration. Every block is dynamic: block.json + render.php in blocks/<name>/, rendered by
 * PHP with the same markup as the Astro components. The editor (src/ → build/) shows a live server
 * preview and a form for the block's fields.
 *
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

/** Names of the blocks in blocks/, in inserter order. */
function novidea_block_names(): array {
	return array( 'hero', 'page-header', 'text', 'features', 'names', 'links', 'quotes', 'location', 'prose', 'lead-form' );
}

function novidea_blocks_version( string $relative_path ): string {
	$file = NOVIDEA_BLOCKS_DIR . '/' . $relative_path;
	return file_exists( $file ) ? (string) filemtime( $file ) : '0.1.0';
}

add_action(
	'init',
	static function (): void {
		// Styles shared by several blocks (stripe motif, "coming soon" badge, video). Blocks list it in block.json.
		wp_register_style( 'novidea-blocks-common', NOVIDEA_BLOCKS_URL . 'assets/css/common.css', array(), novidea_blocks_version( 'assets/css/common.css' ) );

		// The editing form inside the editor canvas (an iframe): listed as each block's editorStyle.
		wp_register_style( 'novidea-blocks-editor-ui', NOVIDEA_BLOCKS_URL . 'build/index.css', array(), novidea_blocks_version( 'build/index.css' ) );

		// Cookie consent API (window.nvConsent), needed by the HubSpot form.
		wp_register_script(
			'novidea-consent',
			NOVIDEA_BLOCKS_URL . 'assets/js/consent.js',
			array(),
			novidea_blocks_version( 'assets/js/consent.js' ),
			array( 'strategy' => 'defer', 'in_footer' => false )
		);

		// HubSpot form loader: the lead-form block's view script.
		wp_register_script(
			'novidea-hubspot-form',
			NOVIDEA_BLOCKS_URL . 'assets/js/hubspot-form.js',
			array( 'novidea-consent' ),
			novidea_blocks_version( 'assets/js/hubspot-form.js' ),
			array( 'strategy' => 'defer', 'in_footer' => true )
		);

		foreach ( novidea_block_names() as $name ) {
			register_block_type( NOVIDEA_BLOCKS_DIR . '/blocks/' . $name );
		}
	}
);

// Inserter category.
add_filter(
	'block_categories_all',
	static function ( array $categories ): array {
		array_unshift(
			$categories,
			array(
				'slug'  => 'novidea',
				'title' => __( 'Novidea sections', 'novidea-blocks' ),
				'icon'  => null,
			)
		);
		return $categories;
	}
);

// Editor: block forms, previews and the page's SEO panel (one bundle, built from src/).
add_action(
	'enqueue_block_editor_assets',
	static function (): void {
		$asset_file = NOVIDEA_BLOCKS_DIR . '/build/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return; // Not built yet: run `npm run build`.
		}
		$asset = require $asset_file;
		wp_enqueue_script( 'novidea-blocks-editor', NOVIDEA_BLOCKS_URL . 'build/index.js', $asset['dependencies'], $asset['version'], true );
		wp_set_script_translations( 'novidea-blocks-editor', 'novidea-blocks', NOVIDEA_BLOCKS_DIR . '/languages' );

		// The icon picker needs the catalogue and where the files are.
		$icons = array();
		foreach ( novidea_icons() as $id => $icon ) {
			$icons[] = array(
				'id'    => $id,
				'label' => $icon['he'] ?? ( $icon['en'] ?? $id ),
				'en'    => $icon['en'] ?? '',
			);
		}
		wp_add_inline_script(
			'novidea-blocks-editor',
			'window.novideaBlocks = ' . wp_json_encode(
				array(
					'iconBase' => NOVIDEA_BLOCKS_URL . 'assets/icons/',
					'icons'    => $icons,
				)
			) . ';',
			'before'
		);
	}
);

// Video captions (WebVTT) can be uploaded to the media library.
add_filter(
	'upload_mimes',
	static function ( array $mimes ): array {
		$mimes['vtt'] = 'text/vtt';
		return $mimes;
	}
);
