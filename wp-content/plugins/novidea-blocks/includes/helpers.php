<?php
/**
 * Shared rendering helpers for the blocks.
 *
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

/** Section surfaces (design-system.md §3.3) a block may use. */
const NOVIDEA_SURFACES = array( 'light', 'subtle', 'tint', 'navy' );

/** A surface from block attributes, falling back to $default when unknown. */
function novidea_surface( $value, string $default = 'light' ): string {
	return in_array( $value, NOVIDEA_SURFACES, true ) ? $value : $default;
}

/** A safe HTML id from an editor-typed anchor ('' when empty). */
function novidea_anchor( $value ): string {
	return sanitize_html_class( (string) $value );
}

/** Non-empty trimmed strings from a list attribute. */
function novidea_strings( $list ): array {
	if ( ! is_array( $list ) ) {
		return array();
	}
	return array_values( array_filter( array_map( static fn ( $s ) => trim( (string) $s ), $list ), 'strlen' ) );
}

/** HTML attributes from an array; null/false values are left out, true prints the bare name. */
function novidea_attrs( array $attrs ): string {
	$html = '';
	foreach ( $attrs as $name => $value ) {
		if ( null === $value || false === $value ) {
			continue;
		}
		$html .= true === $value ? ' ' . $name : sprintf( ' %s="%s"', $name, esc_attr( (string) $value ) );
	}
	return $html;
}

// ---------- Brand icon library (assets/icons/, catalogue in assets/icons/icons.json) ----------

/** The icon catalogue: id => [he, en, category, directional, light, dark]. */
function novidea_icons(): array {
	static $icons = null;
	if ( null === $icons ) {
		$json  = file_get_contents( NOVIDEA_BLOCKS_DIR . '/assets/icons/icons.json' );
		$icons = $json ? (array) json_decode( $json, true ) : array();
	}
	return $icons;
}

/**
 * Brand illustration icon (design-system.md §7.4). Icons sit next to text that says the same thing,
 * so they're decorative (alt="").
 *   light: navy lines, for white, light grey, yellow, sky and red surfaces
 *   dark:  white lines, for navy surfaces
 */
function novidea_brand_icon( string $id, string $variant = 'light', string $class = '' ): string {
	$icon = novidea_icons()[ $id ] ?? null;
	if ( ! $icon ) {
		return '';
	}
	$dark = 'dark' === $variant && ! empty( $icon['dark'] );
	if ( ! $dark && empty( $icon['light'] ) ) {
		return '';
	}
	$file    = $dark ? "{$id}.on-dark.png" : "{$id}.png";
	$classes = trim( 'brand-icon ' . ( ! empty( $icon['directional'] ) ? 'icon-directional ' : '' ) . $class );
	return sprintf(
		'<img src="%s" alt="" width="64" height="64" loading="lazy" decoding="async" class="%s">',
		esc_url( NOVIDEA_BLOCKS_URL . 'assets/icons/' . rawurlencode( $file ) ),
		esc_attr( $classes )
	);
}

// ---------- UI icons: 24×24 line icons (currentColor), plus filled 16×16 Bootstrap Icons ----------

/**
 * UI icon. Directional icons (arrow, rocket) mirror in RTL (design-system.md §8.1); play is a media
 * control and never mirrors. rocket = Bootstrap Icons v1.13.2 "rocket-takeoff-fill" (MIT).
 */
