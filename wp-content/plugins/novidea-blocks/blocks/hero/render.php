<?php
/**
 * Hero (design-system.md §10.6). Mobile/tablet: media then panel, stacked. Desktop (≥ lg): media on 7
 * columns at the inline-start, slanted yellow panel on 6 columns at the inline-end, overlapping by one
 * column. Without a picture the media is the stripe motif.
 *
 * @var array $attributes
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

$picture   = $attributes['picture'] ?? array();
$primary   = $attributes['primaryCta'] ?? array();
$secondary = $attributes['secondaryCta'] ?? array();
$panel     = 'hero__panel surface-yellow' . ( ! empty( $attributes['redLine'] ) ? ' hero__panel--red-line' : '' );
?>
<section class="hero" aria-labelledby="hero-title">
	<div class="container">
		<div class="hero__grid">
			<?php if ( novidea_has_picture( $picture ) ) : ?>
				<div class="hero__media hero__media--picture">
					<?php
					// Likely LCP element: load eagerly, high priority.
					echo novidea_picture( // phpcs:ignore WordPress.Security.EscapeOutput -- built from escaped parts.
						$picture,
						array(
							'class' => 'hero__picture',
							'sizes' => '(min-width: 992px) 58vw, 100vw',
							'eager' => true,
						)
					);
					?>
				</div>
			<?php else : ?>
				<div class="hero__media" aria-hidden="true"><?php echo novidea_stripe_motif( 'hero__stripes' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>

			<div class="<?php echo esc_attr( $panel ); ?>">
				<h1 id="hero-title" class="display-nv">
					<strong><?php echo esc_html( $attributes['titleStrong'] ?? '' ); ?></strong>
					<span class="headline-rest"><?php echo esc_html( $attributes['titleRest'] ?? '' ); ?></span>
				</h1>
				<?php if ( ! empty( $attributes['lead'] ) ) : ?>
					<p class="lead-nv"><?php echo esc_html( $attributes['lead'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $primary['label'] ) || ! empty( $secondary['label'] ) ) : ?>
					<div class="d-flex flex-wrap gap-3">
						<?php if ( ! empty( $primary['label'] ) && ! empty( $primary['href'] ) ) : ?>
							<a class="btn btn-primary" href="<?php echo esc_url( $primary['href'] ); ?>"><?php echo esc_html( $primary['label'] ); ?></a>
						<?php endif; ?>
						<?php if ( ! empty( $secondary['label'] ) && ! empty( $secondary['href'] ) ) : ?>
							<a class="btn btn-outline-primary" href="<?php echo esc_url( $secondary['href'] ); ?>"><?php echo esc_html( $secondary['label'] ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
