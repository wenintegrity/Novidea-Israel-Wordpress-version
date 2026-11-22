<?php
/**
 * HubSpot lead form (design-system.md §10.22). Logic: assets/js/hubspot-form.js
 *
 * States (data-state on the slot):
 *   consent: marketing cookies not allowed yet → notice + button that allows them and loads the form
 *   loading: HubSpot script loading
 *   ready:   HubSpot renders the form into #hubspot-form-target
 *   error:   the script couldn't load (offline, blocked)
 * Without JavaScript, a <noscript> message explains the form needs it.
 * Without HubSpot settings, the block shows a placeholder listing the required fields.
 *
 * @var array $attributes
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

$anchor    = novidea_anchor( $attributes['anchor'] ?? '' );
$title     = trim( (string) ( $attributes['title'] ?? '' ) );
$intro     = trim( (string) ( $attributes['intro'] ?? '' ) );
$portal_id = preg_match( '/^\d+$/', (string) ( $attributes['portalId'] ?? '' ) ) ? $attributes['portalId'] : '';
$form_id   = preg_match( '/^[0-9a-f-]{36}$/i', (string) ( $attributes['formId'] ?? '' ) ) ? $attributes['formId'] : '';
$region    = preg_match( '/^[a-z]{2}\d$/', (string) ( $attributes['region'] ?? '' ) ) ? $attributes['region'] : 'na1';
$hubspot   = $portal_id && $form_id;

printf(
	'<section%s>',
	novidea_attrs( // phpcs:ignore WordPress.Security.EscapeOutput
		array(
			'id'              => $anchor ? $anchor : null,
			'tabindex'        => $anchor ? '-1' : null,
			'class'           => 'section nv-lead-form surface-subtle',
			'aria-labelledby' => 'lead-form-title',
		)
	)
);
?>
	<div class="container">
		<div class="row">
			<div class="col-12 col-lg-10 col-xl-8 mx-auto">
				<div class="lead-card" data-animate="fade-up">
					<h2 id="lead-form-title" class="title-accent"><?php echo esc_html( $title ? $title : __( 'Get in touch', 'novidea-blocks' ) ); ?></h2>
					<?php if ( $intro ) : ?>
						<p class="lead-card__intro"><?php echo esc_html( $intro ); ?></p>
					<?php endif; ?>
					<?php if ( $hubspot ) : ?>
						<div id="hubspot-form" class="lead-form" data-hubspot-form data-portal-id="<?php echo esc_attr( $portal_id ); ?>" data-form-id="<?php echo esc_attr( $form_id ); ?>" data-region="<?php echo esc_attr( $region ); ?>" data-submit-label="<?php esc_attr_e( 'Get in touch', 'novidea-blocks' ); ?>" data-state="consent">
							<div class="lead-form__notice" data-when="consent">
								<p><?php esc_html_e( 'The contact form is provided by HubSpot, which uses marketing and form cookies. To show the form, please allow these cookies.', 'novidea-blocks' ); ?></p>
								<div class="d-flex flex-wrap gap-2">
									<button type="button" class="btn btn-primary" data-hubspot-consent><?php esc_html_e( 'Allow marketing cookies and show the form', 'novidea-blocks' ); ?></button>
									<button type="button" class="btn btn-outline-primary" data-consent-open><?php esc_html_e( 'Cookie settings', 'novidea-blocks' ); ?></button>
								</div>
							</div>
							<p class="lead-form__notice" data-when="loading" role="status" hidden><?php esc_html_e( 'Loading the form…', 'novidea-blocks' ); ?></p>
							<p class="lead-form__notice lead-form__notice--error" data-when="error" role="alert" hidden><?php esc_html_e( "We couldn't load the form. An ad blocker may be stopping it. Try refreshing the page or allowing this site.", 'novidea-blocks' ); ?></p>
							<div id="hubspot-form-target" class="lead-form__target" data-hubspot-target></div>
							<noscript><p class="lead-form__notice"><?php esc_html_e( 'Please enable JavaScript to see the contact form.', 'novidea-blocks' ); ?></p></noscript>
						</div>
					<?php else : ?>
						<div id="hubspot-form" class="lead-form lead-form--placeholder">
							<p class="h3"><?php esc_html_e( 'The contact form will be embedded here', 'novidea-blocks' ); ?></p>
							<p class="text-secondary-nv"><?php esc_html_e( 'The form is coming soon. Form fields:', 'novidea-blocks' ); ?></p>
							<ul class="mb-0">
								<?php foreach ( novidea_strings( $attributes['fields'] ?? array() ) as $field ) : ?>
									<li><?php echo esc_html( $field ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
