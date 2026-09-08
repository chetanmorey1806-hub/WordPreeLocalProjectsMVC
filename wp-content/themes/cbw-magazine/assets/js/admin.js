/**
 * Counts the dashboard tiles up from zero.
 *
 * Progressive: the number is already in the markup, so if this never runs
 * the panel still reads correctly. Skipped entirely when the reader has
 * asked for reduced motion.
 */
(function () {
	'use strict';

	var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var nums = document.querySelectorAll('.cbw-dash__num[data-count]');
	if (!nums.length || reduce) { return; }

	function run(el) {
		var target = parseInt(el.getAttribute('data-count'), 10);
		if (isNaN(target) || target === 0) { return; }

		var start = null;
		var dur = Math.min(900, 260 + target * 4);

		function step(ts) {
			if (start === null) { start = ts; }
			var p = Math.min(1, (ts - start) / dur);
			// ease-out, so it settles rather than stopping dead
			var eased = 1 - Math.pow(1 - p, 3);
			el.textContent = Math.round(target * eased);
			if (p < 1) { requestAnimationFrame(step); }
			else { el.textContent = target; }
		}
		el.textContent = '0';
		requestAnimationFrame(step);
	}

	Array.prototype.forEach.call(nums, function (el, i) {
		setTimeout(function () { run(el); }, 120 + i * 60);
	});
}());
