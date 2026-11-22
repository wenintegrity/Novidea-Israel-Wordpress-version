<?php
/**
 * Plugin Name: Novidea Import (one-time)
 * Description: Imports the Novidea Israel content (pages, media, menus, settings) into this site. Tools → Novidea import. Delete this plugin after the import.
 * Version:     1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author:      Novidea
 *
 * The wp-admin way to run import.php, for servers without SSH. Shipped as novidea-import.php in the
 * bundle made by `npm run migrate:bundle` (migration/out/novidea-import.zip). Same safety checks as
 * the command line: the administrator types this site's address, and the site must be a single
 * site with no content of its own.
 *
 * @package novidea-israel
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	static function (): void {
		add_management_page( 'Novidea import', 'Novidea import', 'manage_options', 'novidea-import', 'novidea_import_page' );
	}
);

/** Runs import.php as the wp-admin tool. Returns [log lines, finished]. */
function novidea_import_run( string $typed_address ): array {
	global $nv_log;
	define( 'NOVIDEA_IMPORT_ADMIN', true );
	$args = array( $typed_address ); // Read by import.php's safety checks.
	try {
		include __DIR__ . '/import.php';
	} catch ( Novidea_Import_Stopped $e ) {
		return array( (array) $nv_log, false );
	} catch ( Throwable $e ) {
		$nv_log[] = 'ERROR: ' . $e->getMessage() . ' (' . basename( $e->getFile() ) . ':' . $e->getLine() . ')';
		return array( (array) $nv_log, false );
	}
	return array( (array) $nv_log, in_array( 'DONE', (array) $nv_log, true ) );
}

function novidea_import_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Only administrators can run the import.' );
	}

	$host    = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$result  = null;
	$content = json_decode( (string) file_get_contents( __DIR__ . '/out/content.json' ), true );
	$pages   = is_array( $content ) ? array_column( $content['pages'], 'slug' ) : array();

	if ( isset( $_POST['novidea_import'] ) ) {
		check_admin_referer( 'novidea_import' );
		$typed  = sanitize_text_field( wp_unslash( $_POST['site_address'] ?? '' ) );
		$result = novidea_import_run( $typed );
	}

	// Read-only checks shown before running (the importer checks again when it runs).
	$theme_ok  = wp_get_theme( 'novidea-israel' )->exists();
	$plugin_ok = file_exists( WP_PLUGIN_DIR . '/novidea-blocks/novidea-blocks.php' );
	$existing  = get_posts(
		array(
			'post_type'   => array( 'post', 'page' ),
			'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'numberposts' => 50,
		)
	);
	$allowed   = array_merge( $pages, array( 'sample-page', 'privacy-policy', 'hello-world' ) );
	$foreign   = array_filter( $existing, static fn ( $p ) => ! in_array( $p->post_name, $allowed, true ) );
	$checks    = array(
		array( $theme_ok && $plugin_ok, $theme_ok && $plugin_ok ? 'Novidea theme and plugin are installed' : 'Install the Novidea theme (novidea-israel.zip) and plugin (novidea-blocks.zip) first' ),
		array( ! is_multisite(), 'Single site (not a Multisite network)' ),
		array( ! $foreign, $foreign ? 'This site already has content of its own: ' . implode( ', ', array_map( static fn ( $p ) => '"' . $p->post_title . '"', array_slice( $foreign, 0, 5 ) ) ) : 'No content of its own yet (only WordPress samples or Novidea pages)' ),
	);
	$ready     = ! in_array( false, array_column( $checks, 0 ), true );
	?>
	<div class="wrap" dir="ltr" style="max-width: 52rem">
		<h1>Novidea import</h1>

		<?php if ( $result ) : ?>
			<div class="notice <?php echo $result[1] ? 'notice-success' : 'notice-error'; ?>">
				<p><strong><?php echo $result[1] ? 'Import finished.' : 'The import did not finish.'; ?></strong>
				<?php if ( $result[1] ) : ?>
					Open the site to check it. Then delete this plugin: Plugins → Novidea Import (one-time) → Deactivate → Delete. If your host caches pages (Cloudways: Varnish), purge the cache.
				<?php else : ?>
					See the log below. If it stopped part-way (for example a time-out while processing pictures), run it again: finished parts are reused, not duplicated.
				<?php endif; ?>
				</p>
			</div>
			<h2>Log</h2>
			<pre style="background:#fff;border:1px solid #c3c4c7;padding:1em;white-space:pre-wrap;direction:ltr"><?php echo esc_html( implode( "\n", $result[0] ) ); ?></pre>
		<?php endif; ?>

		<?php if ( ! $result || ! $result[1] ) : ?>
			<p>This imports the Novidea Israel content into <strong>this site</strong>:</p>
			<table class="widefat striped" style="max-width: 40rem">
				<tbody>
					<tr><th>Site address</th><td><code><?php echo esc_html( home_url() ); ?></code></td></tr>
					<tr><th>Database</th><td><code><?php echo esc_html( DB_NAME ); ?></code></td></tr>
				</tbody>
			</table>
			<p>It creates <?php echo count( $pages ); ?> pages, the media library items, the Header and Footer menus and the shared texts; sets the site title, Hebrew language, Jerusalem time zone and <code>/%postname%</code> permalinks; turns on "Discourage search engines"; activates the Novidea theme and plugin; and moves WordPress's sample page and post to the trash.</p>

			<h2>Checks</h2>
			<ul>
				<?php foreach ( $checks as list( $ok, $label ) ) : ?>
					<li><?php echo $ok ? '✅' : '❌'; ?> <?php echo esc_html( $label ); ?></li>
				<?php endforeach; ?>
			</ul>

			<?php if ( $ready ) : ?>
				<form method="post">
					<?php wp_nonce_field( 'novidea_import' ); ?>
					<p>
						<label for="site_address"><strong>To confirm, type this site's address:</strong> <code><?php echo esc_html( $host ); ?></code></label><br>
						<input type="text" id="site_address" name="site_address" class="regular-text" dir="ltr" autocomplete="off" required>
					</p>
					<p><button type="submit" name="novidea_import" value="1" class="button button-primary">Import Novidea content</button></p>
					<p class="description">Takes a minute or two (pictures are resized). Don't close the page until it finishes.</p>
				</form>
			<?php else : ?>
				<p><strong>Not available yet: a check failed (see above).</strong></p>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}
