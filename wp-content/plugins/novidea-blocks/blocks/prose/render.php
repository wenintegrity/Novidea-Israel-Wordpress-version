<?php
/**
 * Long-form text for legal pages (design-system.md §10.24): numbered h2 sections, paragraphs, a bullet
 * list and paragraphs after it. Readable measure, light surface. Inline links: [label](href);
 * [label](#cookie-settings) opens the cookie settings; "[לעדכון: …]" placeholders are highlighted.
 *
 * @var array $attributes
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

$anchor   = novidea_anchor( $attributes['anchor'] ?? '' );
$sections = array_values( array_filter( is_array( $attributes['sections'] ?? null ) ? $attributes['sections'] : array(), static fn ( $s ) => is_array( $s ) && ! empty( $s['title'] ) ) );
$prefix   = $anchor ? $anchor : 'prose';
?>
<section<?php echo $anchor ? ' id="' . esc_attr( $anchor ) . '"' : ''; ?> class="section surface-light prose-block">
	<div class="container">
		<div class="prose">
			<?php foreach ( $sections as $i => $section ) : ?>
				<section class="prose__section" aria-labelledby="<?php echo esc_attr( "{$prefix}-{$i}" ); ?>">
					<h2 id="<?php echo esc_attr( "{$prefix}-{$i}" ); ?>" class="h3"><span class="prose__number" dir="ltr"><?php echo (int) $i + 1; ?>.</span> <?php echo esc_html( $section['title'] ); ?></h2>
					<?php foreach ( novidea_strings( $section['paragraphs'] ?? array() ) as $paragraph ) : ?>
						<p><?php echo novidea_inline( $paragraph ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by novidea_inline(). ?></p>
					<?php endforeach; ?>
					<?php $bullets = novidea_strings( $section['bullets'] ?? array() ); ?>
					<?php if ( $bullets ) : ?>
						<ul>
							<?php foreach ( $bullets as $bullet ) : ?>
								<li><?php echo novidea_inline( $bullet ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php foreach ( novidea_strings( $section['after'] ?? array() ) as $paragraph ) : ?>
						<p><?php echo novidea_inline( $paragraph ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<?php endforeach; ?>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
</section>
