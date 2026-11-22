<?php
/**
 * Cookie consent banner + settings dialog (design-system.md §10.23; AGENTS.md §4.4). Logic:
 * assets/js/consent.js. The banner is hidden in the HTML and shown by the script only to visitors who
 * haven't decided yet. It comes right after the theme's skip link, so keyboard users reach it first.
 * Any button with data-consent-open (footer "Cookie settings", legal text) opens the dialog.
 *
 * TODO(verify): legal review of all consent wording (Privacy Protection Law, Amendment 13).
 *
 * @package novidea-blocks
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		wp_enqueue_script( 'novidea-consent' );
		wp_enqueue_style( 'novidea-consent', NOVIDEA_BLOCKS_URL . 'assets/css/consent.css', array(), novidea_blocks_version( 'assets/css/consent.css' ) );
	}
);

add_action(
	'wp_body_open',
	static function (): void {
		$privacy    = get_privacy_policy_url();
		$categories = array(
			'analytics' => array( __( 'Analytics cookies', 'novidea-blocks' ), __( 'Help us understand how the site is used, so we can improve it.', 'novidea-blocks' ) ),
			'marketing' => array( __( 'Marketing and form cookies', 'novidea-blocks' ), __( 'Used by HubSpot to show the contact form and track enquiries.', 'novidea-blocks' ) ),
		);
		?>
<div id="consent-banner" class="consent-banner surface-navy" role="region" aria-labelledby="consent-title" hidden>
	<div class="container">
		<div class="consent-banner__inner">
			<div class="consent-banner__text">
				<p id="consent-title" class="consent-banner__title"><?php esc_html_e( 'This site uses cookies', 'novidea-blocks' ); ?></p>
				<p class="text-sm-nv mb-0">
					<?php esc_html_e( 'We use necessary cookies to make the site work. With your consent, we also use cookies for analytics and marketing, including the HubSpot contact form. You can change your choice at any time with the "Cookie settings" link at the bottom of the page.', 'novidea-blocks' ); ?>
					<?php if ( $privacy ) : ?>
						<a href="<?php echo esc_url( $privacy ); ?>"><?php esc_html_e( 'Privacy policy', 'novidea-blocks' ); ?></a>
					<?php endif; ?>
				</p>
			</div>
			<div class="consent-banner__actions">
				<?php // Accept and reject are equally prominent (same style, same size). ?>
				<button type="button" class="btn btn-on-dark btn-sm" data-consent-action="accept"><?php esc_html_e( 'Accept all', 'novidea-blocks' ); ?></button>
				<button type="button" class="btn btn-on-dark btn-sm" data-consent-action="reject"><?php esc_html_e( 'Necessary cookies only', 'novidea-blocks' ); ?></button>
				<button type="button" class="btn btn-outline-on-dark btn-sm" data-consent-open><?php esc_html_e( 'Settings', 'novidea-blocks' ); ?></button>
			</div>
		</div>
	</div>
</div>

<dialog id="consent-dialog" class="consent-dialog" aria-labelledby="consent-dialog-title">
	<form id="consent-form" method="dialog">
		<div class="consent-dialog__head">
			<h2 id="consent-dialog-title" class="h3 mb-0"><?php esc_html_e( 'Cookie settings', 'novidea-blocks' ); ?></h2>
			<button type="button" class="btn-close" aria-label="<?php esc_attr_e( 'Close', 'novidea-blocks' ); ?>" data-consent-close></button>
		</div>

		<ul class="list-unstyled consent-options">
			<li class="consent-option">
				<div class="form-check form-switch">
					<input class="form-check-input" type="checkbox" role="switch" id="consent-necessary" checked disabled>
					<label class="form-check-label" for="consent-necessary">
						<?php esc_html_e( 'Necessary cookies', 'novidea-blocks' ); ?> <span class="consent-option__state">(<?php esc_html_e( 'always on', 'novidea-blocks' ); ?>)</span>
					</label>
				</div>
				<p class="text-sm-nv text-secondary-nv mb-0"><?php esc_html_e( 'Needed for the site to work and to remember your choice.', 'novidea-blocks' ); ?></p>
			</li>
			<?php foreach ( $categories as $id => list( $title, $desc ) ) : ?>
				<li class="consent-option">
					<div class="form-check form-switch">
						<input class="form-check-input" type="checkbox" role="switch" id="consent-<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $id ); ?>" aria-describedby="consent-<?php echo esc_attr( $id ); ?>-desc">
						<label class="form-check-label" for="consent-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ); ?></label>
					</div>
					<p id="consent-<?php echo esc_attr( $id ); ?>-desc" class="text-sm-nv text-secondary-nv mb-0"><?php echo esc_html( $desc ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="consent-dialog__actions">
			<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Save preferences', 'novidea-blocks' ); ?></button>
			<button type="button" class="btn btn-outline-primary" data-consent-action="accept"><?php esc_html_e( 'Accept all', 'novidea-blocks' ); ?></button>
		</div>
	</form>
</dialog>
		<?php
	},
	10
);
