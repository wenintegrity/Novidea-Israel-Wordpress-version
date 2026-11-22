<?php
/**
 * Page shell, bottom: end of <main>, footer (design-system.md §10.13), navy surface.
 *
 * @package novidea-israel
 */

defined( 'ABSPATH' ) || exit;

$novidea_about   = get_theme_mod( 'novidea_footer_about' );
$novidea_links   = novidea_menu_items( 'footer' );
$novidea_privacy = get_privacy_policy_url();
?>
</main>
<footer class="site-footer surface-navy">
	<div class="container">
		<div class="row gy-5">
			<div class="col-12 col-lg-5">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Novidea, home', 'novidea-israel' ); ?>" class="footer-brand">
					<?php novidea_logo( 'dark', true ); ?>
				</a>
				<?php if ( $novidea_about ) : ?>
					<p class="footer-about mt-4 mb-0"><?php echo esc_html( $novidea_about ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $novidea_links ) : ?>
				<nav class="col-12 col-md-8 col-lg-6 offset-lg-1" aria-labelledby="footer-nav-title">
					<h2 class="h4" id="footer-nav-title"><?php esc_html_e( 'Navigation', 'novidea-israel' ); ?></h2>
					<ul class="footer-nav__list list-unstyled mb-0">
						<?php foreach ( $novidea_links as $novidea_item ) : ?>
							<li><a href="<?php echo esc_url( $novidea_item->url ); ?>"><?php echo esc_html( $novidea_item->title ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</nav>
			<?php endif; ?>
		</div>

		<div class="footer-legal text-sm-nv text-secondary-nv">
			<p class="mb-0">© <span dir="ltr"><?php echo esc_html( wp_date( 'Y' ) ); ?></span> Novidea. <?php esc_html_e( 'All rights reserved.', 'novidea-israel' ); ?></p>
			<nav aria-label="<?php esc_attr_e( 'Legal', 'novidea-israel' ); ?>">
				<ul class="footer-legal__links list-unstyled mb-0">
					<?php if ( $novidea_privacy ) : ?>
						<li><a href="<?php echo esc_url( $novidea_privacy ); ?>"><?php esc_html_e( 'Privacy policy', 'novidea-israel' ); ?></a></li>
					<?php endif; ?>
					<?php // Opens the cookie settings dialog (Novidea Blocks plugin). ?>
					<li><button type="button" class="footer-link-button" data-consent-open><?php esc_html_e( 'Cookie settings', 'novidea-israel' ); ?></button></li>
				</ul>
			</nav>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
