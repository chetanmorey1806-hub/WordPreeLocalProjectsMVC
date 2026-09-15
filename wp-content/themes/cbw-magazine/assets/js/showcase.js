/**
 * Magazine shelf and flipbook.
 *
 * The shelf lays the issues out on a 3D stage: the active cover square to the
 * reader, the rest turned in toward it. Opening a cover lifts it off the shelf
 * into a reader where it opens like a printed magazine, a leaf at a time.
 *
 * Each book's pages are server-rendered into a <template>, so the content is
 * the site's own and a closed book costs nothing until it is opened.
 */
(function () {
	'use strict';

	var shelf = document.querySelector('.cbw-shelf');
	var dlg = document.getElementById('cbw-book');
	if (!shelf || !dlg || typeof dlg.showModal !== 'function') { return; }

	var motion = window.matchMedia('(prefers-reduced-motion: reduce)');
	function still() { return motion.matches; }

	function onFrame(fn) {
		var queued = false;
		return function () {
			if (queued) { return; }
			queued = true;
			window.requestAnimationFrame(function () { queued = false; fn(); });
		};
	}

	/* ================================================================
	   Shelf
	   ================================================================ */
	var stage = shelf.querySelector('.cbw-shelf__stage');
	var items = Array.prototype.slice.call(shelf.querySelectorAll('.cbw-shelf__item'));
	var nameEl = shelf.querySelector('.cbw-shelf__name');
	var dateEl = shelf.querySelector('.cbw-shelf__date');
	var count = items.length;
	var active = 0;
	var swiped = false;

	if (!count) { return; }

	items.forEach(function (el) { el.setAttribute('aria-haspopup', 'dialog'); });

	function layout() {
		var small = shelf.clientWidth < 700;
		var cw = items[0].offsetWidth;
		var near = cw * (small ? 0.56 : 0.66);  // the first neighbour tucks under the centre cover
		var step = cw * (small ? 0.3 : 0.42);   // each further one steps out a little less
		var depth = small ? 120 : 170;
		var angle = small ? 40 : 32;
		var reach = small ? 1 : 2;              // covers left visible either side

		items.forEach(function (el, i) {
			// The shelf is a loop, so the first issue has neighbours on both sides.
			var off = (i - active + count) % count;
			if (off > count / 2) { off -= count; }
			var a = Math.abs(off);
			var side = off < 0 ? -1 : 1;
			var x = a ? side * (near + (a - 1) * step) : 0;
			var turn = a ? -side * angle : 0;    // side covers face in toward the centre
			var hidden = a > reach;

			el.style.transform = 'translate3d(' + x.toFixed(1) + 'px,0,' + (-a * depth) + 'px) rotateY(' + turn + 'deg)';
			el.style.zIndex = String(100 - a);
			el.style.opacity = hidden ? '0' : '1';
			el.style.pointerEvents = hidden ? 'none' : '';
			el.classList.toggle('is-active', a === 0);
			el.tabIndex = a === 0 ? 0 : -1;
			if (hidden) { el.setAttribute('aria-hidden', 'true'); } else { el.removeAttribute('aria-hidden'); }
		});

		nameEl.textContent = items[active].getAttribute('data-title');
		dateEl.textContent = items[active].getAttribute('data-date');
	}

	function go(i, focus, auto) {
		active = (i + count) % count;
		layout();
		if (focus) { items[active].focus({ preventScroll: true }); }
		// A move by hand restarts the autoplay count, so the cover just
		// chosen stays put for the full interval.
		if (!auto) { restart(); }
	}

	/*
	 * Autoplay: the next issue every two seconds. It holds while the mouse is
	 * on the front cover or keyboard focus is on the shelf, while a book is
	 * open, and while the shelf is off screen or the tab hidden. It never
	 * starts under reduced motion, and the pause button stops it outright.
	 */
	var DELAY = 2000;
	var playBtn = shelf.querySelector('.cbw-shelf__play');
	var autoTimer = 0;
	var held = false;
	var inView = !('IntersectionObserver' in window);

	function restart() {
		window.clearTimeout(autoTimer);
		autoTimer = window.setTimeout(function () {
			if (!shelf.classList.contains('is-paused') && !held && !dlg.open && !document.hidden && inView) {
				go(active + 1, false, true);
			}
			restart();
		}, DELAY);
	}

	function setPaused(paused) {
		shelf.classList.toggle('is-paused', paused);
		if (playBtn) { playBtn.setAttribute('aria-pressed', paused ? 'true' : 'false'); }
		restart();
	}

	if (playBtn) {
		playBtn.addEventListener('click', function () {
			setPaused(!shelf.classList.contains('is-paused'));
		});
	}
	stage.addEventListener('pointerover', function (e) {
		if (e.pointerType === 'mouse') { held = !!e.target.closest('.cbw-shelf__item.is-active'); }
	});
	stage.addEventListener('pointerleave', function () { held = false; });
	shelf.addEventListener('focusin', function (e) {
		if (e.target.matches(':focus-visible') && e.target !== playBtn) { held = true; }
	});
	shelf.addEventListener('focusout', function (e) {
		if (!e.relatedTarget || !shelf.contains(e.relatedTarget)) { held = false; }
	});
	if (!inView) {
		new IntersectionObserver(function (entries) {
			inView = entries[0].isIntersecting;
		}, { threshold: 0.35 }).observe(shelf);
	}

	shelf.addEventListener('click', function (e) {
		var nav = e.target.closest('[data-go]');
		if (nav) { go(active + Number(nav.getAttribute('data-go'))); return; }

		var item = e.target.closest('.cbw-shelf__item');
		if (!item) { return; }
		// A modified click still opens the issue page in a new tab, as a link would.
		if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) { return; }
		e.preventDefault();
		if (swiped) { return; }

		var i = items.indexOf(item);
		if (i === active) { openBook(item); } else { go(i); }
	});

	stage.addEventListener('keydown', function (e) {
		var map = { ArrowRight: active + 1, ArrowLeft: active - 1, Home: 0, End: count - 1 };
		if (!Object.prototype.hasOwnProperty.call(map, e.key)) { return; }
		e.preventDefault();
		go(map[e.key], true);
	});

	(function () {
		var x0 = null;
		stage.addEventListener('pointerdown', function (e) { x0 = e.clientX; });
		stage.addEventListener('pointercancel', function () { x0 = null; });
		// Covers are links, and dragging a link starts native drag-and-drop,
		// which cancels the pointer stream before the swipe can finish.
		stage.addEventListener('dragstart', function (e) { e.preventDefault(); });
		stage.addEventListener('pointerup', function (e) {
			if (x0 === null) { return; }
			var dx = e.clientX - x0;
			x0 = null;
			if (Math.abs(dx) < 40) { return; }
			// The click that follows a swipe must not also select or open a cover.
			swiped = true;
			go(active + (dx < 0 ? 1 : -1));
			window.setTimeout(function () { swiped = false; }, 320);
		});
	}());

	function start() {
		shelf.classList.add('is-ready');
		if (!still()) {
			// Fan out from the centre, nearest covers first.
			items.forEach(function (el, i) { el.style.transitionDelay = (Math.abs(i - active) * 90) + 'ms'; });
			window.setTimeout(function () {
				items.forEach(function (el) { el.style.transitionDelay = ''; });
			}, 1200);
		}
		layout();
		setPaused(still());
	}

	if (still() || !('IntersectionObserver' in window)) {
		start();
	} else {
		var seen = new IntersectionObserver(function (entries) {
			if (!entries[0].isIntersecting) { return; }
			seen.disconnect();
			window.requestAnimationFrame(start);
		}, { threshold: 0.2 });
		seen.observe(shelf);
	}

	window.addEventListener('resize', onFrame(function () {
		if (shelf.classList.contains('is-ready')) { layout(); }
	}));

	/* ================================================================
	   Book
	   ================================================================ */
	var book = dlg.querySelector('.cbw-book__book');
	var fly = dlg.querySelector('.cbw-book__fly');
	var bookStage = dlg.querySelector('.cbw-book__stage');
	var counter = dlg.querySelector('.cbw-book__count');
	var prevBtn = dlg.querySelector('[data-turn="-1"]');
	var nextBtn = dlg.querySelector('[data-turn="1"]');
	var titleName = dlg.querySelector('.cbw-book__title strong');
	var titleDate = dlg.querySelector('.cbw-book__title span');
	var words = {};
	try { words = JSON.parse(dlg.getAttribute('data-l10n')) || {}; } catch (err) { words = {}; }

	var pages = [];
	var leaves = [];
	var pos = 0;          // leaves turned so far
	var single = false;   // one page at a time on narrow screens
	var opener = null;
	var lift = 500;       // z-index for a leaf mid-turn; each new turn goes on top
	var openTimer = 0;
	var closeTimer = 0;
	var swipedBook = false;

	function fill(s, a, b, c) {
		return String(s || '').replace('%1$d', a).replace('%2$d', b).replace('%3$d', c);
	}

	function wantSingle() { return window.innerWidth < 820; }

	function measure() {
		var vw = window.innerWidth;
		var vh = window.innerHeight;
		var h = Math.max(260, Math.min(vh - (vw < 820 ? 150 : 176), 860));
		var w = vw - (single ? 32 : 150);
		// Pages share the covers' 3:4 proportion.
		var pw = Math.floor(single ? Math.min(w, h * 0.75) : Math.min(w / 2, h * 0.75));
		dlg.style.setProperty('--pw', pw + 'px');
		dlg.style.setProperty('--ph', Math.floor(pw / 0.75) + 'px');
		dlg.classList.toggle('is-single', single);
	}

	function face(src, side, index) {
		var el = document.createElement('div');
		el.className = 'cbw-book__face cbw-book__face--' + side;
		if (src) {
			el.appendChild(src.cloneNode(true));
			el.setAttribute('data-page', String(index + 1));
		} else {
			el.className += ' is-blank';
		}
		return el;
	}

	/*
	 * Two pages to a leaf when the book lies open: the front is a right-hand
	 * page, the back is the left-hand page it becomes once turned. One page
	 * to a leaf in single-page mode.
	 */
	function build() {
		book.textContent = '';
		leaves = [];
		var n = single ? pages.length : Math.ceil(pages.length / 2);
		for (var i = 0; i < n; i++) {
			var first = single ? i : i * 2;
			var leaf = document.createElement('div');
			leaf.className = 'cbw-book__leaf';
			leaf.appendChild(face(pages[first], 'front', first));
			leaf.appendChild(face(single ? null : pages[first + 1], 'back', first + 1));
			leaf.addEventListener('transitionend', settle);
			book.appendChild(leaf);
			leaves.push(leaf);
		}
	}

	function last() { return single ? leaves.length - 1 : leaves.length; }

	/* At rest: the turned stack on the left, lowest index at the bottom;
	   the unturned stack on the right, lowest index on top. */
	function rest(leaf, i) { leaf.style.zIndex = String(i < pos ? i + 1 : leaves.length - i); }

	function label() {
		var total = pages.length;
		if (pos === 0) { return words.cover || ''; }
		if (single) { return pos === total - 1 ? (words.back || '') : fill(words.page, pos + 1, total); }
		if (pos === leaves.length) { return words.back || ''; }
		return fill(words.pages, pos * 2, pos * 2 + 1, total);
	}

	function render() {
		leaves.forEach(function (leaf, i) {
			leaf.classList.toggle('is-flipped', i < pos);
			if (!leaf.classList.contains('is-turning')) { rest(leaf, i); }
			// Only the pages facing the reader are reachable by keyboard and screen reader.
			leaf.children[0].inert = i !== pos;
			leaf.children[1].inert = single || i !== pos - 1;
		});

		var shift = 0;
		if (!single) {
			// A closed book shows a single page: centre that, not the empty spread.
			if (pos === 0) { shift = -25; } else if (pos === leaves.length) { shift = 25; }
		}
		book.style.transform = 'translateX(' + shift + '%)';

		prevBtn.disabled = pos === 0;
		nextBtn.disabled = pos === last();
		counter.textContent = label();
	}

	function turn(d) {
		var next = pos + d;
		if (!leaves.length || next < 0 || next > last()) { return; }
		var leaf = d > 0 ? leaves[pos] : leaves[pos - 1];
		if (!still()) {
			leaf.classList.add('is-turning');
			leaf.style.zIndex = String(++lift);
		}
		pos = next;
		render();
	}

	function settle(e) {
		if (e.target !== e.currentTarget || e.propertyName !== 'transform') { return; }
		var leaf = e.currentTarget;
		leaf.classList.remove('is-turning');
		rest(leaf, leaves.indexOf(leaf));
	}

	/* Move with no animation: resizes and Home/End jumps. */
	function snap(p) {
		dlg.classList.add('is-snapping');
		leaves.forEach(function (leaf) { leaf.classList.remove('is-turning'); });
		pos = p;
		render();
		void book.offsetWidth;
		dlg.classList.remove('is-snapping');
	}

	function firstPage() { return single ? pos : (pos === 0 ? 0 : pos * 2 - 1); }
	function posFor(page) { return single ? Math.min(page, pages.length - 1) : (page === 0 ? 0 : Math.floor((page + 1) / 2)); }

	function openBook(item) {
		var tpl = document.getElementById(item.getAttribute('data-book'));
		if (!tpl || dlg.open) { return; }

		window.clearTimeout(closeTimer);
		pages = Array.prototype.slice.call(tpl.content.children);
		opener = item;
		titleName.textContent = item.getAttribute('data-title');
		titleDate.textContent = item.getAttribute('data-date');
		dlg.setAttribute('aria-label', item.getAttribute('data-title') + ', ' + item.getAttribute('data-date'));

		single = wantSingle();
		pos = 0;
		document.documentElement.classList.add('cbw-book-lock');
		dlg.classList.remove('is-open', 'is-closing');
		dlg.classList.add('is-snapping');
		dlg.showModal();
		measure();
		build();
		render();

		if (still()) {
			dlg.classList.remove('is-snapping');
			dlg.classList.add('is-open');
			nextBtn.focus();
			return;
		}

		// Lift the cover off the shelf: start the book exactly where the cover
		// sits, then let it travel to the middle of the reader and open.
		var from = item.querySelector('img').getBoundingClientRect();
		fly.style.transform = 'none';
		var to = leaves[0].children[0].getBoundingClientRect();
		var box = fly.getBoundingClientRect();
		fly.style.transformOrigin = (to.left + to.width / 2 - box.left) + 'px ' + (to.top + to.height / 2 - box.top) + 'px';
		fly.style.transform = 'translate(' +
			(from.left + from.width / 2 - (to.left + to.width / 2)) + 'px,' +
			(from.top + from.height / 2 - (to.top + to.height / 2)) + 'px) scale(' + (from.width / to.width) + ')';
		item.classList.add('is-lifted');
		void fly.offsetWidth;
		dlg.classList.remove('is-snapping');

		window.requestAnimationFrame(function () {
			dlg.classList.add('is-open');
			fly.style.transform = '';
			openTimer = window.setTimeout(function () { turn(1); }, 680);
		});
		nextBtn.focus({ preventScroll: true });
	}

	function closeBook() {
		if (!dlg.open || dlg.classList.contains('is-closing')) { return; }
		window.clearTimeout(openTimer);
		dlg.classList.remove('is-open');
		dlg.classList.add('is-closing');
		closeTimer = window.setTimeout(function () { dlg.close(); }, still() ? 0 : 340);
	}

	/* Runs however the dialog closes, including a browser-forced close. */
	function cleanup() {
		window.clearTimeout(openTimer);
		window.clearTimeout(closeTimer);
		dlg.classList.remove('is-open', 'is-closing', 'is-snapping');
		book.textContent = '';
		leaves = [];
		pages = [];
		fly.style.transform = '';
		fly.style.transformOrigin = '';
		document.documentElement.classList.remove('cbw-book-lock');
		if (opener) {
			opener.classList.remove('is-lifted');
			opener.focus({ preventScroll: true });
		}
	}

	dlg.addEventListener('cancel', function (e) { e.preventDefault(); closeBook(); });
	dlg.addEventListener('close', cleanup);

	dlg.addEventListener('click', function (e) {
		if (e.target.closest('[data-close]')) { closeBook(); return; }
		var t = e.target.closest('[data-turn]');
		if (t) { turn(Number(t.getAttribute('data-turn'))); return; }
		// Links inside a page ("Continue reading") behave as links.
		if (swipedBook || e.target.closest('a') || !e.target.closest('.cbw-book__book')) { return; }
		var r = book.getBoundingClientRect();
		var edge = single ? r.left + r.width * 0.3 : r.left + r.width / 2;
		turn(e.clientX < edge ? -1 : 1);
	});

	dlg.addEventListener('keydown', function (e) {
		switch (e.key) {
			case 'ArrowRight':
			case 'PageDown':
				e.preventDefault(); turn(1); break;
			case 'ArrowLeft':
			case 'PageUp':
				e.preventDefault(); turn(-1); break;
			case 'Home':
				e.preventDefault(); snap(0); break;
			case 'End':
				e.preventDefault(); snap(last()); break;
		}
	});

	(function () {
		var x0 = null;
		bookStage.addEventListener('pointerdown', function (e) { x0 = e.clientX; });
		bookStage.addEventListener('pointercancel', function () { x0 = null; });
		bookStage.addEventListener('pointerup', function (e) {
			if (x0 === null) { return; }
			var dx = e.clientX - x0;
			x0 = null;
			if (Math.abs(dx) < 40) { return; }
			swipedBook = true;
			turn(dx < 0 ? 1 : -1);
			window.setTimeout(function () { swipedBook = false; }, 320);
		});
	}());

	window.addEventListener('resize', onFrame(function () {
		if (!dlg.open || !pages.length) { return; }
		var page = firstPage();
		var was = single;
		single = wantSingle();
		measure();
		if (was !== single) {
			build();
			snap(posFor(page));
		}
	}));
}());
