<?php
/**
 * Plugin Name:       Novidea Blocks
 * Description:       Page sections for the Novidea Israel site (hero, page header, text, features, customers, links, quotes, location, legal text, lead form), plus page SEO, cookie consent and the HubSpot form.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Novidea
 * License:           Proprietary
 * Text Domain:       novidea-blocks
 * Domain Path:       /languages
 *
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

define( 'NOVIDEA_BLOCKS_FILE', __FILE__ );
define( 'NOVIDEA_BLOCKS_DIR', __DIR__ );
define( 'NOVIDEA_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

require __DIR__ . '/includes/helpers.php';
require __DIR__ . '/includes/blocks.php';
require __DIR__ . '/includes/meta.php';
require __DIR__ . '/includes/seo.php';
require __DIR__ . '/includes/consent.php';
require __DIR__ . '/includes/media.php';
require __DIR__ . '/includes/patterns.php';

add_action(
	'init',
	static function (): void {
		load_plugin_textdomain( 'novidea-blocks', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	},
	0
);
