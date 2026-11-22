<?php
/**
 * Internal page header (design-system.md §10.19): a smaller version of the home hero. Desktop (≥ lg):
 * media on 7 columns at the inline-start, slanted yellow panel (h1 + intro) on 6 columns at the
 * inline-end, overlapping by one column. Below lg: media above the panel. Without a picture the media
 * is a navy block with the stripe motif, hidden below lg (decorative).
 *
 * @var array $attributes
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

$picture     = $attributes['picture'] ?? array();
$has_picture = novidea_has_picture( $picture );
?>
<section class="page-header<?php echo $has_picture ? ' page-header--picture' : ''; ?>" aria-labelledby="page-title">
	<div class="container">
		<div class="page-header__grid">
			<?php if ( $has_picture ) : ?>
				<div class="page-header__media">
					<?php
					// Likely LCP element: load eagerly, high priority.
					echo novidea_picture( // phpcs:ignore WordPress.Security.EscapeOutput
						$picture,
						array(
							'class' => 'page-header__picture',
							'sizes' => '(min-width: 992px) 58vw, 100vw',
							'eager' => true,
						)
					);
					?>
				</div>
			<?php else : ?>
				<div class="page-header__media page-header__media--motif surface-navy" aria-hidden="true"><?php echo novidea_stripe_motif(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php endif; ?>

			<div class="page-header__panel surface-yellow">
				<h1 id="page-title"><?php echo esc_html( $attributes['title'] ?? '' ); ?></h1>
				<?php foreach ( novidea_strings( $attributes['intro'] ?? array() ) as $paragraph ) : ?>
					<p class="lead-nv"><?php echo esc_html( $paragraph ); ?></p>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
