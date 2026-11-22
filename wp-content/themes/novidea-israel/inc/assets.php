<?php
/**
 * Styles and scripts. Bootstrap 5.3.9 comes from jsDelivr with Subresource Integrity
 * (docs/decisions/0001-bootstrap-5-via-cdn.md): the RTL build on Hebrew pages, the LTR build otherwise.
 * The brand layer loads after it, so it wins.
 *
 * @package novidea-israel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bootstrap CDN files. Integrity hashes were computed from the CDN files and checked byte-for-byte
 * against the npm package bootstrap@5.3.9 on 19/11/2026. Update version and hashes together.
 */
function novidea_bootstrap(): array {
	$base = 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist';
	return array(
		'version' => '5.3.9',
		'origin'  => 'https://cdn.jsdelivr.net',
		'css'     => array(
			'ltr' => array(
				'href'      => $base . '/css/bootstrap.min.css',
				'integrity' => 'sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX',
			),
			'rtl' => array(
				'href'      => $base . '/css/bootstrap.rtl.min.css',
				'integrity' => 'sha384-fFMX5LqaHoT6olm8r/JTe3Dg4+vFEFXrVlhIo8IGs96iA+JhgKLLYwY4y2fHeyMY',
			),
		),
		'js'      => array(
			'src'       => $base . '/js/bootstrap.bundle.min.js',
			'integrity' => 'sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu',
		),
	);
}

/** Version string for a theme file: its modification time, so browsers never keep a stale copy. */
function novidea_asset_version( string $relative_path ): string {
	$file = get_template_directory() . '/' . $relative_path;
	return file_exists( $file ) ? (string) filemtime( $file ) : wp_get_theme()->get( 'Version' );
}

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$bs  = novidea_bootstrap();
		$css = $bs['css'][ is_rtl() ? 'rtl' : 'ltr' ];
		$uri = get_template_directory_uri();

		// Bootstrap first, then the brand layer (handles depend on each other in this order).
		wp_enqueue_style( 'bootstrap', $css['href'], array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- version is in the URL; SRI needs the exact URL.
		wp_style_add_data( 'bootstrap', 'integrity', $css['integrity'] );

		foreach ( array( 'fonts', 'tokens', 'brand', 'layout' ) as $i => $name ) {
			$path = "assets/css/{$name}.css";
			$deps = 0 === $i ? array( 'bootstrap' ) : array( 'novidea-' . array( 'fonts', 'tokens', 'brand', 'layout' )[ $i - 1 ] );
			wp_enqueue_style( "novidea-{$name}", "{$uri}/{$path}", $deps, novidea_asset_version( $path ) );
		}

		wp_enqueue_script( 'bootstrap', $bs['js']['src'], array(), null, array( 'strategy' => 'defer', 'in_footer' => false ) ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		wp_script_add_data( 'bootstrap', 'integrity', $bs['js']['integrity'] );

		wp_enqueue_script(
			'novidea-animate',
			"{$uri}/assets/js/animate-on-scroll.js",
			array(),
			novidea_asset_version( 'assets/js/animate-on-scroll.js' ),
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}
);

/** Adds integrity + crossorigin to a CDN <link> or <script> tag that was given an 'integrity' value. */
function novidea_add_sri( string $tag, string $handle, string $type ): string {
	$registry  = 'script' === $type ? wp_scripts() : wp_styles();
	$integrity = $registry->get_data( $handle, 'integrity' );
	if ( ! $integrity ) {
		return $tag;
	}
	$attr = sprintf( ' integrity="%s" crossorigin="anonymous"', esc_attr( $integrity ) );
	return preg_replace( '/<(script|link)\b/', '<$1' . $attr, $tag, 1 );
}

add_filter( 'style_loader_tag', static fn ( string $tag, string $handle ) => novidea_add_sri( $tag, $handle, 'style' ), 10, 2 );
add_filter( 'script_loader_tag', static fn ( string $tag, string $handle ) => novidea_add_sri( $tag, $handle, 'script' ), 10, 2 );

// Connect to the CDN early.
add_filter(
	'wp_resource_hints',
	static function ( array $urls, string $relation ): array {
		if ( 'preconnect' === $relation ) {
			$urls[] = array(
				'href'        => novidea_bootstrap()['origin'],
				'crossorigin' => 'anonymous',
			);
		}
		return $urls;
	},
	10,
	2
);

// Brand color for the browser UI, and the favicons (unless a Site Icon is set in the Customizer).
add_action(
	'wp_head',
	static function (): void {
		echo '<meta name="theme-color" content="#101338">' . "\n";
		if ( has_site_icon() ) {
			return;
		}
		$img = get_template_directory_uri() . '/assets/images';
		printf( '<link rel="icon" href="%s" sizes="16x16 32x32 48x48">' . "\n", esc_url( "$img/favicon.ico" ) );
		printf( '<link rel="icon" href="%s" type="image/png" sizes="192x192">' . "\n", esc_url( "$img/icon-192.png" ) );
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( "$img/apple-touch-icon.png" ) );
	},
	5
);
