/**
 * Loads the HubSpot lead form into [data-hubspot-form] — only after marketing-cookie consent
 * (window.nvConsent, assets/js/consent.js). Config comes from data attributes set by the lead-form
 * block: data-portal-id, data-form-id, data-region, data-submit-label.
 * Same embed as HubSpot's snippet (forms/embed/v2.js + hbspt.forms.create), without inline script.
 *
 * HubSpot draws the form inside an iframe, so the site's CSS can't reach it. The iframe is
 * same-origin (about:blank), so once the form is in it we style it from here (styleForm): the page's
 * language and direction, the brand font, colors and focus ring, and LTR email and phone values.
 * Field labels and the field list themselves are set in HubSpot.
 */
(function () {
	'use strict';

	var EMBED_SRC = 'https://js.hsforms.net/forms/embed/v2.js';
	var STYLE_ID = 'nv-form-style';

	// Brand colors (theme tokens.css). The iframe can't read the page's CSS variables, so the values
	// are repeated here. Logical properties only: direction comes from the iframe's dir.
	var FORM_CSS = [
		'html, body { background: transparent; }',
		"body, .hs-form, .hs-form *, .submitted-message, .submitted-message * { font-family: 'Rubik Variable', 'Rubik', system-ui, sans-serif !important; color: #101338; }",
		'.hs-form fieldset { max-inline-size: none !important; }',
		'.hs-form .hs-form-field { margin-block-end: 1.25rem; }',
		'.hs-form label:not(.hs-form-booleancheckbox-display):not(.hs-form-radio-display) { display: block; margin-block-end: 0.375rem; font-size: 1rem !important; font-weight: 500 !important; color: #101338 !important; text-align: start !important; }',
		'.hs-form .hs-form-required { color: #c22e2d; margin-inline-start: 0.125rem; }',
		".hs-form .hs-input:not([type='checkbox']):not([type='radio']) { box-sizing: border-box; inline-size: 100% !important; min-block-size: 3rem; padding: 0.75rem 1rem !important; border: 1px solid #5e6066 !important; border-radius: 2px !important; background: #fff !important; color: #101338 !important; font-size: 1rem !important; text-align: start; }",
		".hs-form .hs-input[dir='ltr'] { text-align: end; }",
		'.hs-form .hs-input:focus { outline: 2px solid #101338 !important; outline-offset: 2px; border-color: #101338 !important; box-shadow: none !important; }',
		'.hs-form .inputs-list { padding-inline-start: 0 !important; margin: 0; list-style: none; }',
		'.hs-form .hs-error-msgs label, .hs-form .hs-error-msg, .hs-form .hs-main-font-element { color: #c22e2d !important; font-weight: 500 !important; }',
		'.hs-form .hs_submit, .hs-form .actions { text-align: start !important; padding: 0 !important; }',
		".hs-form .hs-button, .hs-form input[type='submit'] { min-block-size: 3rem; padding: 0.75rem 2rem !important; border: 1px solid #101338 !important; border-radius: 2px !important; background: #101338 !important; color: #fff !important; font-size: 1rem !important; font-weight: 500 !important; cursor: pointer; }",
		".hs-form .hs-button:hover, .hs-form input[type='submit']:hover { background: #1b1f4a !important; border-color: #1b1f4a !important; }",
		".hs-form .hs-button:focus-visible, .hs-form input[type='submit']:focus-visible { outline: 2px solid #101338 !important; outline-offset: 2px; }",
	].join('\n');

	function loadScript(src) {
		return new Promise(function (resolve, reject) {
			var existing = document.querySelector('script[src="' + src + '"]');
			if (existing && window.hbspt) return resolve();
			var script = existing || document.createElement('script');
			script.addEventListener('load', function () { resolve(); }, { once: true });
			script.addEventListener('error', function () { reject(new Error('Failed to load ' + src)); }, { once: true });
			if (!existing) {
				script.src = src;
				script.charset = 'utf-8';
				document.head.append(script);
			}
		});
	}

	function show(slot, state) {
		slot.dataset.state = state;
		slot.querySelectorAll('[data-when]').forEach(function (el) {
			el.hidden = el.dataset.when !== state;
		});
	}

	/** The site's self-hosted @font-face rules, so the iframe can use the brand font. */
	function fontFaces() {
		var rules = [];
		Array.prototype.forEach.call(document.styleSheets, function (sheet) {
			var list;
			try {
				list = sheet.cssRules; // throws for cross-origin sheets (the Bootstrap CDN), which have no fonts
			} catch (e) {
				return;
			}
			Array.prototype.forEach.call(list || [], function (rule) {
				if (window.CSSFontFaceRule && rule instanceof CSSFontFaceRule) rules.push(rule.cssText);
			});
		});
		return rules.join('\n');
	}

	/** Styles the form inside HubSpot's iframe. Returns false until the form is there. */
	function styleForm(target, submitLabel) {
		var frame = target.querySelector('iframe');
		var doc;
		try {
			doc = frame && frame.contentDocument;
		} catch (e) {
			return true; // not same-origin (HubSpot changed its embed): leave HubSpot's own styling
		}
		var form = doc && doc.querySelector('form');
		if (!frame || !doc || !form) return false;

		if (!doc.getElementById(STYLE_ID)) {
			doc.documentElement.lang = document.documentElement.lang;
			doc.documentElement.dir = document.documentElement.dir;
			var style = doc.createElement('style');
			style.id = STYLE_ID;
			style.textContent = fontFaces() + '\n' + FORM_CSS;
			doc.head.append(style);

			// Keep the iframe as tall as its content (fonts and spacing changed it; errors appear later)
			var fit = function () {
				frame.style.height = doc.documentElement.scrollHeight + 'px';
			};
			if (window.ResizeObserver) new ResizeObserver(fit).observe(doc.body);
			fit();
		}

		// Email addresses and phone numbers are typed left to right (design-system.md §8.2)
		form.querySelectorAll('input[type="email"], input[type="tel"]').forEach(function (input) {
			input.dir = 'ltr';
		});
		if (submitLabel) {
			form.querySelectorAll('input[type="submit"]').forEach(function (button) {
				button.value = submitLabel;
			});
		}
		return true;
	}

	/** onFormReady can fire before the iframe has the form, so retry briefly. */
	function styleWhenReady(target, submitLabel, tries) {
		if (tries === undefined) tries = 40;
		if (styleForm(target, submitLabel) || tries <= 0) return;
		setTimeout(function () {
			styleWhenReady(target, submitLabel, tries - 1);
		}, 100);
	}

	var consent = window.nvConsent;

	document.querySelectorAll('[data-hubspot-form]').forEach(function (slot) {
		var data = slot.dataset;
		var target = slot.querySelector('[data-hubspot-target]');
		if (!data.portalId || !data.formId || !data.region || !target || !consent) return;

		show(slot, 'consent');
		var allow = slot.querySelector('[data-hubspot-consent]');
		if (allow) allow.addEventListener('click', function () {
			consent.grant('marketing');
		});

		consent.whenGranted('marketing', function () {
			show(slot, 'loading');
			loadScript(EMBED_SRC)
				.then(function () {
					if (!window.hbspt) throw new Error('HubSpot embed did not initialise');
					window.hbspt.forms.create({
						portalId: data.portalId,
						formId: data.formId,
						region: data.region,
						target: '#' + target.id,
						onFormReady: function () {
							styleWhenReady(target, data.submitLabel);
						},
					});
					show(slot, 'ready');
				})
				.catch(function (error) {
					console.error(error);
					show(slot, 'error');
				});
		});
	});
})();
