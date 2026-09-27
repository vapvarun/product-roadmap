/**
 * Admin feedback without browser dialogs (handle: roadmap-admin-feedback).
 *
 * - Two-step confirm: a link or button with data-roadmap-confirm="Warning"
 *   needs two clicks. The first click shows the warning next to it (announced
 *   to screen readers) and relabels it with data-roadmap-confirm-label; a
 *   second click within 5 seconds proceeds as normal (link, submit, or the
 *   element's own click handlers). Runs in the capture phase, so it holds back
 *   the element's other handlers on the arming click.
 * - roadmapAdminNotice( message, type ): a standard dismissible admin notice
 *   at the top of the screen. type: success | error | warning | info.
 *
 * Shared by Free and Pro admin screens; replaces alert() / confirm().
 *
 * @package ProductRoadmap
 * @since 1.8.0
 */
( function () {
	'use strict';

	var ARM_MS = 5000;

	function disarm( el ) {
		el.removeAttribute( 'data-roadmap-armed' );
		if ( el._roadmapLabel !== undefined ) {
			el.innerHTML = el._roadmapLabel; // The element's own markup (icons kept).
			delete el._roadmapLabel;
		}
		var note = el.nextElementSibling;
		if ( note && note.classList.contains( 'roadmap-confirm-note' ) ) {
			note.remove();
		}
	}

	document.addEventListener(
		'click',
		function ( event ) {
			var el = event.target.closest( '[data-roadmap-confirm]' );
			if ( ! el ) {
				return;
			}
			if ( el.hasAttribute( 'data-roadmap-armed' ) ) {
				clearTimeout( el._roadmapTimer );
				disarm( el );
				return; // Second click: let the action run.
			}

			event.preventDefault();
			event.stopImmediatePropagation();

			el.setAttribute( 'data-roadmap-armed', '' );
			if ( el.dataset.roadmapConfirmLabel ) {
				el._roadmapLabel = el.innerHTML;
				el.textContent = el.dataset.roadmapConfirmLabel;
			}
			var note = document.createElement( 'span' );
			note.className = 'roadmap-confirm-note';
			note.setAttribute( 'role', 'status' );
			note.style.marginInlineStart = '8px';
			note.textContent = el.dataset.roadmapConfirm;
			el.insertAdjacentElement( 'afterend', note );

			el._roadmapTimer = setTimeout( function () {
				disarm( el );
			}, ARM_MS );
		},
		true
	);

	window.roadmapAdminNotice = function ( message, type ) {
		var notice = document.createElement( 'div' );
		notice.className = 'notice notice-' + ( type || 'info' ) + ' is-dismissible';
		notice.setAttribute( 'role', 'error' === type ? 'alert' : 'status' );
		var p = document.createElement( 'p' );
		p.textContent = message;
		notice.appendChild( p );

		// Where core puts notices: after the screen's first heading.
		var wrap = document.querySelector( '.wrap' ) || document.querySelector( '#wpbody-content' );
		var heading = wrap && wrap.querySelector( 'h1, h2' );
		if ( heading ) {
			heading.insertAdjacentElement( 'afterend', notice );
		} else if ( wrap ) {
			wrap.prepend( notice );
		}

		// Core adds the dismiss button to notices added after load on this event.
		if ( window.jQuery ) {
			window.jQuery( document ).trigger( 'wp-updates-notice-added' );
		}
		notice.scrollIntoView( { block: 'nearest' } );
		return notice;
	};
} )();