function novidea_ui_icon( string $name, string $class = '' ): string {
	$line   = array(
		'arrow' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
		'check' => '<path d="M20 6 9 17l-5-5"/>',
		'play'  => '<path d="M6 3.5v17L20 12z"/>',
		'menu'  => '<path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/>',
	);
	$filled = array(
		'rocket' => '<path d="M12.17 9.53c2.307-2.592 3.278-4.684 3.641-6.218.21-.887.214-1.58.16-2.065a3.6 3.6 0 0 0-.108-.563 2 2 0 0 0-.078-.23V.453c-.073-.164-.168-.234-.352-.295a2 2 0 0 0-.16-.045 4 4 0 0 0-.57-.093c-.49-.044-1.19-.03-2.08.188-1.536.374-3.618 1.343-6.161 3.604l-2.4.238h-.006a2.55 2.55 0 0 0-1.524.734L.15 7.17a.512.512 0 0 0 .433.868l1.896-.271c.28-.04.592.013.955.132.232.076.437.16.655.248l.203.083c.196.816.66 1.58 1.275 2.195.613.614 1.376 1.08 2.191 1.277l.082.202c.089.218.173.424.249.657.118.363.172.676.132.956l-.271 1.9a.512.512 0 0 0 .867.433l2.382-2.386c.41-.41.668-.949.732-1.526zm.11-3.699c-.797.8-1.93.961-2.528.362-.598-.6-.436-1.733.361-2.532.798-.799 1.93-.96 2.528-.361s.437 1.732-.36 2.531Z"/><path d="M5.205 10.787a7.6 7.6 0 0 0 1.804 1.352c-1.118 1.007-4.929 2.028-5.054 1.903-.126-.127.737-4.189 1.839-5.18.346.69.837 1.35 1.411 1.925"/>',
	);
	$directional = in_array( $name, array( 'arrow', 'rocket' ), true );
	$classes     = trim( 'icon ' . ( $directional ? 'icon-directional ' : '' ) . $class );

	if ( isset( $filled[ $name ] ) ) {
		return sprintf( '<svg class="%s" viewBox="0 0 16 16" fill="currentColor" focusable="false" aria-hidden="true">%s</svg>', esc_attr( $classes ), $filled[ $name ] );
	}
	if ( isset( $line[ $name ] ) ) {
		return sprintf( '<svg class="%s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">%s</svg>', esc_attr( $classes ), $line[ $name ] );
	}
	return '';
}

/**
 * Decorative stripe motif (design-system.md §7.2): rounded diagonal bars in brand yellow. An abstract
 * stand-in, not a copy of the logo mark. The angle never mirrors in RTL.
 */
function novidea_stripe_motif( string $class = '' ): string {
	$bars  = array( array( 70, 20, 200, 115 ), array( 45, 55, 200, 168 ), array( 55, 105, 195, 207 ), array( 75, 152, 170, 222 ), array( 100, 197, 140, 226 ) );
	$lines = '';
	foreach ( $bars as list( $x1, $y1, $x2, $y2 ) ) {
		$lines .= sprintf( '<line x1="%d" y1="%d" x2="%d" y2="%d"/>', $x1, $y1, $x2, $y2 );
	}
	return sprintf( '<svg class="%s" viewBox="0 0 240 240" aria-hidden="true" focusable="false">%s</svg>', esc_attr( trim( 'stripe-motif ' . $class ) ), $lines );
}

// ---------- Pictures from the media library ----------

/**
 * <img> for a picture attribute ({id, url, alt}) with srcset from the media library.
 * The alt text is the block's own (it describes the picture in this context); "" = decorative.
 *
 * @param array $args class, sizes, eager (bool: the likely LCP image).
 */
function novidea_picture( $picture, array $args = array() ): string {
	if ( ! is_array( $picture ) ) {
		return '';
	}
	$id    = (int) ( $picture['id'] ?? 0 );
	$alt   = (string) ( $picture['alt'] ?? '' );
	$eager = ! empty( $args['eager'] );
	$attr  = array(
		'alt'      => $alt,
		'class'    => $args['class'] ?? '',
		'loading'  => $eager ? 'eager' : 'lazy',
		'decoding' => 'async',
	);
	if ( $eager ) {
		$attr['fetchpriority'] = 'high';
	}
	if ( ! empty( $args['sizes'] ) ) {
		$attr['sizes'] = $args['sizes'];
	}

	if ( $id && wp_attachment_is_image( $id ) ) {
		return wp_get_attachment_image( $id, $args['size'] ?? 'full', false, $attr );
	}
	if ( ! empty( $picture['url'] ) ) {
		$attr['src'] = $picture['url'];
		unset( $attr['sizes'] );
		return '<img' . novidea_attrs( $attr ) . '>';
	}
	return '';
}

