<?php
/**
 * Page link cards (design-system.md §10.20). Each card is one link (stretched ::after): icon, title,
 * short summary, arrow. 1 column on mobile, 2 on tablet, 4 on desktop.
 *
 * @var array $attributes
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

$anchor     = novidea_anchor( $attributes['anchor'] ?? '' );
$title      = trim( (string) ( $attributes['title'] ?? '' ) );
$heading_id = ( $anchor ? $anchor : 'links' ) . '-title';
$card_tag   = $title ? 'h3' : 'h2';
$items      = array_filter( is_array( $attributes['items'] ?? null ) ? $attributes['items'] : array(), static fn ( $i ) => is_array( $i ) && ! empty( $i['title'] ) && ! empty( $i['href'] ) );

printf(
	'<nav%s>',
	novidea_attrs( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'id'              => $anchor ? $anchor : null,
			'class'           => 'section nv-links surface-' . novidea_surface( $attributes['surface'] ?? '' ),
			'aria-labelledby' => $title ? $heading_id : null,
			'aria-label'      => $title ? null : __( 'More about Novidea', 'novidea-blocks' ),
		)
	)
);
?>
	<div class="container">
		<?php if ( $title ) : ?>
			<header class="section-header" data-animate="fade-up">
				<h2 id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $title ); ?></h2>
			</header>
		<?php endif; ?>
		<ul class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 list-unstyled mb-0" data-animate-children="fade-up">
			<?php foreach ( $items as $item ) : ?>
				<li class="col">
					<div class="link-card">
						<?php
						if ( ! empty( $item['icon'] ) ) {
							echo novidea_brand_icon( (string) $item['icon'], 'light' ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
						?>
						<<?php echo $card_tag; // phpcs:ignore WordPress.Security.EscapeOutput -- h2 or h3. ?> class="h4 link-card__title">
							<a class="stretched-link" href="<?php echo esc_url( $item['href'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
						</<?php echo $card_tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<?php if ( ! empty( $item['body'] ) ) : ?>
							<p class="text-secondary-nv mb-0"><?php echo esc_html( $item['body'] ); ?></p>
						<?php endif; ?>
						<?php echo novidea_ui_icon( 'arrow', 'link-card__arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</nav>
