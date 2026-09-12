/**
 * CBW Magazine — front-end behaviour.
 *
 * Every block is independent and bails out quietly when its markup is absent,
 * so one template can omit a component without breaking the rest.
 */
var cbwReduceMotion = (function () {
	try {
		return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	} catch (e) { return false; }
}());

function cbwEach(list, fn) {
	Array.prototype.forEach.call(list, fn);
}

/**
 * Navigation: desktop dropdowns, the off-canvas panel on small screens, and
 * the stuck / scrolled state of the bar.
 */
(function () {
	'use strict';

	var nav = document.getElementById('cbw-nav');
	var burger = document.querySelector('.cbw-burger');
	var panel = document.getElementById('cbw-nav-panel');
	var closeBtn = document.querySelector('.cbw-nav__close');
	var backdrop = document.querySelector('.cbw-nav__backdrop');
	var header = document.querySelector('.cbw-header');
	var mobile = window.matchMedia('(max-width: 900px)');
	var hideTimer = null;

	function isOpen() {
		return panel && panel.classList.contains('is-open');
	}

	function openPanel() {
		if (!panel) { return; }
		clearTimeout(hideTimer);
		panel.classList.add('is-open');
		burger.setAttribute('aria-expanded', 'true');
		document.body.classList.add('cbw-menu-open');
		if (backdrop) {
			backdrop.hidden = false;
			requestAnimationFrame(function () { backdrop.classList.add('is-visible'); });
		}
		// Wait for visibility to flip before moving focus into the panel.
		setTimeout(function () { if (closeBtn) { closeBtn.focus(); } }, 60);
	}

	function closePanel(returnFocus) {
		if (!isOpen()) { return; }
		panel.classList.remove('is-open');
		burger.setAttribute('aria-expanded', 'false');
		document.body.classList.remove('cbw-menu-open');
		if (backdrop) {
			backdrop.classList.remove('is-visible');
			hideTimer = setTimeout(function () { backdrop.hidden = true; }, 320);
		}
		if (returnFocus) { burger.focus(); }
	}

	if (burger && panel) {
		burger.addEventListener('click', function () {
			if (isOpen()) { closePanel(false); } else { openPanel(); }
		});
		if (closeBtn) { closeBtn.addEventListener('click', function () { closePanel(true); }); }
		if (backdrop) { backdrop.addEventListener('click', function () { closePanel(true); }); }

		// Keep Tab inside the open panel.
		panel.addEventListener('keydown', function (e) {
			if (e.key !== 'Tab' || !isOpen() || !mobile.matches) { return; }
			var focusable = Array.prototype.filter.call(
				panel.querySelectorAll('a[href], button:not([disabled]), input:not([type="hidden"])'),
				function (el) { return el.offsetParent !== null && !el.closest('.cbw-hp'); }
			);
			if (!focusable.length) { return; }
			var first = focusable[0];
			var last = focusable[focusable.length - 1];
			if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
			else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
		});

		var onBreakpoint = function () { if (!mobile.matches) { closePanel(false); } };
		if (mobile.addEventListener) { mobile.addEventListener('change', onBreakpoint); }
		else if (mobile.addListener) { mobile.addListener(onBreakpoint); }
	}

	// Sub-menu toggles (used on small screens; harmless on desktop hover).
	cbwEach(document.querySelectorAll('.cbw-nav__toggle'), function (btn) {
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			var item = btn.closest('li');
			if (!item) { return; }
			var open = item.classList.toggle('is-open');
			btn.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
	});

	// Escape closes any open dropdown, then the panel.
	document.addEventListener('keydown', function (e) {
		if (e.key !== 'Escape') { return; }
		cbwEach(document.querySelectorAll('.cbw-nav__item.is-open'), function (item) {
			item.classList.remove('is-open');
			var btn = item.querySelector('.cbw-nav__toggle');
			if (btn) { btn.setAttribute('aria-expanded', 'false'); }
		});
		closePanel(true);
	});

	// Click outside closes desktop dropdowns opened by keyboard.
	document.addEventListener('click', function (e) {
		if (e.target.closest('.cbw-nav')) { return; }
		cbwEach(document.querySelectorAll('.cbw-nav__item.is-open'), function (item) {
			item.classList.remove('is-open');
		});
	});

	/* Scroll state: stuck bar, reading progress, back-to-top ring. */
	var totop = document.querySelector('.cbw-totop');
	var ticking = false;

	function onScroll() {
		ticking = false;
		var y = window.pageYOffset || document.documentElement.scrollTop;
		var max = document.documentElement.scrollHeight - window.innerHeight;
		var pct = max > 0 ? Math.min(1, y / max) : 0;

		if (nav) {
			nav.classList.toggle('is-stuck', header ? header.getBoundingClientRect().bottom <= 0 : y > 120);
			nav.style.setProperty('--cbw-progress', pct.toFixed(4));
		}
		if (totop) {
			totop.classList.toggle('is-visible', y > 700);
			totop.style.setProperty('--cbw-progress', (pct * 100).toFixed(2));
		}
	}

	window.addEventListener('scroll', function () {
		if (!ticking) { ticking = true; requestAnimationFrame(onScroll); }
	}, { passive: true });
	window.addEventListener('resize', onScroll);
	onScroll();

	if (totop) {
		totop.addEventListener('click', function (e) {
			e.preventDefault();
			window.scrollTo({ top: 0, behavior: cbwReduceMotion ? 'auto' : 'smooth' });
			var skip = document.querySelector('.skip-link');
			if (skip) { skip.focus({ preventScroll: true }); }
		});
	}
}());

