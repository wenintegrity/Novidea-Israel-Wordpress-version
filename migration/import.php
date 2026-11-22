<?php
/**
 * Imports content.json into WordPress. Runs in three places:
 *   - locally, from `npm run migrate` (Playground blueprint; /novidea-migration = ./migration,
 *     /novidea-assets = the Astro project's src/assets)
 *   - on a server, from the bundle made by `npm run migrate:bundle`:
 *       - in wp-admin: the bundle zip is also a plugin (Tools → Novidea import, admin-plugin.php)
 *       - with WP-CLI: wp eval-file <bundle>/import.php <site address>
 *     (content in <bundle>/out, media in <bundle>/assets)
 *
 * Idempotent: media items are matched by their source path (_novidea_source meta), pages by slug,
 * menus by name. Running it again updates them to match the Astro content.
 *
 * @package novidea-israel
 */

if ( ! defined( 'ABSPATH' ) ) {
	require_once '/wordpress/wp-load.php'; // Playground
}

// This file may be included inside a function (the wp-admin tool): share its state as globals.
global $nv_log, $nv_media;

$nv_admin = defined( 'NOVIDEA_IMPORT_ADMIN' );
$nv_cli   = defined( 'WP_CLI' ) && WP_CLI;

if ( ! class_exists( 'Novidea_Import_Stopped' ) ) {
	/** A safety check failed: the import stopped before changing anything. */
	class Novidea_Import_Stopped extends RuntimeException {} // phpcs:ignore Generic.Files.OneObjectStructurePerFile
}
if ( $nv_admin && function_exists( 'set_time_limit' ) ) {
	set_time_limit( 900 ); // phpcs:ignore -- media processing can take a few minutes.
}
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/post.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/translation-install.php';

define( 'NV_OUT', __DIR__ . '/out' );
define( 'NV_ASSETS', is_dir( '/novidea-assets' ) ? '/novidea-assets' : __DIR__ . '/assets' );

$nv_log = array();
function nv_log( string $line ): void {
	global $nv_log;
	$nv_log[] = $line;
	@file_put_contents( NV_OUT . '/import-log.txt', implode( "\n", $nv_log ) . "\n" ); // phpcs:ignore -- best effort.
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		echo $line . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- CLI output.
	}
}

if ( ! $nv_admin ) {
	// The wp-admin tool catches errors itself and shows them on its page.
	set_exception_handler(
		static function ( Throwable $e ): void {
			if ( ! $e instanceof Novidea_Import_Stopped ) {
				nv_log( 'ERROR: ' . $e->getMessage() . ' (' . basename( $e->getFile() ) . ':' . $e->getLine() . ')' );
			}
			exit( 1 );
		}
	);
}

$data = json_decode( (string) file_get_contents( NV_OUT . '/content.json' ), true );
if ( ! is_array( $data ) ) {
	throw new RuntimeException( 'content.json is missing or invalid' );
}

if ( ! function_exists( 'nv_abort' ) ) {
	/** Stops the import without changing anything. */
	function nv_abort( string $reason ): void {
		nv_log( "STOPPED, nothing was changed: $reason" );
		throw new Novidea_Import_Stopped( $reason );
	}
}

// ---------- Safety checks on a server (WP-CLI or the wp-admin tool) ----------
// The importer rewrites site settings, pages and menus, so on a real server it only runs on the
// intended, fresh site. The site's address must be given: `wp eval-file import.php <address>`, or
// typed into the wp-admin tool.
if ( $nv_cli || $nv_admin ) {
	$nv_args     = isset( $args ) && is_array( $args ) ? $args : array(); // phpcs:ignore -- set by `wp eval-file` or the wp-admin tool.
	$nv_expected = strtolower( preg_replace( '~^https?://|/.*$~', '', (string) ( $nv_args[0] ?? '' ) ) );
	$nv_actual   = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	nv_log( 'Site: ' . home_url() . ' | database: ' . DB_NAME );

	if ( '' === $nv_expected ) {
		nv_abort( "give this site's address ({$nv_actual}) to confirm." );
	}
	if ( $nv_expected !== $nv_actual ) {
		nv_abort( "this WordPress is {$nv_actual}, not {$nv_expected}. Run it in the right site." );
	}
	if ( is_multisite() ) {
		nv_abort( 'this WordPress is a Multisite network; the importer only supports a single, separate site.' );
	}

	// Anything other than WordPress's sample content and our own pages means the site is in use.
	$nv_ours     = array_merge( array_column( $data['pages'], 'slug' ), array( 'sample-page', 'privacy-policy', 'hello-world' ) );
	$nv_existing = get_posts(
		array(
			'post_type'   => array( 'post', 'page' ),
			'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'numberposts' => 50,
		)
	);
	$nv_foreign  = array_filter( $nv_existing, static fn ( $p ) => ! in_array( $p->post_name, $nv_ours, true ) );
	if ( $nv_foreign && ! in_array( 'allow-existing', $nv_args, true ) ) {
		$nv_list = implode( ', ', array_map( static fn ( $p ) => "{$p->post_type} \"{$p->post_title}\" (/{$p->post_name})", array_slice( $nv_foreign, 0, 10 ) ) );
		nv_abort( "this site already has content: {$nv_list}. Is it really the new, empty subdomain?" );
	}
	nv_log( 'Safety checks passed: right address, single site, no existing content.' );
}

