<?php
/**
 * Imports migration/out/content.json into WordPress (run by migrate.mjs through a Playground
 * blueprint; /novidea-migration = ./migration, /novidea-assets = the Astro project's src/assets).
 *
 * Idempotent: media items are matched by their source path (_novidea_source meta), pages by slug,
 * menus by name. Running it again updates them to match the Astro content.
 *
 * @package novidea-israel
 */

require_once '/wordpress/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/post.php';

const NV_OUT    = '/novidea-migration/out';
const NV_ASSETS = '/novidea-assets';

$nv_log = array();
function nv_log( string $line ): void {
	global $nv_log;
	$nv_log[] = $line;
	file_put_contents( NV_OUT . '/import-log.txt', implode( "\n", $nv_log ) . "\n" );
}

set_exception_handler(
	static function ( Throwable $e ): void {
		nv_log( 'ERROR: ' . $e->getMessage() . ' (' . basename( $e->getFile() ) . ':' . $e->getLine() . ')' );
	}
);

$data = json_decode( (string) file_get_contents( NV_OUT . '/content.json' ), true );
if ( ! is_array( $data ) ) {
	throw new RuntimeException( 'content.json is missing or invalid' );
}

// Act as the first administrator (needed for kses-free block content and media).
$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID' ) );
wp_set_current_user( $admins ? $admins[0]->ID : 1 );

// ---------- Site settings ----------
update_option( 'blogname', $data['site']['name'] );
update_option( 'blogdescription', '' );
update_option( 'timezone_string', 'Asia/Jerusalem' );
update_option( 'date_format', 'd/m/Y' );
update_option( 'time_format', 'H:i' );
update_option( 'start_of_week', 0 ); // Sunday
update_option( 'default_comment_status', 'closed' );
update_option( 'default_ping_status', 'closed' );
update_option( 'blog_public', '0' ); // "Discourage search engines" until launch (docs/seo.md §6)
update_option( 'permalink_structure', '/%postname%' );
flush_rewrite_rules( false );
nv_log( 'Settings: site name, Asia/Jerusalem, d/m/Y, permalinks /%postname%, search engines discouraged' );
nv_log( 'Language: ' . get_option( 'WPLANG' ) . ' (RTL: ' . ( is_rtl() ? 'yes' : 'no' ) . ')' );

// ---------- Media ----------
$nv_media = array();

/** Attachment for a file under the Astro src/assets: [id, url]. Created once, then reused. */
function nv_media( string $rel, string $alt = '' ): array {
	global $nv_media;
	if ( isset( $nv_media[ $rel ] ) ) {
		return $nv_media[ $rel ];
	}
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'any',
			'meta_key'    => '_novidea_source', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $rel, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	if ( $existing ) {
		$id = (int) $existing[0];
	} else {
		$source = NV_ASSETS . '/' . $rel;
		if ( ! file_exists( $source ) ) {
			throw new RuntimeException( "Media file not found: $rel" );
		}
		$upload = wp_upload_dir();
		wp_mkdir_p( $upload['path'] );
		$name   = wp_unique_filename( $upload['path'], sanitize_file_name( basename( $rel ) ) );
		$target = $upload['path'] . '/' . $name;
		if ( ! copy( $source, $target ) ) {
			throw new RuntimeException( "Could not copy $rel to uploads" );
		}
		$type = wp_check_filetype( $name );
		$id   = wp_insert_attachment(
			array(
				'post_mime_type' => $type['type'],
				'post_title'     => $alt ? $alt : preg_replace( '/\.[^.]+$/', '', basename( $rel ) ),
				'post_status'    => 'inherit',
			),
			$target,
			0,
			true
		);
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( $id->get_error_message() );
		}
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $target ) );
		update_post_meta( $id, '_novidea_source', $rel );
		nv_log( "  + media $rel (#$id)" );
	}
	if ( $alt && wp_attachment_is_image( $id ) ) {
		update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	}
	$nv_media[ $rel ] = array( $id, (string) wp_get_attachment_url( $id ) );
	return $nv_media[ $rel ];
}

foreach ( $data['media'] as $item ) {
	nv_media( $item['file'], $item['alt'] ?? '' );
}
nv_log( 'Media: ' . count( $data['media'] ) . ' files in the media library' );

