// Enfold Rotating Header Logo - front-end swap
// Runs client-side so it works correctly even behind full-page caching:
// the cached HTML always has the default logo, and this script swaps it
// on every single page view, in the visitor's own browser.
(function () {
	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
		} else {
			document.addEventListener('DOMContentLoaded', fn);
		}
	}

	ready(function () {
		if (typeof rleLogos === 'undefined' || !rleLogos.length) {
			return;
		}

		var logo = rleLogos[Math.floor(Math.random() * rleLogos.length)];

		// Enfold's standard header logo markup: <span class="logo avia-standard-logo"><a ...><img ...></a></span>
		// querySelectorAll (not just querySelector) in case the theme renders more than
		// one instance (e.g. a mobile menu duplicate) - all instances get the same logo.
		var imgs = document.querySelectorAll('.logo.avia-standard-logo img');
		if (!imgs.length) {
			return;
		}

		imgs.forEach(function (img) {
			img.src = logo.src;

			if (logo.srcset) {
				img.setAttribute('srcset', logo.srcset);
			} else {
				img.removeAttribute('srcset');
			}

			if (logo.sizes) {
				img.setAttribute('sizes', logo.sizes);
			} else {
				img.removeAttribute('sizes');
			}

			if (logo.width) {
				img.setAttribute('width', logo.width);
			}
			if (logo.height) {
				img.setAttribute('height', logo.height);
			}
			if (logo.alt) {
				img.setAttribute('alt', logo.alt);
			}
		});
	});
})();
