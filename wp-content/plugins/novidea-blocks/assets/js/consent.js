/**
 * Cookie consent (docs/design-system.md §10.23; AGENTS.md §4.4).
 *
 * Categories: "necessary" (always on: remembers this choice), "analytics", "marketing" (HubSpot forms
 * and tracking). Nothing third-party may run before its category is granted.
 *
 * Gating third-party scripts — add them as inert scripts and they run only after consent:
 *   <script type="text/plain" data-consent="marketing" data-src="https://…/embed.js"></script>
 * Or from code:
 *   window.nvConsent.whenGranted('marketing', function () { … });
 *
 * The choice is kept in localStorage ("nv-consent"). Bump VERSION when the categories or the policy
 * change, so every visitor is asked again.
 */
(function () {
	'use strict';

	var KEY = 'nv-consent';
	var VERSION = 1;
	var CATEGORIES = ['analytics', 'marketing'];

	/** Cookies each category may set; deleted when that consent is withdrawn. */
	var COOKIES = {
		analytics: [/^_ga/, /^_gid$/, /^_gcl/],
		marketing: [/^__hs/, /^hubspotutk$/, /^__hstc$/, /^__hssc$/, /^__hssrc$/, /^messagesUtk$/],
	};

	function read() {
		try {
			var parsed = JSON.parse(localStorage.getItem(KEY) || 'null');
			return parsed && parsed.v === VERSION ? parsed : null;
		} catch (e) {
			return null;
		}
	}

	function write(value) {
		try {
			localStorage.setItem(KEY, JSON.stringify(value));
		} catch (e) {
			// Storage blocked (private mode): the choice holds for this page view only.
		}
	}

	var state = read();
	var waiting = { analytics: [], marketing: [] };

	function granted(category) {
		return !!state && state[category] === true;
	}

	function hasDecided() {
		return state !== null;
	}

	function whenGranted(category, fn) {
		if (granted(category)) fn();
		else waiting[category].push(fn);
	}

	/** Turn inert <script type="text/plain" data-consent="…"> elements into running scripts. */
	function activate(category) {
		document
			.querySelectorAll('script[type="text/plain"][data-consent="' + category + '"]')
			.forEach(function (inert) {
				var script = document.createElement('script');
				Array.prototype.forEach.call(inert.attributes, function (attr) {
					if (['type', 'data-consent', 'data-src'].indexOf(attr.name) < 0) script.setAttribute(attr.name, attr.value);
				});
				if (inert.dataset.src) script.src = inert.dataset.src;
				else script.textContent = inert.textContent;
				inert.replaceWith(script);
			});
		waiting[category].splice(0).forEach(function (fn) {
			fn();
		});
	}

	function deleteCookies(category) {
		var names = document.cookie
			.split(';')
			.map(function (c) {
				return (c.split('=')[0] || '').trim();
			})
			.filter(Boolean);
		var host = location.hostname;
		var domains = ['', host, '.' + host, '.' + host.split('.').slice(-2).join('.')];
		names.forEach(function (name) {
			var match = COOKIES[category].some(function (re) {
				return re.test(name);
			});
			if (!match) return;
			domains.forEach(function (domain) {
				document.cookie = name + '=; Max-Age=0; path=/' + (domain ? '; domain=' + domain : '');
			});
		});
	}

	function save(choice) {
		var before = state;
		state = { v: VERSION, analytics: !!choice.analytics, marketing: !!choice.marketing, at: new Date().toISOString() };
		write(state);

		// Withdrawn consent: delete that category's cookies and reload, so already-running scripts stop.
		var withdrawn = CATEGORIES.filter(function (c) {
			return before && before[c] && !choice[c];
		});
		if (withdrawn.length) {
			withdrawn.forEach(deleteCookies);
			location.reload();
			return;
		}

		CATEGORIES.filter(function (c) {
			return choice[c];
		}).forEach(activate);
		document.dispatchEvent(new CustomEvent('nv:consent', { detail: state }));
	}

	/** Grant one category, keeping the visitor's other choices (e.g. "show the form" on /contact). */
	function grant(category) {
		var choice = { analytics: granted('analytics'), marketing: granted('marketing') };
		choice[category] = true;
		save(choice);
		var banner = document.getElementById('consent-banner');
		if (banner) banner.hidden = true;
	}

	// ---------- UI: banner, settings dialog, footer link ----------

	function initUi() {
		var banner = document.getElementById('consent-banner');
		var dialog = document.getElementById('consent-dialog');
		var form = document.getElementById('consent-form');
		if (!banner || !dialog || !form) return;

		function hideBanner() {
			var hadFocus = banner.contains(document.activeElement);
			banner.hidden = true;
			var main = document.getElementById('main');
			if (hadFocus && main) main.focus({ preventScroll: true });
		}

		function syncForm() {
			CATEGORIES.forEach(function (c) {
				var box = form.elements.namedItem(c);
				if (box) box.checked = granted(c);
			});
		}

		function openDialog() {
			syncForm();
			dialog.showModal();
		}

		function decide(choice) {
			save(choice);
			hideBanner();
			if (dialog.open) dialog.close();
		}

		document.querySelectorAll('[data-consent-action="accept"]').forEach(function (b) {
			b.addEventListener('click', function () {
				decide({ analytics: true, marketing: true });
			});
		});
		document.querySelectorAll('[data-consent-action="reject"]').forEach(function (b) {
			b.addEventListener('click', function () {
				decide({ analytics: false, marketing: false });
			});
		});
		document.querySelectorAll('[data-consent-open]').forEach(function (b) {
			b.addEventListener('click', openDialog);
		});
		var close = dialog.querySelector('[data-consent-close]');
		if (close) close.addEventListener('click', function () {
			dialog.close();
		});

		form.addEventListener('submit', function (event) {
			event.preventDefault();
			function value(name) {
				var box = form.elements.namedItem(name);
				return !!box && box.checked === true;
			}
			decide({ analytics: value('analytics'), marketing: value('marketing') });
		});

		if (!hasDecided()) banner.hidden = false;
	}

	window.nvConsent = {
		whenGranted: whenGranted,
		granted: granted,
		grant: grant,
		open: function () {
			var opener = document.querySelector('[data-consent-open]');
			if (opener) opener.click();
		},
	};

	// Start (deferred script: the DOM is parsed): run what's already allowed, then wire the UI.
	CATEGORIES.filter(granted).forEach(activate);
	initUi();
})();
