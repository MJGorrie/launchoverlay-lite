/**
 * LaunchOverlay — public JS  v1.1.1
 *
 * The overlay span is injected server-side (PHP) so it is present in the
 * HTML from the initial page load. No DOM manipulation is needed for rendering.
 *
 * This script ensures .lo-img-wrap receives display:block when a theme
 * overrides it, and re-runs after AJAX product reloads.
 *
 * No jQuery dependency.
 */
( function () {
	'use strict';

	function fixWrapDisplay() {
		var wraps = document.querySelectorAll( '.lo-img-wrap' );
		for ( var i = 0; i < wraps.length; i++ ) {
			var d = window.getComputedStyle( wraps[ i ] ).display;
			if ( d === 'inline' || d === 'inline-block' ) {
				wraps[ i ].style.display = 'block';
			}
		}
	}

	// Run on DOM ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', fixWrapDisplay );
	} else {
		fixWrapDisplay();
	}

	// Re-run after WooCommerce AJAX events (cart fragments, infinite scroll)
	document.body.addEventListener( 'wc_fragments_loaded',   fixWrapDisplay );
	document.body.addEventListener( 'wc_fragments_refreshed', fixWrapDisplay );
	document.body.addEventListener( 'post-load',             fixWrapDisplay );

} )();