/**
 * Language dropdown. A disclosure of plain links: arrow keys move between
 * them, Escape and clicking away close it.
 */
(function () {
	'use strict';

	var root = document.querySelector('.cbw-lang');
	if (!root) { return; }
	var btn = root.querySelector('.cbw-lang__btn');
	var menu = root.querySelector('.cbw-lang__menu');
	var opts = root.querySelectorAll('.cbw-lang__opt');

	function setOpen(open) {
		if (open) {
			// Where the top bar wraps, the button can sit near the left edge;
			// open the list rightward there rather than off-screen.
			root.classList.remove('cbw-lang--start');
			if (menu.getBoundingClientRect().left < 8) { root.classList.add('cbw-lang--start'); }
		}
		root.classList.toggle('is-open', open);
		btn.setAttribute('aria-expanded', open ? 'true' : 'false');
	}

	function focusOpt(i) {
		var n = opts.length;
		opts[(i + n) % n].focus();
	}

	btn.addEventListener('click', function () {
		setOpen(!root.classList.contains('is-open'));
	});

	root.addEventListener('keydown', function (e) {
		var idx = Array.prototype.indexOf.call(opts, document.activeElement);
		if (e.key === 'Escape' && root.classList.contains('is-open')) {
			e.stopPropagation();
			setOpen(false);
			btn.focus();
		} else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
			e.preventDefault();
			if (!root.classList.contains('is-open')) { setOpen(true); }
			var step = e.key === 'ArrowDown' ? 1 : -1;
			focusOpt(idx === -1 ? (step > 0 ? 0 : opts.length - 1) : idx + step);
		}
	});

	root.addEventListener('focusout', function (e) {
		if (!e.relatedTarget || !root.contains(e.relatedTarget)) { setOpen(false); }
	});

	document.addEventListener('click', function (e) {
		if (!root.contains(e.target)) { setOpen(false); }
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

		cbwEach(document.querySelectorAll('.cbw-mode__btn'), function (b) {
			b.setAttribute('aria-pressed', b.getAttribute('data-mode') === mode ? 'true' : 'false');
		});
	}

	function store(mode) {
		// 'auto' clears the cookie so the server stops stamping a theme.
		var v = mode === 'auto' ? '; Max-Age=0' : '=' + mode + '; Max-Age=' + YEAR;
		document.cookie = 'cbw_mode' + (mode === 'auto' ? '=' : '') + v + '; Path=/; SameSite=Lax';
	}

	cbwEach(document.querySelectorAll('.cbw-mode__btn'), function (btn) {
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

/**
 * Hero slider.
 *
 * The autoplay clock is the CSS fill animation on the active tab: when it
 * ends, the next slide shows. Pausing is then just animation-play-state, so
 * hover, focus and the pause button all stop the clock exactly where it is.
 */
(function () {
	'use strict';

	cbwEach(document.querySelectorAll('.cbw-slider'), function (root) {
		var slides = root.querySelectorAll('.cbw-slide');
		var tabs = root.querySelectorAll('.cbw-slider__tab');
		var playBtn = root.querySelector('.cbw-slider__play');
		var stage = root.querySelector('.cbw-slider__slides');
		var n = slides.length;
		var cur = 0;
		if (n < 2) { return; }

		root.classList.add('is-ready');

		function setPaused(paused) {
			root.classList.toggle('is-paused', paused);
			if (playBtn) { playBtn.setAttribute('aria-pressed', paused ? 'true' : 'false'); }
			// Announce slide changes only while the reader is driving them.
			stage.setAttribute('aria-live', paused ? 'polite' : 'off');
		}

		function go(i) {
			i = (i + n) % n;
			if (i === cur) { return; }
			var from = slides[cur];
			var to = slides[i];

			from.classList.remove('is-active');
			from.classList.add('is-leaving');
			from.setAttribute('aria-hidden', 'true');
			from.setAttribute('inert', '');
			setTimeout(function () { from.classList.remove('is-leaving'); }, 900);

			to.classList.add('is-active');
			to.removeAttribute('aria-hidden');
			to.removeAttribute('inert');

			cbwEach(tabs, function (t, k) {
				if (k === i) { t.setAttribute('aria-current', 'true'); } else { t.removeAttribute('aria-current'); }
			});
			cur = i;
		}

		cbwEach(tabs, function (tab, k) {
			tab.addEventListener('click', function () { go(k); });
			var bar = tab.querySelector('.cbw-slider__tabprogress');
			if (bar) {
				bar.addEventListener('animationend', function (e) {
					if (e.animationName === 'cbw-fill' && k === cur) { go(cur + 1); }
				});
			}
		});

		cbwEach(root.querySelectorAll('[data-go]'), function (b) {
			b.addEventListener('click', function () {
				go(cur + (b.getAttribute('data-go') === 'next' ? 1 : -1));
			});
		});

		if (playBtn) {
			playBtn.addEventListener('click', function () {
				setPaused(!root.classList.contains('is-paused'));
			});
		}

		// Hold the clock while a pointer or keyboard focus is inside.
		root.addEventListener('mouseenter', function () { root.classList.add('is-held'); });
		root.addEventListener('mouseleave', function () { root.classList.remove('is-held'); });
		root.addEventListener('focusin', function () { root.classList.add('is-held'); });
		root.addEventListener('focusout', function (e) {
			if (!e.relatedTarget || !root.contains(e.relatedTarget)) { root.classList.remove('is-held'); }
		});

		root.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowRight') { go(cur + 1); }
			else if (e.key === 'ArrowLeft') { go(cur - 1); }
		});

		// Swipe. A drag that moved suppresses the click it would otherwise end in.
		var sx = 0, sy = 0, down = false, dragged = false;
		stage.addEventListener('pointerdown', function (e) {
			down = true; dragged = false; sx = e.clientX; sy = e.clientY;
		});
		stage.addEventListener('pointerup', function (e) {
			if (!down) { return; }
			down = false;
			var dx = e.clientX - sx;
			var dy = e.clientY - sy;
			if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy) * 1.3) {
				dragged = true;
				go(cur + (dx < 0 ? 1 : -1));
			}
		});
		stage.addEventListener('pointercancel', function () { down = false; });
		stage.addEventListener('click', function (e) {
			if (dragged) { e.preventDefault(); e.stopPropagation(); dragged = false; }
		}, true);

		// Autoplay never starts under reduced motion; the controls still work.
		setPaused(cbwReduceMotion);
	});
}());

