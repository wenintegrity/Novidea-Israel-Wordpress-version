<?php
/**
 * A page: its blocks (hero or page header first, then sections), then the shared lead banner.
 *
 * @package novidea-israel
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) {
	the_post();
	the_content();
}

novidea_lead_cta();

get_footer();
