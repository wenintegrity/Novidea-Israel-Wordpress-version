<?php
/**
 * Office location: building, address, directions, and buttons that open Waze or Google Maps. Plain
 * links, no embedded map, so no third-party cookies or scripts. `mapQuery` is the search text for the
 * maps (defaults to place + address).
 *
 * @var array $attributes
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

$anchor     = novidea_anchor( $attributes['anchor'] ?? '' );
$anchor     = $anchor ? $anchor : 'location';
$heading_id = "{$anchor}-title";
$surface    = novidea_surface( $attributes['surface'] ?? '' );
$place      = (string) ( $attributes['place'] ?? '' );
$address    = (string) ( $attributes['address'] ?? '' );
$directions = trim( (string) ( $attributes['directions'] ?? '' ) );
$query      = trim( (string) ( $attributes['mapQuery'] ?? '' ) );
$query      = rawurlencode( '' !== $query ? $query : trim( "$place, $address", ', ' ) );
$waze       = "https://waze.com/ul?q={$query}&navigate=yes";
$maps       = "https://www.google.com/maps/search/?api=1&query={$query}";
$new_window = '<span class="visually-hidden">' . esc_html__( '(opens in a new window)', 'novidea-blocks' ) . '</span>';
?>
<section id="<?php echo esc_attr( $anchor ); ?>" tabindex="-1" class="section nv-location surface-<?php echo esc_attr( $surface ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="container">
		<header class="section-header" data-animate="fade-up">
			<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="title-accent"><?php echo esc_html( $attributes['title'] ?? '' ); ?></h2>
		</header>
		<div class="location-card" data-animate="fade-up">
			<?php echo novidea_brand_icon( 'office-building', 'navy' === $surface ? 'dark' : 'light', 'location-card__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<div class="location-card__body">
				<address class="mb-0">
					<strong class="location-card__place"><?php echo esc_html( $place ); ?></strong>
					<span class="location-card__address"><?php echo esc_html( $address ); ?></span>
				</address>
				<?php if ( $directions ) : ?>
					<p class="location-card__directions"><?php echo esc_html( $directions ); ?></p>
				<?php endif; ?>
				<div class="location-card__actions">
					<a class="btn btn-primary" href="<?php echo esc_url( $waze ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Navigate with Waze', 'novidea-blocks' ); ?> <?php echo $new_window; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<a class="btn btn-outline-primary" href="<?php echo esc_url( $maps ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Open in Google Maps', 'novidea-blocks' ); ?> <?php echo $new_window; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				</div>
			</div>
		</div>
	</div>
</section>