/**
 * Scroll-snap carousels: arrow buttons scroll by a page, and a mouse can drag
 * the track. Touch uses native scrolling.
 */
(function () {
	'use strict';

	cbwEach(document.querySelectorAll('.cbw-carousel__track'), function (track) {
		var btns = document.querySelectorAll('.cbw-carousel__btn[aria-controls="' + track.id + '"]');

		function update() {
			var max = track.scrollWidth - track.clientWidth - 2;
			cbwEach(btns, function (b) {
				var dir = +b.getAttribute('data-dir');
				b.disabled = dir < 0 ? track.scrollLeft <= 2 : track.scrollLeft >= max;
			});
		}

		cbwEach(btns, function (b) {
			b.addEventListener('click', function () {
				track.scrollBy({
					left: +b.getAttribute('data-dir') * track.clientWidth * 0.9,
					behavior: cbwReduceMotion ? 'auto' : 'smooth'
				});
			});
		});

		track.addEventListener('scroll', function () { requestAnimationFrame(update); }, { passive: true });
		window.addEventListener('resize', update);
		update();

		var startX = 0, startLeft = 0, dragging = false, moved = false;
		track.addEventListener('pointerdown', function (e) {
			if (e.pointerType !== 'mouse' || e.button !== 0) { return; }
			dragging = true; moved = false;
			startX = e.clientX; startLeft = track.scrollLeft;
		});
		window.addEventListener('pointermove', function (e) {
			if (!dragging) { return; }
			var dx = e.clientX - startX;
			if (!moved && Math.abs(dx) > 6) {
				moved = true;
				track.classList.add('is-dragging');
			}
			if (moved) { track.scrollLeft = startLeft - dx; }
		});
		window.addEventListener('pointerup', function () {
			if (!dragging) { return; }
			dragging = false;
			track.classList.remove('is-dragging');
		});
		track.addEventListener('click', function (e) {
			if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
		}, true);
		track.addEventListener('dragstart', function (e) { e.preventDefault(); });
	});
}());

