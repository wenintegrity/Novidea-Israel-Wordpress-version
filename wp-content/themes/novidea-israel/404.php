<?php
/**
 * 404 page (docs/seo.md §5). Always noindex (WordPress core adds it on 404s). Links back to the home
 * page and the pages in the header menu.
 *
 * @package novidea-israel
 */

defined( 'ABSPATH' ) || exit;

$novidea_links = novidea_menu_items( 'primary' );
$novidea_cta   = novidea_page_link( 'novidea_header_cta_page' );

get_header();
?>
<section class="section surface-subtle not-found" aria-labelledby="page-title">
	<div class="container">
		<div class="measure">
			<h1 id="page-title" class="title-accent"><?php esc_html_e( 'Page not found', 'novidea-israel' ); ?></h1>
			<p><?php esc_html_e( 'The link may be wrong, or the page may have moved. Go back to the home page or pick a page:', 'novidea-israel' ); ?></p>
			<ul class="not-found__links list-unstyled">
				<?php foreach ( $novidea_links as $novidea_item ) : ?>
					<li><a href="<?php echo esc_url( $novidea_item->url ); ?>"><?php echo esc_html( $novidea_item->title ); ?></a></li>
				<?php endforeach; ?>
				<?php if ( $novidea_cta && get_theme_mod( 'novidea_header_cta_label' ) ) : ?>
					<li><a href="<?php echo esc_url( $novidea_cta[0] ); ?>"><?php echo esc_html( get_theme_mod( 'novidea_header_cta_label' ) ); ?></a></li>
				<?php endif; ?>
			</ul>
			<a class="btn btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home page', 'novidea-israel' ); ?></a>
		</div>
	</div>
</section>
<?php
get_footer();
