/* global jQuery, loAdminData */
/**
 * LaunchOverlay Lite — Admin JS
 * Handles: color pickers, live preview, style picker cards.
 */
( function ( $ ) {
	'use strict';

	var POS_CLASSES   = 'lo-pos-top-left lo-pos-top-center lo-pos-top-right lo-pos-bottom-left lo-pos-bottom-center lo-pos-bottom-right lo-pos-center'.split( ' ' );
	var STYLE_CLASSES = 'lo-style-banner lo-style-badge lo-style-ribbon'.split( ' ' );

	$( function () {

		var $preview = $( '#lo-preview-badge' );

		// ── Color pickers ────────────────────────────────────────────────────
		$( '.lo-color-field' ).wpColorPicker( {
			change: function () {
				// Slight delay so wpColorPicker has written new value to input
				setTimeout( syncPreviewColors, 20 );
			},
			clear: function () {
				setTimeout( syncPreviewColors, 20 );
			},
		} );

		// ── Preview sync ─────────────────────────────────────────────────────
		function getVal( id, fallback ) {
			var v = $( '#' + id ).val();
			return v || fallback;
		}

		function syncPreviewColors() {
			var bg  = getVal( 'overlay_color_coming_soon', '#3b5bdb' );
			var txt = getVal( 'overlay_color_text',        '#ffffff' );
			$preview.css( { 'background-color': bg, color: txt } );
		}

		function syncPreviewStyle() {
			var style = $( 'input[name="overlay_style"]:checked' ).val() || 'banner';
			$preview.removeClass( STYLE_CLASSES.join( ' ' ) ).addClass( 'lo-style-' + style );
			$( '.lo-style-option' ).removeClass( 'is-selected' );
			$( '.lo-style-option input:checked' ).closest( '.lo-style-option' ).addClass( 'is-selected' );
		}

		function syncPreviewPosition() {
			var pos = $( '#overlay_position' ).val() || 'top-left';
			$preview.removeClass( POS_CLASSES.join( ' ' ) ).addClass( 'lo-pos-' + pos );
		}

		// ── Events ───────────────────────────────────────────────────────────
		$( 'input[name="overlay_style"]' ).on( 'change', syncPreviewStyle );
		$( '#overlay_position' ).on( 'change', syncPreviewPosition );

		// Style card click — select hidden radio
		$( document ).on( 'click', '.lo-style-option', function () {
			$( this ).find( 'input[type="radio"]' ).prop( 'checked', true ).trigger( 'change' );
		} );

		// ── Init ─────────────────────────────────────────────────────────────
		syncPreviewColors();
		syncPreviewStyle();
		syncPreviewPosition();

	} );

} ( jQuery ) );
