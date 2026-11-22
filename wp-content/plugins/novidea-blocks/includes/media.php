<?php
/**
 * Image output (AGENTS.md §3.3: optimised images). The resized copies WordPress makes on upload are
 * WebP instead of PNG/JPEG, when the server's image library supports it. The original upload is kept.
 *
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'image_editor_output_format',
	static function ( array $formats ): array {
		// Checked once per request. The guard stops a loop: newer WordPress versions apply this
		// same filter while answering wp_image_editor_supports().
		static $webp = null;
		if ( null === $webp ) {
			$webp = false;
			$webp = wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
		}
		if ( $webp ) {
			$formats['image/png']  = 'image/webp';
			$formats['image/jpeg'] = 'image/webp';
		}
		return $formats;
	}
);
