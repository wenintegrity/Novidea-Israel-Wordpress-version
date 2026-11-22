<?php
/**
 * Technical SEO (docs/seo.md §5), as on the Astro site:
 *   - <title> and meta description per page (Novidea panel in the editor)
 *   - noindex per page; the whole site stays noindex while Settings → Reading → "Discourage search
 *     engines" is on (the temporary address). Turn it off at launch on the final domain (docs/seo.md §6).
 *   - link previews (Open Graph, X card) and structured data (Organization, WebSite, BreadcrumbList)
 *   - sitemap.xml lists pages only, never noindex ones
 *   - robots.txt always allows crawling (crawlers must fetch a page to see its noindex)
 * Absolute URLs come from the site address (Settings → General), never a hard-coded domain.
 *
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

/** Page ID when viewing a single page, else 0. */
function novidea_seo_page_id(): int {
	return is_singular( 'page' ) ? (int) get_queried_object_id() : 0;
}

function novidea_seo_noindex( int $page_id ): bool {
	return $page_id && (bool) get_post_meta( $page_id, '_novidea_noindex', true );
}

/** Site name (Settings → General → Site Title), used in titles and link previews. */
function novidea_seo_site_name(): string {
	return get_bloginfo( 'name' );
}

// "Title | נובידאה", not "Title – נובידאה".
add_filter( 'document_title_separator', static fn () => '|' );

add_filter(
	'pre_get_document_title',
	static function ( string $title ): string {
		$page_id = novidea_seo_page_id();
		$custom  = $page_id ? trim( (string) get_post_meta( $page_id, '_novidea_seo_title', true ) ) : '';
		if ( '' !== $custom ) {
			return $custom;
		}
		if ( is_404() ) {
			return __( 'Page not found', 'novidea-blocks' ) . ' | ' . novidea_seo_site_name();
		}
		return $title;
	}
);

add_filter(
	'wp_robots',
	static function ( array $robots ): array {
		if ( novidea_seo_noindex( novidea_seo_page_id() ) ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);

// No canonical on pages kept out of search.
add_filter(
	'get_canonical_url',
	static function ( $url, $post ) {
		return ( $post instanceof WP_Post && 'page' === $post->post_type && get_post_meta( $post->ID, '_novidea_noindex', true ) ) ? false : $url;
	},
	10,
	2
);

/** Description for the current view. */
function novidea_seo_description(): string {
	$page_id = novidea_seo_page_id();
	if ( $page_id ) {
		$description = trim( (string) get_post_meta( $page_id, '_novidea_seo_description', true ) );
		if ( '' !== $description ) {
			return $description;
		}
	}
	if ( is_404() ) {
		return __( "The page you're looking for isn't on the Novidea site.", 'novidea-blocks' );
	}
	return (string) get_bloginfo( 'description' );
}

/** Image for link previews: the theme's share image (1200×630), else the site icon. */
function novidea_seo_share_image(): ?array {
	$path = get_theme_file_path( 'assets/images/og/novidea-share.jpg' );
	if ( file_exists( $path ) ) {
		return array(
			'url'    => get_theme_file_uri( 'assets/images/og/novidea-share.jpg' ),
			'width'  => 1200,
			'height' => 630,
		);
	}
	$icon = get_site_icon_url( 512 );
	return $icon ? array( 'url' => $icon, 'width' => 512, 'height' => 512 ) : null;
}

/** schema.org JSON-LD for the current view (only facts already on the site, docs/seo.md §9). */
function novidea_seo_json_ld(): array {
	$root = home_url( '/' );
	if ( is_front_page() ) {
		$logo = file_exists( get_theme_file_path( 'assets/images/logo-novidea.png' ) ) ? get_theme_file_uri( 'assets/images/logo-novidea.png' ) : get_site_icon_url( 512 );
		return array(
			array_filter(
				array(
					'@context'      => 'https://schema.org',
					'@type'         => 'Organization',
					'name'          => 'Novidea',
					'alternateName' => 'נובידאה',
					'url'           => $root,
					'logo'          => $logo ? $logo : null,
					'sameAs'        => array( 'https://novidea.com' ),
				)
			),
			array(
				'@context'   => 'https://schema.org',
				'@type'      => 'WebSite',
				'name'       => novidea_seo_site_name(),
				'url'        => $root,
				'inLanguage' => str_replace( '_', '-', get_locale() ),
			),
		);
	}

	$page_id = novidea_seo_page_id();
	if ( ! $page_id || novidea_seo_noindex( $page_id ) ) {
		return array();
	}
	$front_id = (int) get_option( 'page_on_front' );
	return array(
		array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => $front_id ? get_the_title( $front_id ) : novidea_seo_site_name(),
					'item'     => $root,
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => get_the_title( $page_id ),
					'item'     => get_permalink( $page_id ),
				),
			),
		),
	);
}

