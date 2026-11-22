<?php
/**
 * Customer logo strip (design-system.md §10.10). Each customer shows its logo when there is one,
 * otherwise its name as text. Logos are images of text, so the alt text is the customer's name.
 *
 * @var array $attributes
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

$anchor     = novidea_anchor( $attributes['anchor'] ?? '' );
$heading_id = ( $anchor ? $anchor : 'names' ) . '-title';
$more       = trim( (string) ( $attributes['more'] ?? '' ) );
$names      = array_filter( is_array( $attributes['names'] ?? null ) ? $attributes['names'] : array(), static fn ( $n ) => is_array( $n ) && ! empty( $n['name'] ) );

printf(
	'<section%s>',
	novidea_attrs( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'id'              => $anchor ? $anchor : null,
			'tabindex'        => $anchor ? '-1' : null,
			'class'           => 'section nv-names surface-' . novidea_surface( $attributes['surface'] ?? '', 'subtle' ),
			'aria-labelledby' => $heading_id,
		)
	)
);
?>
	<div class="container">
		<header class="section-header" data-animate="fade-up">
			<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="title-accent"><?php echo esc_html( $attributes['title'] ?? '' ); ?></h2>
		</header>
		<ul class="names list-unstyled mb-0" data-animate-children="fade-in">
			<?php foreach ( $names as $customer ) : ?>
				<?php
				$logo = is_array( $customer['logo'] ?? null ) ? $customer['logo'] : array();
				if ( novidea_has_picture( $logo ) ) :
					$logo['alt'] = $customer['name'];
					?>
					<li class="name name--logo">
						<?php
						echo novidea_picture( // phpcs:ignore WordPress.Security.EscapeOutput
							$logo,
							array(
								'class' => 'name__logo',
								'size'  => 'medium_large',
								'sizes' => '(min-width: 768px) 16rem, 45vw',
							)
						);
						?>
					</li>
				<?php else : ?>
					<li class="name"><?php echo esc_html( $customer['name'] ); ?></li>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php if ( $more ) : ?>
				<li class="name name--more"><?php echo esc_html( $more ); ?></li>
			<?php endif; ?>
		</ul>
	</div>
</section>
