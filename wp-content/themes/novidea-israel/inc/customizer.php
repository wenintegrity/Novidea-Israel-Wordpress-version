<?php
/**
 * Site-wide content that every page shares (src/content/shared/he.yaml in the Astro site):
 * the header button, the lead banner at the bottom of pages, and the footer blurb.
 * Edited in Appearance → Customize → Novidea.
 *
 * @package novidea-israel
 */

defined( 'ABSPATH' ) || exit;

/** Every shared setting: id => [label, control type]. Values are theme mods. */
function novidea_customizer_fields(): array {
	return array(
		'novidea_header_cta_label'   => array( __( 'Header button: label', 'novidea-israel' ), 'text' ),
		'novidea_header_cta_page'    => array( __( 'Header button: page', 'novidea-israel' ), 'dropdown-pages' ),
		'novidea_lead_title'         => array( __( 'Lead banner: title', 'novidea-israel' ), 'text' ),
		'novidea_lead_body'          => array( __( 'Lead banner: text', 'novidea-israel' ), 'textarea' ),
		'novidea_lead_button_label'  => array( __( 'Lead banner: button label', 'novidea-israel' ), 'text' ),
		'novidea_lead_button_page'   => array( __( 'Lead banner: button page', 'novidea-israel' ), 'dropdown-pages' ),
		'novidea_footer_about'       => array( __( 'Footer: about Novidea', 'novidea-israel' ), 'textarea' ),
	);
}

add_action(
	'customize_register',
	static function ( WP_Customize_Manager $wp_customize ): void {
		$wp_customize->add_section(
			'novidea_shared',
			array(
				'title'       => __( 'Novidea: shared content', 'novidea-israel' ),
				'description' => __( 'Text that appears on every page. Turn the lead banner off for a single page in the page editor (Novidea panel).', 'novidea-israel' ),
				'priority'    => 30,
			)
		);

		foreach ( novidea_customizer_fields() as $id => list( $label, $type ) ) {
			$is_page = 'dropdown-pages' === $type;
			$wp_customize->add_setting(
				$id,
				array(
					'type'              => 'theme_mod',
					'sanitize_callback' => $is_page ? 'absint' : ( 'textarea' === $type ? 'sanitize_textarea_field' : 'sanitize_text_field' ),
					'transport'         => 'refresh',
				)
			);
			$wp_customize->add_control(
				$id,
				array(
					'label'   => $label,
					'section' => 'novidea_shared',
					'type'    => $type,
				)
			);
		}
	}
);