add_action(
	'wp_head',
	static function (): void {
		$description = novidea_seo_description();
		$title       = wp_get_document_title();
		$page_id     = novidea_seo_page_id();
		$url         = $page_id && ! novidea_seo_noindex( $page_id ) ? wp_get_canonical_url( $page_id ) : ( is_front_page() ? home_url( '/' ) : '' );
		$image       = novidea_seo_share_image();

		$tags = array(
			'<meta name="description" content="%s">'         => $description,
			'<meta property="og:type" content="%s">'         => 'website',
			'<meta property="og:site_name" content="%s">'    => novidea_seo_site_name(),
			'<meta property="og:locale" content="%s">'       => get_locale(),
			'<meta property="og:title" content="%s">'        => $title,
			'<meta property="og:description" content="%s">'  => $description,
			'<meta property="og:url" content="%s">'          => $url,
			'<meta property="og:image" content="%s">'        => $image['url'] ?? '',
			'<meta property="og:image:width" content="%s">'  => $image ? (string) $image['width'] : '',
			'<meta property="og:image:height" content="%s">' => $image ? (string) $image['height'] : '',
			'<meta property="og:image:alt" content="%s">'    => $image ? __( 'The Novidea logo next to a smiling woman in an office', 'novidea-blocks' ) : '',
			'<meta name="twitter:card" content="%s">'        => 'summary_large_image',
		);
		foreach ( $tags as $format => $value ) {
			if ( '' !== $value && false !== $value ) {
				printf( $format . "\n", esc_attr( $value ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- formats are fixed above.
			}
		}

		foreach ( novidea_seo_json_ld() as $data ) {
			// wp_json_encode escapes "/" and, with JSON_HEX_TAG, "<" and ">", so the text can never close the script element.
			printf( '<script type="application/ld+json">%s</script>' . "\n", wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	3
);

// ---------- sitemap.xml (/wp-sitemap.xml): pages only, never noindex ones ----------
add_filter(
	'wp_sitemaps_add_provider',
	static fn ( $provider, string $name ) => 'posts' === $name ? $provider : false,
	10,
	2
);
add_filter( 'wp_sitemaps_post_types', static fn ( array $types ) => array_intersect_key( $types, array( 'page' => true ) ) );
add_filter(
	'wp_sitemaps_posts_query_args',
	static function ( array $args ): array {
		$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery -- small site, cached by core.
			'relation' => 'OR',
			array(
				'key'     => '_novidea_noindex',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_novidea_noindex',
				'value'   => '1',
				'compare' => '!=',
			),
		);
		return $args;
	}
);

// ---------- robots.txt: crawling always allowed; the Sitemap line only when the site may be indexed ----------
add_filter(
	'robots_txt',
	static function ( string $output, $is_public ): string {
		$lines = array( 'User-agent: *', 'Allow: /' );
		if ( $is_public ) {
			$lines[] = '';
			$lines[] = 'Sitemap: ' . home_url( '/wp-sitemap.xml' );
		}
		return implode( "\n", $lines ) . "\n";
	},
	10,
	2
);
