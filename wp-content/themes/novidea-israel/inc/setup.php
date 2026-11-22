<?php
/**
 * Theme supports, menus, and a lean <head> (no emoji script, generator tag or feed links).
 *
 * @package novidea-israel
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain( 'novidea-israel', get_template_directory() . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );

		// Editor canvas: same CSS as the site, so block previews look like the real page.
		add_theme_support( 'editor-styles' );
		add_editor_style(
			array(
				novidea_bootstrap()['css'][ is_rtl() ? 'rtl' : 'ltr' ]['href'],
				'assets/css/fonts.css',
				'assets/css/tokens.css',
				'assets/css/brand.css',
				'assets/css/editor.css',
			)
		);
	}
);

add_action(
	'init',
	static function (): void {
		register_nav_menus(
			array(
				'primary' => __( 'Header menu', 'novidea-israel' ),
				'footer'  => __( 'Footer menu', 'novidea-israel' ),
			)
		);
	}
);

// ---------- A lean <head> (the Astro site shipped no extra scripts or links) ----------
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );

// Only the CSS of blocks actually on the page (keeps the performance budget, AGENTS.md §3.4).
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

// The site has no blog: no comments anywhere.
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_action(
	'init',
	static function (): void {
		remove_post_type_support( 'page', 'comments' );
		remove_post_type_support( 'post', 'comments' );
	}
);

// Skip link: the very first thing in <body> (design-system.md §10.1). The plugin's cookie banner hooks in after it.
add_action(
	'wp_body_open',
	static function (): void {
		printf( '<a class="skip-link" href="#main">%s</a>' . "\n", esc_html__( 'Skip to main content', 'novidea-israel' ) );
	},
	5
);