/** Replaces {"$media": path} placeholders with attachment data, recursively. */
function nv_resolve( $value ) {
	if ( ! is_array( $value ) ) {
		return $value;
	}
	foreach ( array( 'file' => array( 'id', 'url' ), 'poster' => array( 'posterId', 'posterUrl' ), 'captions' => array( 'captionsId', 'captionsUrl' ) ) as $key => list( $id_key, $url_key ) ) {
		// Video: {title, file: {$media}, poster: {$media}, captions: {$media}} → {title, id, url, posterId, …}
		if ( isset( $value[ $key ]['$media'] ) ) {
			list( $value[ $id_key ], $value[ $url_key ] ) = nv_media( $value[ $key ]['$media'] );
			unset( $value[ $key ] );
		}
	}
	if ( isset( $value['$media'] ) ) {
		list( $id, $url ) = nv_media( $value['$media'] );
		$resolved         = array(
			'id'  => $id,
			'url' => $url,
		);
		if ( array_key_exists( 'alt', $value ) ) {
			$resolved['alt'] = $value['alt'];
		}
		return $resolved;
	}
	return array_map( 'nv_resolve', $value );
}

// ---------- Pages ----------
$page_ids = array();
foreach ( $data['pages'] as $order => $page ) {
	$blocks = array_map(
		static fn ( $b ) => array(
			'blockName'    => $b['name'],
			'attrs'        => nv_resolve( $b['attrs'] ),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		),
		$page['blocks']
	);
	$content = implode( "\n\n", array_map( 'serialize_block', $blocks ) );

	$existing = get_page_by_path( $page['slug'], OBJECT, 'page' );
	$postarr  = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => $page['slug'],
		'post_title'   => $page['title'],
		'post_content' => $content,
		'menu_order'   => $order,
		'meta_input'   => array(
			'_novidea_seo_title'       => $page['seo']['title'],
			'_novidea_seo_description' => $page['seo']['description'],
			'_novidea_noindex'         => $page['seo']['noindex'] ? '1' : '',
			'_novidea_hide_lead_cta'   => $page['hideLeadCta'] ? '1' : '',
		),
	);
	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
	}
	// wp_insert_post() unslashes its input; the block JSON contains backslashes.
	$id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $id ) ) {
		throw new RuntimeException( "Page {$page['slug']}: " . $id->get_error_message() );
	}
	$page_ids[ $page['slug'] ] = (int) $id;
	nv_log( sprintf( '  %s page /%s (#%d, %d blocks)', $existing ? 'updated' : 'created', $page['slug'], $id, count( $blocks ) ) );
}
nv_log( 'Pages: ' . count( $page_ids ) );

// Remove WordPress's sample content, if it's still there.
foreach ( array( array( 'sample-page', 'page' ), array( 'privacy-policy', 'page' ), array( 'hello-world', 'post' ) ) as list( $slug, $type ) ) {
	$sample = get_page_by_path( $slug, OBJECT, $type );
	if ( $sample && ! isset( $page_ids[ $slug ] ) ) {
		wp_delete_post( $sample->ID, true );
		nv_log( "  removed sample $type /$slug" );
	}
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $page_ids[ $data['frontPage'] ] );
update_option( 'wp_page_for_privacy_policy', $page_ids[ $data['privacyPage'] ] );
nv_log( 'Front page: /' . $data['frontPage'] . ', privacy policy page: /' . $data['privacyPage'] );

// ---------- Menus ----------
$names     = array(
	'primary' => 'Header',
	'footer'  => 'Footer',
);
$locations = get_theme_mod( 'nav_menu_locations', array() );
foreach ( $data['menus'] as $location => $items ) {
	$menu    = wp_get_nav_menu_object( $names[ $location ] );
	$menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $names[ $location ] );
	foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $old ) {
		if ( $old ) {
			wp_delete_post( $old->ID, true );
		}
	}
	foreach ( $items as $position => $item ) {
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id' => $page_ids[ $item['page'] ],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-position'  => $position + 1,
				'menu-item-title'     => $item['title'] ?? '',
			)
		);
	}
	$locations[ $location ] = $menu_id;
	nv_log( "Menu {$names[$location]}: " . count( $items ) . ' items' );
}
set_theme_mod( 'nav_menu_locations', $locations );

// ---------- Customizer (shared content) ----------
foreach ( $data['themeMods'] as $key => $value ) {
	set_theme_mod( $key, is_array( $value ) ? $page_ids[ $value['page'] ] : $value );
}
nv_log( 'Customizer: ' . count( $data['themeMods'] ) . ' shared texts (theme: ' . get_option( 'stylesheet' ) . ')' );

nv_log( 'DONE' );
