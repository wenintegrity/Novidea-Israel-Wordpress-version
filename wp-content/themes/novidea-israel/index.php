<?php
/**
 * Fallback template. The site is built from pages (page.php); this only shows if something else is
 * requested, e.g. a post.
 *
 * @package novidea-israel
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="section surface-light">
	<div class="container">
		<div class="measure">
			<?php if ( have_posts() ) : ?>
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<article <?php post_class( 'mb-5' ); ?>>
						<?php if ( is_singular() ) : ?>
							<h1 class="title-accent"><?php the_title(); ?></h1>
						<?php else : ?>
							<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php endif; ?>
						<?php is_singular() ? the_content() : the_excerpt(); ?>
					</article>
				<?php endwhile; ?>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<p><?php esc_html_e( 'Nothing here yet.', 'novidea-israel' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php
get_footer();
