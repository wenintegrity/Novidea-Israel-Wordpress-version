<?php
/**
 * Text section: optional title, paragraphs, and an optional picture or video beside them (a picture
 * takes precedence if both are set). A video gets 7 of 12 columns (text 5) so the player is large
 * enough to watch; a picture gets 5.
 *
 * @var array $attributes
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

$anchor     = novidea_anchor( $attributes['anchor'] ?? '' );
$title      = trim( (string) ( $attributes['title'] ?? '' ) );
$heading_id = $anchor ? "{$anchor}-title" : null;
$picture    = $attributes['picture'] ?? array();
$video      = is_array( $attributes['video'] ?? null ) ? $attributes['video'] : array();
$has_pic    = novidea_has_picture( $picture );
$has_video  = ! $has_pic && ( ! empty( $video['id'] ) || ! empty( $video['url'] ) || ! empty( $video['title'] ) );
$text_cols  = $has_video ? 'col-lg-5' : ( $has_pic ? 'col-lg-6' : 'col-lg-9' );

printf(
	'<section%s>',
	novidea_attrs( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by novidea_attrs().
		array(
			'id'              => $anchor ? $anchor : null,
			'tabindex'        => $anchor ? '-1' : null,
			'class'           => 'section nv-text surface-' . novidea_surface( $attributes['surface'] ?? '' ),
			'aria-labelledby' => $title ? $heading_id : null,
		)
	)
);
?>
	<div class="container">
		<div class="row gy-5 align-items-center<?php echo $has_video ? ' gx-lg-5' : ''; ?>">
			<div class="col-12 <?php echo esc_attr( $text_cols ); ?> nv-text__body" data-animate="fade-up">
				<?php if ( $title ) : ?>
					<h2<?php echo $heading_id ? ' id="' . esc_attr( $heading_id ) . '"' : ''; ?> class="title-accent"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>
				<?php foreach ( novidea_strings( $attributes['paragraphs'] ?? array() ) as $i => $paragraph ) : ?>
					<p<?php echo 0 === $i ? ' class="lead-nv"' : ''; ?>><?php echo esc_html( $paragraph ); ?></p>
				<?php endforeach; ?>
			</div>
			<?php if ( $has_pic ) : ?>
				<div class="col-12 col-lg-5 offset-lg-1" data-animate="fade-end" data-animate-delay="0.1">
					<?php
					echo novidea_picture( // phpcs:ignore WordPress.Security.EscapeOutput
						$picture,
						array(
							'class' => 'text-block__picture',
							'sizes' => '(min-width: 992px) 40vw, 100vw',
						)
					);
					?>
				</div>
			<?php elseif ( $has_video ) : ?>
				<div class="col-12 col-lg-7" data-animate="fade-end" data-animate-delay="0.1">
					<div class="text-block__video"><?php echo novidea_video( $video ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