/**
 * Trending ticker pause button.
 */
(function () {
	'use strict';

	var ticker = document.querySelector('.cbw-ticker');
	var btn = ticker && ticker.querySelector('.cbw-ticker__toggle');
	if (!btn) { return; }
	btn.addEventListener('click', function () {
		var paused = ticker.classList.toggle('is-paused');
		btn.setAttribute('aria-pressed', paused ? 'true' : 'false');
	});
}());

/**
 * Entrance reveals and count-up figures.
 */
(function () {
	'use strict';

	var items = document.querySelectorAll('.cbw-reveal');
	var counters = document.querySelectorAll('[data-count]');

	function countUp(el) {
		var to = parseInt(el.getAttribute('data-count'), 10) || 0;
		if (cbwReduceMotion || to < 2) { return; }
		var start = null;
		var dur = 1400;
		function step(t) {
			if (start === null) { start = t; }
			var p = Math.min(1, (t - start) / dur);
			var eased = 1 - Math.pow(1 - p, 3);
			el.textContent = String(Math.round(to * eased));
			if (p < 1) { requestAnimationFrame(step); }
		}
		requestAnimationFrame(step);
	}

	if (!('IntersectionObserver' in window)) {
		cbwEach(items, function (el) { el.classList.add('is-visible'); });
		return;
	}

	var io = new IntersectionObserver(function (entries) {
		var k = 0;
		entries.forEach(function (en) {
			if (!en.isIntersecting) { return; }
			var el = en.target;
			io.unobserve(el);
			// Items that arrive together enter in a short cascade.
			el.style.transitionDelay = Math.min(k++ * 80, 400) + 'ms';
			el.classList.add('is-visible');
			el.addEventListener('transitionend', function clear() {
				el.style.transitionDelay = '';
				el.removeEventListener('transitionend', clear);
			});
		});
	}, { rootMargin: '0px 0px -6% 0px', threshold: 0.06 });

	cbwEach(items, function (el) { io.observe(el); });

	var co = new IntersectionObserver(function (entries) {
		entries.forEach(function (en) {
			if (en.isIntersecting) { co.unobserve(en.target); countUp(en.target); }
		});
	}, { threshold: 0.6 });
	cbwEach(counters, function (el) { co.observe(el); });
}());
