<?php
/**
 * Starter page patterns: one per page type of the site. WordPress offers them when a new page is
 * created ("Choose a pattern"), and they're in the inserter under Patterns → "Novidea pages".
 * Markup in patterns/<name>.html, generated from the Astro content by `npm run migrate`.
 *
 * Page settings a pattern can't carry (set them in the "Novidea: search and page" panel):
 *   - landing pages: "Keep out of search results (noindex)"
 *   - the lead banner hides itself on pages that contain the lead form (theme: novidea_lead_cta()).
 *
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function (): void {
		register_block_pattern_category( 'novidea-pages', array( 'label' => __( 'Novidea pages', 'novidea-blocks' ) ) );

		$patterns = array(
			'home'     => array( __( 'Home page', 'novidea-blocks' ), __( 'Hero, text with video, customer logos and links to the main pages.', 'novidea-blocks' ) ),
			'landing'  => array( __( 'Landing page', 'novidea-blocks' ), __( 'Campaign page, the whole story on one page: problem, solution, product, proof, form. Turn on "Keep out of search results".', 'novidea-blocks' ) ),
			'internal' => array( __( 'Internal page', 'novidea-blocks' ), __( 'Page header, text and feature cards, like "The problem and the solution".', 'novidea-blocks' ) ),
			'contact'  => array( __( 'Contact page', 'novidea-blocks' ), __( 'Page header, the HubSpot lead form and the office location.', 'novidea-blocks' ) ),
			'legal'    => array( __( 'Legal page', 'novidea-blocks' ), __( 'Page header and numbered legal text, like the privacy policy or the accessibility statement.', 'novidea-blocks' ) ),
		);

		foreach ( $patterns as $name => list( $title, $description ) ) {
			$file = NOVIDEA_BLOCKS_DIR . "/patterns/{$name}.html";
			if ( ! file_exists( $file ) ) {
				continue;
			}
			register_block_pattern(
				"novidea/page-{$name}",
				array(
					'title'         => $title,
					'description'   => $description,
					'categories'    => array( 'novidea-pages' ),
					'blockTypes'    => array( 'core/post-content' ),
					'postTypes'     => array( 'page' ),
					'viewportWidth' => 1440,
					'content'       => (string) file_get_contents( $file ),
				)
			);
		}
	}
);
