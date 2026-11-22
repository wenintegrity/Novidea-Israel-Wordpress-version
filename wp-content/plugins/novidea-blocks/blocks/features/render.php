<?php
/**
 * Feature cards (design-system.md §10.8): brand icon, title, body, and "coming soon" labels.
 * 1 column on mobile, 2 on tablet, 2 or 3 on desktop. Rows of cards alternate between two background
 * colors, picked from the section surface. Item headings are h3 under a block title, h2 when the block
 * has none (keeps the heading order intact).
 *
 * @var array $attributes
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

$anchor     = novidea_anchor( $attributes['anchor'] ?? '' );
$title      = trim( (string) ( $attributes['title'] ?? '' ) );
$intro      = trim( (string) ( $attributes['intro'] ?? '' ) );
$picture    = $attributes['picture'] ?? array();
$has_pic    = novidea_has_picture( $picture );
$surface    = novidea_surface( $attributes['surface'] ?? '' );
$three      = 2 !== (int) ( $attributes['columns'] ?? 3 );
$heading_id = $anchor ? "{$anchor}-title" : null;
$item_tag   = $title ? 'h3' : 'h2';
$icon_var   = 'navy' === $surface ? 'dark' : 'light';
$items      = array_filter( is_array( $attributes['items'] ?? null ) ? $attributes['items'] : array(), static fn ( $i ) => is_array( $i ) && ( ! empty( $i['title'] ) || ! empty( $i['body'] ) ) );
$soon_badge = '<span class="badge-soon">' . novidea_ui_icon( 'rocket', 'badge-soon__icon' ) . esc_html__( 'Coming soon', 'novidea-blocks' ) . '</span>';

printf(
	'<section%s>',
	novidea_attrs( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'id'              => $anchor ? $anchor : null,
			'tabindex'        => $anchor ? '-1' : null,
			'class'           => "section nv-features surface-{$surface}",
			'aria-labelledby' => $title ? $heading_id : null,
		)
	)
);
?>
	<div class="container">
		<?php if ( $title || $intro || $has_pic ) : ?>
			<div class="row gy-4 align-items-center section-header">
				<header class="col-12<?php echo $has_pic ? ' col-lg-6' : ''; ?>">
					<div class="measure" data-animate="fade-up">
						<?php if ( $title ) : ?>
							<h2<?php echo $heading_id ? ' id="' . esc_attr( $heading_id ) . '"' : ''; ?> class="title-accent"><?php echo esc_html( $title ); ?></h2>
						<?php endif; ?>
						<?php if ( $intro ) : ?>
							<p class="lead-nv text-secondary-nv mb-0"><?php echo esc_html( $intro ); ?></p>
						<?php endif; ?>
					</div>
				</header>
				<?php if ( $has_pic ) : ?>
					<div class="col-12 col-lg-5 offset-lg-1" data-animate="zoom-in" data-animate-delay="0.1">
						<?php
						echo novidea_picture( // phpcs:ignore WordPress.Security.EscapeOutput
							$picture,
							array(
								'class' => 'section-picture',
								'sizes' => '(min-width: 992px) 40vw, 100vw',
							)
						);
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php // gx-4 matches the container padding; a larger horizontal gutter overflows on mobile. ?>
		<ul class="features row row-cols-1 row-cols-md-2 gx-4 gy-4 list-unstyled mb-0<?php echo $three ? ' row-cols-lg-3 features--3' : ''; ?>" data-animate-children="fade-up">
			<?php foreach ( $items as $item ) : ?>
				<li class="col">
					<div class="feature-card">
						<?php
						if ( ! empty( $item['icon'] ) ) {
							echo novidea_brand_icon( (string) $item['icon'], $icon_var, 'feature-icon' ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
						if ( ! empty( $item['soon'] ) ) {
							echo $soon_badge; // phpcs:ignore WordPress.Security.EscapeOutput
						}
						?>
						<?php if ( ! empty( $item['title'] ) ) : ?>
							<<?php echo $item_tag; // phpcs:ignore WordPress.Security.EscapeOutput -- h2 or h3. ?> class="h4 feature__title"><?php echo esc_html( $item['title'] ); ?></<?php echo $item_tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<?php endif; ?>
						<?php if ( ! empty( $item['body'] ) ) : ?>
							<p class="feature__body mb-0"><?php echo esc_html( $item['body'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $item['soonNote'] ) ) : ?>
							<p class="feature__soon mb-0"><?php echo $soon_badge; // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $item['soonNote'] ); ?></p>
						<?php endif; ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
