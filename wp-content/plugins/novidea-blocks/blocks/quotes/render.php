<?php
/**
 * Customer quotes carousel (design-system.md §10.25). Bootstrap 5 carousel (data API, JS from the
 * theme's CDN bundle), cross-fade, no autoplay: visitors move between quotes with the arrow buttons,
 * the dots or the keyboard arrows. All slides share one grid cell, so the carousel is as tall as the
 * longest quote and doesn't jump. Quotation marks come from CSS `quotes`, not the content.
 *
 * @var array $attributes
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

$anchor      = novidea_anchor( $attributes['anchor'] ?? '' );
$anchor      = $anchor ? $anchor : 'quotes';
$carousel_id = "{$anchor}-carousel";
$heading_id  = "{$anchor}-title";
$items       = array_values( array_filter( is_array( $attributes['items'] ?? null ) ? $attributes['items'] : array(), static fn ( $i ) => is_array( $i ) && ! empty( $i['quote'] ) ) );
$total       = count( $items );
/* translators: 1: number of this quote, 2: number of quotes. */
$position = static fn ( int $n ) => sprintf( __( 'Quote %1$d of %2$d', 'novidea-blocks' ), $n, $total );
?>
<section id="<?php echo esc_attr( $anchor ); ?>" tabindex="-1" class="section nv-quotes surface-<?php echo esc_attr( novidea_surface( $attributes['surface'] ?? '', 'navy' ) ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="container">
		<header class="section-header" data-animate="fade-up">
			<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="title-accent"><?php echo esc_html( $attributes['title'] ?? '' ); ?></h2>
		</header>

		<div id="<?php echo esc_attr( $carousel_id ); ?>" class="carousel slide carousel-fade quotes" aria-roledescription="<?php esc_attr_e( 'carousel', 'novidea-blocks' ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>" data-animate="fade-up">
			<div class="carousel-inner" aria-live="polite">
				<?php foreach ( $items as $i => $item ) : ?>
					<div class="carousel-item<?php echo 0 === $i ? ' active' : ''; ?>" role="group" aria-roledescription="<?php esc_attr_e( 'quote', 'novidea-blocks' ); ?>" aria-label="<?php echo esc_attr( $position( $i + 1 ) ); ?>">
						<figure class="quote mb-0">
							<span class="quote__mark" aria-hidden="true"></span>
							<blockquote class="quote__text">
								<p><?php echo esc_html( $item['quote'] ); ?></p>
							</blockquote>
							<figcaption class="quote__by">
								<span class="quote__name"><?php echo esc_html( $item['name'] ?? '' ); ?></span>
								<span class="quote__role">
									<?php // A middle dot, not a comma: it reads right whichever language each part is in. ?>
									<bdi><?php echo esc_html( $item['role'] ?? '' ); ?></bdi> · <bdi><?php echo esc_html( $item['company'] ?? '' ); ?></bdi>
								</span>
							</figcaption>
						</figure>
					</div>
				<?php endforeach; ?>
			</div>

			<?php if ( $total > 1 ) : ?>
				<div class="quotes__controls">
					<button type="button" class="quotes__arrow" data-bs-target="#<?php echo esc_attr( $carousel_id ); ?>" data-bs-slide="prev">
						<span class="quotes__prev-icon"><?php echo novidea_ui_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
						<span class="visually-hidden"><?php esc_html_e( 'Previous quote', 'novidea-blocks' ); ?></span>
					</button>
					<div class="carousel-indicators quotes__dots">
						<?php foreach ( $items as $i => $item ) : ?>
							<button type="button" data-bs-target="#<?php echo esc_attr( $carousel_id ); ?>" data-bs-slide-to="<?php echo (int) $i; ?>"<?php echo 0 === $i ? ' class="active" aria-current="true"' : ''; ?> aria-label="<?php echo esc_attr( $position( $i + 1 ) . ': ' . ( $item['name'] ?? '' ) ); ?>"></button>
						<?php endforeach; ?>
					</div>
					<button type="button" class="quotes__arrow" data-bs-target="#<?php echo esc_attr( $carousel_id ); ?>" data-bs-slide="next">
						<?php echo novidea_ui_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span class="visually-hidden"><?php esc_html_e( 'Next quote', 'novidea-blocks' ); ?></span>
					</button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
