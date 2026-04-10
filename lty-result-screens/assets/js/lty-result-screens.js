( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var overlay = document.querySelector( '.lty-rs-overlay' );
		if ( ! overlay ) {
			return;
		}

		// Lock body scroll while overlay is visible.
		document.body.classList.add( 'lty-rs-scroll-locked' );

		// Move focus into the overlay so screen readers announce it immediately.
		var firstFocusable = overlay.querySelector( 'button, [href], [tabindex]:not([tabindex="-1"])' );
		if ( firstFocusable ) {
			firstFocusable.focus();
		}

		// ── Dismiss helper ──────────────────────────────────────────────────
		function dismiss() {
			overlay.style.transition = 'opacity 0.3s ease';
			overlay.style.opacity    = '0';

			// Use transitionend to avoid setTimeout/animation mismatch.
			overlay.addEventListener( 'transitionend', function handler( e ) {
				if ( e.propertyName !== 'opacity' ) {
					return;
				}
				overlay.removeEventListener( 'transitionend', handler );
				overlay.style.display = 'none';
				document.body.classList.remove( 'lty-rs-scroll-locked' );

				// Return focus to a sensible element on the underlying page.
				var returnTarget = document.querySelector( 'h1, .entry-title, main' ) || document.body;
				returnTarget.focus();
			} );
		}

		// ── Dismiss on explicit buttons only — not on backdrop click ────────
		overlay.querySelectorAll( '[data-lty-rs-dismiss]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', dismiss );
		} );

		// ── Esc key closes the overlay ───────────────────────────────────────
		document.addEventListener( 'keydown', function escHandler( e ) {
			if ( e.key === 'Escape' || e.key === 'Esc' ) {
				document.removeEventListener( 'keydown', escHandler );
				dismiss();
			}
		} );

		// ── Copy coupon code to clipboard ───────────────────────────────────────
		function copyText( text ) {
			// Modern Clipboard API (HTTPS / secure context)
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				return navigator.clipboard.writeText( text );
			}
			// Fallback for HTTP / non-secure contexts
			var ta = document.createElement( 'textarea' );
			ta.value = text;
			ta.style.cssText = 'position:fixed;opacity:0;pointer-events:none';
			document.body.appendChild( ta );
			ta.select();
			document.execCommand( 'copy' );
			document.body.removeChild( ta );
			return Promise.resolve();
		}

		overlay.querySelectorAll( '.lty-rs-copy-code' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var code    = btn.dataset.code;
				var confirm = btn.querySelector( '.lty-rs-copy-code__confirm' );

				copyText( code ).then( function () {
					if ( confirm ) {
						confirm.classList.remove( 'hidden' );
						setTimeout( function () {
							confirm.classList.add( 'hidden' );
						}, 2000 );
					}
				} );
			} );
		} );

		// ── Focus trap — keep Tab/Shift+Tab cycling within the overlay ───────
		var focusables = Array.prototype.slice.call(
			overlay.querySelectorAll( 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])' )
		).filter( function ( el ) {
			return ! el.disabled && el.offsetParent !== null;
		} );

		if ( focusables.length ) {
			var first = focusables[ 0 ];
			var last  = focusables[ focusables.length - 1 ];

			overlay.addEventListener( 'keydown', function ( e ) {
				if ( e.key !== 'Tab' ) {
					return;
				}
				if ( e.shiftKey ) {
					if ( document.activeElement === first ) {
						e.preventDefault();
						last.focus();
					}
				} else {
					if ( document.activeElement === last ) {
						e.preventDefault();
						first.focus();
					}
				}
			} );
		}
	} );
} )();
