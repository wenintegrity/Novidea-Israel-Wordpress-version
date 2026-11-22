<?php
/**
 * Template helpers: logo, menus, lead banner.
 *
 * @package novidea-israel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Novidea logo (design-system.md §2), inlined from the official SVG so each variant only changes fills.
 * The paths are never edited, mirrored or redrawn (assets/images/logo-shapes.svg).
 *
 *   light (default): navy wordmark, yellow mark → white, navy-50, yellow-50 surfaces
 *   dark:            white wordmark, yellow mark → navy surfaces
 *   mono:            all black → yellow, sky and red surfaces
 *
 * @param bool $decorative Set when the surrounding link already carries the name.
 */
function novidea_logo( string $variant = 'light', bool $decorative = false ): void {
	static $shapes = null;
	$shapes ??= (string) file_get_contents( get_template_directory() . '/assets/images/logo-shapes.svg' );

	$a11y = $decorative ? 'aria-hidden="true"' : 'role="img" aria-label="Novidea"';
	printf(
		'<svg class="logo logo--%1$s" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1398.33333 462.8661" focusable="false" %2$s>%3$s</svg>',
		esc_attr( $variant ),
		$a11y, // phpcs:ignore WordPress.Security.EscapeOutput -- fixed strings above.
		$shapes // phpcs:ignore WordPress.Security.EscapeOutput -- trusted file shipped with the theme.
	);
}

/** Top-level items of the menu in a theme location, in menu order. */
function novidea_menu_items( string $location ): array {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] );
	if ( ! $items ) {
		return array();
	}
	return array_values( array_filter( $items, static fn ( $item ) => ! (int) $item->menu_item_parent ) );
}

/** True when a menu item points to the page being viewed. */
function novidea_menu_item_is_current( WP_Post $item ): bool {
	if ( 'post_type' === $item->type ) {
		return is_singular() && (int) $item->object_id === get_queried_object_id();
	}
	return untrailingslashit( $item->url ) === untrailingslashit( home_url( add_query_arg( array() ) ) );
}

/** A page picked in the Customizer: [url, is_current], or null when not set. */
function novidea_page_link( string $theme_mod ): ?array {
	$page_id = (int) get_theme_mod( $theme_mod );
	if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
		return null;
	}
	return array( get_permalink( $page_id ), is_page( $page_id ) );
}

/** aria-current="page" when $current. */
function novidea_aria_current( bool $current ): string {
	return $current ? ' aria-current="page"' : '';
}

/** UI icon (24×24 line icon, currentColor). Only the menu icon is needed by the theme. */
function novidea_menu_icon(): string {
	return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>';
}

/**
 * Lead banner (design-system.md §10.17): yellow surface, navy primary button. Shown at the bottom of
 * every page, except pages that already contain the lead form (/contact, landing pages) and pages
 * that turn it off (Novidea panel in the editor, e.g. /privacy).
 */
function novidea_lead_cta(): void {
	if ( is_singular() ) {
		$page = get_queried_object();
		if ( get_post_meta( $page->ID, '_novidea_hide_lead_cta', true ) || has_block( 'novidea/lead-form', $page ) ) {
			return;
		}
	}
	$title  = get_theme_mod( 'novidea_lead_title' );
	$body   = get_theme_mod( 'novidea_lead_body' );
	$label  = get_theme_mod( 'novidea_lead_button_label' );
	$button = novidea_page_link( 'novidea_lead_button_page' );
	if ( ! $title || ! $label || ! $button ) {
		return;
	}
	?>
	<section class="section surface-yellow lead-cta" aria-labelledby="lead-cta-title">
		<div class="container">
			<div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4" data-animate="fade-up">
				<div class="measure">
					<h2 id="lead-cta-title"><?php echo esc_html( $title ); ?></h2>
					<?php if ( $body ) : ?>
						<p class="lead-nv mb-0"><?php echo esc_html( $body ); ?></p>
					<?php endif; ?>
				</div>
				<a class="btn btn-primary cta-button" href="<?php echo esc_url( $button[0] ); ?>"><?php echo esc_html( $label ); ?></a>
			</div>
		</div>
	</section>
	<?php
}
