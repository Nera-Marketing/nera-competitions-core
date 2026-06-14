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

		// ── Prize pagination ────────────────────────────────────────────────
		( function initPager() {
			var grid  = overlay.querySelector( '[data-lty-rs-prizes]' );
			var pager = overlay.querySelector( '[data-lty-rs-pager]' );
			if ( ! grid ) {
				return;
			}

			var cards     = Array.prototype.slice.call( grid.children );
			var PAGE_SIZE = 6;
			var pageCount = Math.ceil( cards.length / PAGE_SIZE );
			var meta      = overlay.querySelector( '[data-lty-rs-meta]' );
			var reduce    = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

			// Replay the staggered fade/slide-in on the active page's cards.
			function animatePage( current ) {
				if ( reduce ) {
					return;
				}
				var pos = 0;
				cards.forEach( function ( card, idx ) {
					if ( Math.floor( idx / PAGE_SIZE ) !== current ) {
						return;
					}
					card.style.animation = 'none';
					void card.offsetWidth; // force reflow so the animation restarts
					card.style.animation = 'rs-card-enter 0.4s ease-out ' + ( pos * 50 ) + 'ms both';
					pos++;
				} );
			}

			if ( pageCount <= 1 || ! pager ) {
				animatePage( 0 ); // single page: still fade the cards in
				return;
			}

			var prevBtn  = pager.querySelector( '[data-lty-rs-prev]' );
			var nextBtn  = pager.querySelector( '[data-lty-rs-next]' );
			var dotsWrap = pager.querySelector( '[data-lty-rs-dots]' );
			var current  = 0;
			var dots     = [];

			for ( var i = 0; i < pageCount; i++ ) {
				var dot = document.createElement( 'button' );
				dot.type = 'button';
				dot.className = 'lty-rs-pager__dot';
				dot.setAttribute( 'aria-label', 'Go to page ' + ( i + 1 ) );
				( function ( idx ) {
					dot.addEventListener( 'click', function () { go( idx ); } );
				} )( i );
				dotsWrap.appendChild( dot );
				dots.push( dot );
			}

			function render() {
				cards.forEach( function ( card, idx ) {
					card.style.display = ( Math.floor( idx / PAGE_SIZE ) === current ) ? '' : 'none';
				} );
				dots.forEach( function ( d, idx ) {
					d.classList.toggle( 'lty-rs-pager__dot--active', idx === current );
				} );
				prevBtn.disabled = current === 0;
				nextBtn.disabled = current === pageCount - 1;
				if ( meta ) {
					meta.innerHTML = '<b>' + cards.length + '</b> ' +
						( cards.length === 1 ? 'prize' : 'prizes' ) +
						' · Page ' + ( current + 1 ) + ' of ' + pageCount;
				}
				animatePage( current );
			}

			function go( n ) {
				current = Math.max( 0, Math.min( pageCount - 1, n ) );
				render();
			}

			prevBtn.addEventListener( 'click', function () { go( current - 1 ); } );
			nextBtn.addEventListener( 'click', function () { go( current + 1 ); } );
			overlay.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'ArrowLeft' ) { go( current - 1 ); }
				else if ( e.key === 'ArrowRight' ) { go( current + 1 ); }
			} );

			pager.hidden = false;
			if ( meta ) { meta.hidden = false; }
			render();
		} )();
	} );
} )();
