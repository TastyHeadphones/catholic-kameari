/**
 * Mobile navigation drawer.
 *
 * Opens on the hamburger, closes on the X, on Escape, and on any link inside.
 * Focus is moved into and back out of the drawer, and the page behind it is
 * locked while it is open.
 */
( function () {
	'use strict';

	var drawer = document.getElementById( 'kameari-mobile-nav' );
	var opener = document.querySelector( '[data-kameari-menu-open]' );
	var closer = document.querySelector( '[data-kameari-menu-close]' );

	if ( ! drawer || ! opener ) {
		return;
	}

	function open() {
		drawer.classList.add( 'open' );
		drawer.setAttribute( 'aria-hidden', 'false' );
		opener.setAttribute( 'aria-expanded', 'true' );
		document.body.style.overflow = 'hidden';

		if ( closer ) {
			closer.focus();
		}
	}

	function close( returnFocus ) {
		drawer.classList.remove( 'open' );
		drawer.setAttribute( 'aria-hidden', 'true' );
		opener.setAttribute( 'aria-expanded', 'false' );
		document.body.style.overflow = '';

		if ( returnFocus ) {
			opener.focus();
		}
	}

	opener.addEventListener( 'click', open );

	if ( closer ) {
		closer.addEventListener( 'click', function () {
			close( true );
		} );
	}

	drawer.addEventListener( 'click', function ( event ) {
		if ( event.target.closest( 'a' ) ) {
			close( false );
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key && drawer.classList.contains( 'open' ) ) {
			close( true );
		}
	} );

	// Keep the drawer state sane when the viewport grows past the breakpoint.
	var wide = window.matchMedia( '(min-width: 981px)' );
	var onChange = function ( event ) {
		if ( event.matches && drawer.classList.contains( 'open' ) ) {
			close( false );
		}
	};

	if ( wide.addEventListener ) {
		wide.addEventListener( 'change', onChange );
	} else if ( wide.addListener ) {
		wide.addListener( onChange );
	}
}() );
