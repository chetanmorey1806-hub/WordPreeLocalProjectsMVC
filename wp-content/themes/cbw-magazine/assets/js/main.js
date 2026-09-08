/**
 * CBW Magazine — navigation behaviour.
 */
(function () {
	'use strict';

	var burger = document.querySelector('.cbw-burger');
	var menu = document.getElementById('cbw-primary-menu');

	if (burger && menu) {
		burger.addEventListener('click', function () {
			var open = menu.classList.toggle('is-open');
			burger.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	}

	// Sub-menu toggles (used on small screens; harmless on desktop hover).
	Array.prototype.forEach.call(document.querySelectorAll('.cbw-nav__toggle'), function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			var item = btn.closest('li');
			if (!item) { return; }
			var open = item.classList.toggle('is-open');
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	});

	// Close any open dropdown on Escape, and collapse the mobile drawer.
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') { return; }
		Array.prototype.forEach.call(document.querySelectorAll('.cbw-nav__item.is-open'), function (item) {
			item.classList.remove('is-open');
			var btn = item.querySelector('.cbw-nav__toggle');
			if (btn) { btn.setAttribute('aria-expanded', 'false'); }
		});
		if (menu && menu.classList.contains('is-open')) {
			menu.classList.remove('is-open');
			burger.setAttribute('aria-expanded', 'false');
			burger.focus();
		}
	});

	// Click outside closes desktop dropdowns opened by keyboard.
	document.addEventListener('click', function (e) {
		if (e.target.closest('.cbw-nav')) { return; }
		Array.prototype.forEach.call(document.querySelectorAll('.cbw-nav__item.is-open'), function (item) {
			item.classList.remove('is-open');
		});
	});
}());

/**
 * Colour mode. The server stamps data-theme from the cookie, so this only
 * has to handle the click and keep the cookie in step.
 */
(function () {
	'use strict';

	var YEAR = 60 * 60 * 24 * 365;

	function systemDark() {
		try {
			return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
		} catch (e) { return false; }
	}

	function apply(mode) {
		var root = document.documentElement;
		if (mode === 'auto') {
			if (systemDark()) { root.setAttribute('data-theme', 'dark'); }
			else { root.removeAttribute('data-theme'); }
		} else {
			root.setAttribute('data-theme', mode);
		}

		Array.prototype.forEach.call(document.querySelectorAll('.cbw-mode__btn'), function (b) {
			b.setAttribute('aria-pressed', b.getAttribute('data-mode') === mode ? 'true' : 'false');
		});
	}

	function store(mode) {
		// 'auto' clears the cookie so the server stops stamping a theme.
		var v = mode === 'auto' ? '; Max-Age=0' : '=' + mode + '; Max-Age=' + YEAR;
		document.cookie = 'cbw_mode' + (mode === 'auto' ? '=' : '') + v + '; Path=/; SameSite=Lax';
	}

	Array.prototype.forEach.call(document.querySelectorAll('.cbw-mode__btn'), function (btn) {
		btn.addEventListener('click', function () {
			var mode = btn.getAttribute('data-mode');
			store(mode);
			apply(mode);
		});
	});

	// While on 'auto', follow the system if it changes mid-session.
	try {
		var mq = window.matchMedia('(prefers-color-scheme: dark)');
		var onChange = function () {
			if (!/(^|;\s*)cbw_mode=/.test(document.cookie)) { apply('auto'); }
		};
		if (mq.addEventListener) { mq.addEventListener('change', onChange); }
		else if (mq.addListener) { mq.addListener(onChange); }
	} catch (e) {}
}());