/** True when a picture attribute points at something. */
function novidea_has_picture( $picture ): bool {
	return is_array( $picture ) && ( ! empty( $picture['id'] ) || ! empty( $picture['url'] ) );
}

// ---------- Video (design-system.md §10.25) ----------

/**
 * Video from the media library with native controls. Without a file it shows a "coming soon"
 * placeholder of the same size, so the layout doesn't change when the video arrives. Media controls
 * are never mirrored in RTL.
 *
 * @param array $video {id, url, title, posterId, posterUrl, captionsId, captionsUrl}.
 */
function novidea_video( array $video ): string {
	$title    = (string) ( $video['title'] ?? '' );
	$src      = ! empty( $video['id'] ) ? wp_get_attachment_url( (int) $video['id'] ) : ( $video['url'] ?? '' );
	$poster   = ! empty( $video['posterId'] ) ? wp_get_attachment_image_url( (int) $video['posterId'], '1536x1536' ) : ( $video['posterUrl'] ?? '' );
	$captions = ! empty( $video['captionsId'] ) ? wp_get_attachment_url( (int) $video['captionsId'] ) : ( $video['captionsUrl'] ?? '' );

	if ( ! $src ) {
		return sprintf(
			'<div class="video video--placeholder surface-navy">%s<div class="video__content"><span class="video__play">%s</span><p class="video__soon mb-0"><span class="visually-hidden">%s: </span>%s</p></div></div>',
			novidea_stripe_motif(),
			novidea_ui_icon( 'play' ),
			esc_html( $title ),
			esc_html__( 'Video coming soon', 'novidea-blocks' )
		);
	}

	$type  = str_ends_with( strtolower( (string) wp_parse_url( $src, PHP_URL_PATH ) ), '.webm' ) ? 'video/webm' : 'video/mp4';
	$track = $captions ? sprintf(
		'<track kind="captions" src="%s" srclang="%s" label="%s" default>',
		esc_url( $captions ),
		esc_attr( substr( get_locale(), 0, 2 ) ),
		esc_attr__( 'Hebrew captions', 'novidea-blocks' )
	) : '';

	return sprintf(
		'<video class="video" controls preload="metadata" playsinline%s aria-label="%s"><source src="%s" type="%s">%s<p>%s <a href="%s">%s</a></p></video>',
		$poster ? ' poster="' . esc_url( $poster ) . '"' : '',
		esc_attr( $title ),
		esc_url( $src ),
		esc_attr( $type ),
		$track,
		esc_html__( "Your browser can't play this video.", 'novidea-blocks' ),
		esc_url( $src ),
		esc_html__( 'Download the video', 'novidea-blocks' )
	);
}

// ---------- Inline formatting for long-form text (legal pages) ----------

/**
 * Escapes the text, then turns [label](href) into links. Only http(s), mailto:, tel:, site-relative
 * and #anchor hrefs are allowed. [label](#cookie-settings) becomes a button that opens the cookie
 * settings. Placeholders still to fill in, e.g. "[לעדכון: שם הרכז]", are highlighted with <mark>.
 */
function novidea_inline( string $text ): string {
	$html = htmlspecialchars( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	$html = preg_replace_callback(
		'/\[([^\]]+)\]\(([^)\s]+)\)/u',
		static function ( array $m ): string {
			list( $match, $label, $href ) = $m;
			if ( '#cookie-settings' === $href ) {
				return '<button type="button" class="prose-link-button" data-consent-open>' . $label . '</button>';
			}
			if ( ! preg_match( '~^(https?://|mailto:|tel:|/|#)~', $href ) ) {
				return $match;
			}
			$external = (bool) preg_match( '~^https?://~', $href );
			$ltr      = (bool) preg_match( '~^(mailto:|tel:|https?:)~', $href );
			return '<a href="' . $href . '"' . ( $external ? ' rel="noopener"' : '' ) . ( $ltr ? ' dir="ltr"' : '' ) . '>' . $label . '</a>';
		},
		$html
	);

	return preg_replace( '/\[(לעדכון|TODO):([^\]]*)\](?!\()/u', '<mark>[$1:$2]</mark>', $html );
}
