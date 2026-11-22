/**
 * Scroll-entrance animations (docs/design-system.md §6.1). No library: IntersectionObserver plus the
 * Web Animations API (the Astro site used Motion for the same effect).
 *
 * Markup API:
 *   data-animate="fade-up | fade-in | zoom-in | fade-start | fade-end"   animate one element
 *   data-animate-children="<type>"                                        animate each child in turn
 *   data-animate-delay="0.1"                                              extra delay in seconds
 *
 * "start" and "end" follow the reading direction: in Hebrew, fade-start enters from the right.
 *
 * Progressive enhancement: nothing is hidden by CSS. This script hides only elements that are still
 * below the fold when it runs, then reveals them as they scroll in. Without JavaScript, or with
 * "reduce motion" turned on, everything is simply visible.
 */
(function () {
	'use strict';

	if (!('IntersectionObserver' in window) || !Element.prototype.animate) return;
	if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

	var KINDS = ['fade-up', 'fade-in', 'zoom-in', 'fade-start', 'fade-end'];
	var DISTANCE = 24; // px
	var DURATION = 600; // ms
	var STAGGER = 80; // ms between children
	var EASE = 'cubic-bezier(0.2, 0.8, 0.2, 1)'; // = --ease-out token

	var rtl = document.documentElement.dir === 'rtl';
	// Below lg the layout is a single column: sideways entrances would push content past the screen edge.
	var narrow = window.matchMedia('(max-width: 991.98px)').matches;

	function kindOf(value) {
		var kind = KINDS.indexOf(value) >= 0 ? value : 'fade-up';
		return narrow && (kind === 'fade-start' || kind === 'fade-end') ? 'fade-up' : kind;
	}

	function fromTransform(kind) {
		var startX = rtl ? DISTANCE : -DISTANCE; // the inline-start side
		switch (kind) {
			case 'fade-in':
				return 'none';
			case 'zoom-in':
				return 'scale(0.96)';
			case 'fade-start':
				return 'translateX(' + startX + 'px)';
			case 'fade-end':
				return 'translateX(' + -startX + 'px)';
			default:
				return 'translateY(' + DISTANCE + 'px)';
		}
	}

	function belowFold(el) {
		return el.getBoundingClientRect().top > window.innerHeight;
	}

	function hide(el, from) {
		el.style.opacity = '0';
		el.style.transform = from;
	}

	function play(el, from, delay) {
		var animation = el.animate(
			[
				{ opacity: 0, transform: from },
				{ opacity: 1, transform: 'none' },
			],
			{ duration: DURATION, delay: delay, easing: EASE, fill: 'both' }
		);
		animation.finished.then(function () {
			el.style.opacity = '';
			el.style.transform = '';
			animation.cancel();
		});
	}

	function watch(target, amount, onEnter) {
		var observer = new IntersectionObserver(
			function (entries) {
				entries.forEach(function (entry) {
					if (!entry.isIntersecting) return;
					observer.disconnect();
					onEnter();
				});
			},
			{ threshold: amount }
		);
		observer.observe(target);
	}

	// Single elements
	document.querySelectorAll('[data-animate]').forEach(function (el) {
		if (!belowFold(el)) return;
		var from = fromTransform(kindOf(el.dataset.animate));
		var delay = Number(el.dataset.animateDelay || 0) * 1000;
		hide(el, from);
		watch(el, 0.15, function () {
			play(el, from, delay);
		});
	});

	// Groups: children enter one after another
	document.querySelectorAll('[data-animate-children]').forEach(function (group) {
		if (!belowFold(group)) return;
		var from = fromTransform(kindOf(group.dataset.animateChildren));
		var delay = Number(group.dataset.animateDelay || 0) * 1000;
		var children = Array.prototype.slice.call(group.children);
		children.forEach(function (child) {
			hide(child, from);
		});
		watch(group, 0.1, function () {
			children.forEach(function (child, i) {
				play(child, from, delay + i * STAGGER);
			});
		});
	});
})();
