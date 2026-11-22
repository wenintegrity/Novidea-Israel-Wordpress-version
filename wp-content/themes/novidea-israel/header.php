<?php
/**
 * Page shell, top: <html lang/dir> from the site language (he_IL → dir="rtl"), header, start of <main>.
 * Header (design-system.md §10.2): Bootstrap navbar with a responsive offcanvas (.offcanvas-lg).
 * Below lg the nav opens as a panel from the inline-end side; from lg up it's inline.
 *
 * @package novidea-israel
 */

defined( 'ABSPATH' ) || exit;

$novidea_cta       = novidea_page_link( 'novidea_header_cta_page' );
$novidea_cta_label = get_theme_mod( 'novidea_header_cta_label' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header sticky-top">
	<nav class="navbar navbar-expand-lg" aria-label="<?php esc_attr_e( 'Main', 'novidea-israel' ); ?>">
		<div class="container">
			<a class="navbar-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Novidea, home', 'novidea-israel' ); ?>">
				<?php novidea_logo( 'light', true ); ?>
			</a>

			<button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#site-nav" aria-controls="site-nav" aria-label="<?php esc_attr_e( 'Open menu', 'novidea-israel' ); ?>">
				<?php echo novidea_menu_icon(); // phpcs:ignore WordPress.Security.EscapeOutput -- fixed SVG. ?>
			</button>

			<div class="offcanvas offcanvas-end offcanvas-lg site-nav" tabindex="-1" id="site-nav" aria-labelledby="site-nav-title">
				<div class="offcanvas-header">
					<h2 class="offcanvas-title h4" id="site-nav-title"><?php esc_html_e( 'Menu', 'novidea-israel' ); ?></h2>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#site-nav" aria-label="<?php esc_attr_e( 'Close menu', 'novidea-israel' ); ?>"></button>
				</div>
				<div class="offcanvas-body">
					<ul class="navbar-nav">
						<?php foreach ( novidea_menu_items( 'primary' ) as $novidea_item ) : ?>
							<?php $novidea_current = novidea_menu_item_is_current( $novidea_item ); ?>
							<li class="nav-item">
								<a class="nav-link<?php echo $novidea_current ? ' active' : ''; ?>" href="<?php echo esc_url( $novidea_item->url ); ?>"<?php echo novidea_aria_current( $novidea_current ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $novidea_item->title ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php if ( $novidea_cta && $novidea_cta_label ) : ?>
						<div class="site-nav__actions">
							<a class="btn btn-accent btn-sm site-nav__cta" href="<?php echo esc_url( $novidea_cta[0] ); ?>"<?php echo novidea_aria_current( $novidea_cta[1] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $novidea_cta_label ); ?></a>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</nav>
	<?php // Reading progress (design-system.md §10.26): decorative, CSS only. ?>
	<div class="scroll-progress" aria-hidden="true"></div>
</header>
<main id="main" tabindex="-1">
