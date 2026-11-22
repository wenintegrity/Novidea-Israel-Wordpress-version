<?php
/**
 * Page settings stored as post meta and edited in the "Novidea" panel of the page editor:
 *   _novidea_seo_title        <title> (≤ 60 characters, docs/seo.md)
 *   _novidea_seo_description  meta description (≤ 155 characters)
 *   _novidea_noindex          keep the page out of search results and sitemap.xml (campaign pages)
 *   _novidea_hide_lead_cta    hide the shared lead banner at the bottom (e.g. the contact page)
 *
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function (): void {
		$auth = static fn ( $allowed, $meta_key, $post_id ) => current_user_can( 'edit_post', $post_id );
		$meta = array(
			'_novidea_seo_title'       => array( 'string', 'sanitize_text_field' ),
			'_novidea_seo_description' => array( 'string', 'sanitize_textarea_field' ),
			'_novidea_noindex'         => array( 'boolean', 'rest_sanitize_boolean' ),
			'_novidea_hide_lead_cta'   => array( 'boolean', 'rest_sanitize_boolean' ),
		);
		foreach ( $meta as $key => list( $type, $sanitize ) ) {
			register_post_meta(
				'page',
				$key,
				array(
					'type'              => $type,
					'single'            => true,
					'show_in_rest'      => true,
					'default'           => 'boolean' === $type ? false : '',
					'sanitize_callback' => $sanitize,
					'auth_callback'     => $auth,
				)
			);
		}
	}
);