// Act as an administrator (needed for kses-free block content and media). In wp-admin: the user
// running the tool, who must be an administrator.
if ( ! $nv_admin ) {
	$admins = get_users( array( 'role' => 'administrator', 'number' => 1, 'orderby' => 'ID' ) );
	wp_set_current_user( $admins ? $admins[0]->ID : 1 );
}

// ---------- Theme, plugin, language ----------
if ( 'novidea-israel' !== get_option( 'stylesheet' ) ) {
	if ( ! wp_get_theme( 'novidea-israel' )->exists() ) {
		throw new RuntimeException( 'Theme novidea-israel is not installed (deploy the code first)' );
	}
	switch_theme( 'novidea-israel' );
	nv_log( 'Theme: activated novidea-israel' );
}
if ( ! is_plugin_active( 'novidea-blocks/novidea-blocks.php' ) ) {
	$activated = activate_plugin( 'novidea-blocks/novidea-blocks.php' );
	if ( is_wp_error( $activated ) ) {
		throw new RuntimeException( 'Plugin novidea-blocks: ' . $activated->get_error_message() );
	}
	nv_log( 'Plugin: activated novidea-blocks (reload pages after the import to see it fully)' );
}
if ( get_option( 'WPLANG' ) !== $data['site']['language'] ) {
	// Downloads the Hebrew language pack from wordpress.org when it isn't installed yet.
	$language = wp_download_language_pack( $data['site']['language'] );
	if ( $language ) {
		update_option( 'WPLANG', $language );
		nv_log( "Language: installed and set {$language}" );
	} else {
		nv_log( 'Language: could not download ' . $data['site']['language'] . '. Set Settings → General → Site Language to עברית by hand.' );
	}
}

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
flush_rewrite_rules( true ); // Also writes the .htaccess rules on Apache servers (Cloudways).
nv_log( 'Settings: site name, Asia/Jerusalem, d/m/Y, permalinks /%postname%, search engines discouraged' );
nv_log( 'Language: ' . get_option( 'WPLANG' ) . ' (RTL: ' . ( is_rtl() ? 'yes' : 'no' ) . ')' );

// ---------- Media ----------
$nv_media = array();

/**
 * Attachment for a file under the Astro src/assets: [id, url]. Created once, then reused.
 * When the file isn't in the bundle (e.g. the import without the video), an item with the same file
 * name already in the media library is used; otherwise null (the block then shows no file: a video
 * block shows its "coming soon" placeholder, and the file can be picked later in the editor).
 */
function nv_media( string $rel, string $alt = '' ): ?array {
	global $nv_media;
	if ( array_key_exists( $rel, (array) $nv_media ) ) {
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
	$source = NV_ASSETS . '/' . $rel;
	if ( ! $existing && ! file_exists( $source ) ) {
		// Not in the bundle: look for the same file uploaded by hand (Media → Add New).
		$existing = get_posts(
			array(
				'post_type'   => 'attachment',
				'post_status' => 'any',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_query'  => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'key'     => '_wp_attached_file',
						'value'   => '/' . sanitize_file_name( basename( $rel ) ),
						'compare' => 'LIKE',
					),
				),
			)
		);
		if ( ! $existing ) {
			nv_log( "  - skipped $rel: not in this import. Upload it later (Media → Add New) and pick it in its block." );
			$nv_media[ $rel ] = null;
			return null;
		}
		update_post_meta( (int) $existing[0], '_novidea_source', $rel );
		nv_log( "  = media $rel: using the copy already in the media library (#{$existing[0]})" );
	}
	if ( $existing ) {
		$id = (int) $existing[0];
	} else {
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

$nv_found = 0;
foreach ( $data['media'] as $item ) {
	$nv_found += nv_media( $item['file'], $item['alt'] ?? '' ) ? 1 : 0;
}
nv_log( "Media: {$nv_found} of " . count( $data['media'] ) . ' files in the media library' );

/** Replaces {"$media": path} placeholders with attachment data, recursively. */
function nv_resolve( $value ) {
	if ( ! is_array( $value ) ) {
		return $value;
	}
	foreach ( array( 'file' => array( 'id', 'url' ), 'poster' => array( 'posterId', 'posterUrl' ), 'captions' => array( 'captionsId', 'captionsUrl' ) ) as $key => list( $id_key, $url_key ) ) {
		// Video: {title, file: {$media}, poster: {$media}, captions: {$media}} → {title, id, url, posterId, …}
		if ( isset( $value[ $key ]['$media'] ) ) {
			$found = nv_media( $value[ $key ]['$media'] );
			if ( $found ) {
				list( $value[ $id_key ], $value[ $url_key ] ) = $found;
			}
			unset( $value[ $key ] );
		}
	}
	if ( isset( $value['$media'] ) ) {
		$found = nv_media( $value['$media'] );
		if ( ! $found ) {
			return array(); // No picture: the block shows its no-picture layout.
		}
		list( $id, $url ) = $found;
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

// Move WordPress's sample content to the trash (recoverable from wp-admin), if it's still there.
foreach ( array( array( 'sample-page', 'page' ), array( 'privacy-policy', 'page' ), array( 'hello-world', 'post' ) ) as list( $slug, $type ) ) {
	$sample = get_page_by_path( $slug, OBJECT, $type );
	if ( $sample && ! isset( $page_ids[ $slug ] ) && 'trash' !== $sample->post_status ) {
		wp_trash_post( $sample->ID );
		nv_log( "  moved sample $type /$slug to the trash" );
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
