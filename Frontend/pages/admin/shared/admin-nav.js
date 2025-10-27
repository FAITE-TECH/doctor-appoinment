// Admin sidebar mobile hamburger toggle
// This script enhances existing admin pages by:
// - Injecting a hamburger button on small screens if missing
// - Toggling the sidebar visibility on mobile without breaking desktop layout
// It is defensive and works with the current markup used across admin pages.

(function () {
	function ready(fn) {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	}

	function findNav() {
		return document.querySelector('nav.bg-gray-800');
	}

	function findLayoutContainer(nav) {
		// Layout container is typically the next sibling after the top nav
		if (!nav) return null;
		var el = nav.nextElementSibling;
		// Ensure it's the flex container wrapping sidebar and main content
		if (el && el.classList.contains('flex')) return el;
		return document.querySelector('.flex.flex-col.md\\:flex-row');
	}

	function findSidebar(layout) {
		if (!layout) return null;
		// The sidebar is the first child with white bg + shadow (as in current pages)
		var candidate = layout.querySelector(':scope > div.bg-white.shadow-lg');
		if (candidate) return candidate;
		// Fallbacks if structure differs slightly
		candidate = document.querySelector('div.bg-white.shadow-lg.md\\:min-h-screen');
		if (candidate) return candidate;
		// Last resort: first .bg-white.shadow-lg in the page
		return document.querySelector('div.bg-white.shadow-lg');
	}

	function ensureHamburger(nav) {
		if (!nav) return null;
		var existing = nav.querySelector('#mobileMenuToggle');
		if (existing) {
			// Normalize icon content if using a font-awesome stub
			if (!existing.textContent.trim()) existing.textContent = '☰';
			return existing;
		}

		// Create a button and inject it into the right-side controls if present, else the left brand area
		var btn = document.createElement('button');
		btn.id = 'mobileMenuToggle';
		btn.setAttribute('aria-label', 'Toggle menu');
		btn.setAttribute('aria-expanded', 'false');
		btn.className = 'md:hidden text-2xl focus:outline-none p-2 hover:bg-gray-700 rounded-md transition-colors';
		btn.textContent = '☰';

		var right = nav.querySelector('.flex.items-center.space-x-4');
		var left = nav.querySelector('.flex.items-center');
		if (right) {
			right.insertBefore(btn, right.firstChild);
		} else if (left) {
			left.appendChild(btn);
		} else {
			nav.appendChild(btn);
		}
		return btn;
	}

	function shouldStartHidden() {
		// Tailwind md breakpoint ~768px; hide on smaller screens initially
		return window.innerWidth < 768;
	}

	function init() {
		var nav = findNav();
		var layout = findLayoutContainer(nav);
		var sidebar = findSidebar(layout);
		if (!nav || !layout || !sidebar) {
			// Nothing to do on pages without the standard admin layout
			return;
		}

		// Mark for easier querying later
		sidebar.setAttribute('data-admin-sidebar', '');

		// Hide sidebar on load for small screens only
		if (shouldStartHidden()) {
			if (!sidebar.classList.contains('hidden')) sidebar.classList.add('hidden');
		}

		var btn = ensureHamburger(nav);
		if (!btn) return;

		function isHidden() {
			return sidebar.classList.contains('hidden');
		}

		function showSidebar() {
			sidebar.classList.remove('hidden');
			btn.setAttribute('aria-expanded', 'true');
		}

		function hideSidebar() {
			// Only auto-hide on mobile; on desktop the sidebar should remain visible
			if (window.innerWidth < 768) {
				sidebar.classList.add('hidden');
				btn.setAttribute('aria-expanded', 'false');
			}
		}

		btn.addEventListener('click', function () {
			if (isHidden()) {
				showSidebar();
			} else {
				hideSidebar();
			}
		});

		// Close the sidebar when a link inside it is clicked (mobile only)
		sidebar.addEventListener('click', function (e) {
			var t = e.target;
			if (t && (t.tagName === 'A' || t.closest('a'))) {
				hideSidebar();
			}
		});

		// Handle resize: ensure sidebar visible on desktop, preserve hidden on mobile
		window.addEventListener('resize', function () {
			if (window.innerWidth >= 768) {
				sidebar.classList.remove('hidden');
				btn.setAttribute('aria-expanded', 'true');
			} else {
				// Keep state; if user hasn't opened it yet, keep hidden by default
				if (!btn || btn.getAttribute('aria-expanded') !== 'true') {
					sidebar.classList.add('hidden');
					btn.setAttribute('aria-expanded', 'false');
				}
			}
		});

		// ESC to close on mobile
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') hideSidebar();
		});
	}

	ready(init);
})();

