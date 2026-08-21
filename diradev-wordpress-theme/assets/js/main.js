( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {

		/* Menu mobile */
		var toggle = document.querySelector( '.dr-nav-toggle' );
		var menu   = document.querySelector( '.dr-nav-menu' );

		if ( toggle && menu ) {
			toggle.addEventListener( 'click', function () {
				var isOpen = menu.classList.toggle( 'is-open' );
				toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			} );

			menu.querySelectorAll( 'a' ).forEach( function ( link ) {
				link.addEventListener( 'click', function () {
					menu.classList.remove( 'is-open' );
					toggle.setAttribute( 'aria-expanded', 'false' );
				} );
			} );
		}

		/* Animations au scroll */
		var reveals = document.querySelectorAll( '.dr-reveal' );
		if ( ! reveals.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			reveals.forEach( function ( el ) { el.classList.add( 'is-visible' ); } );
			return;
		}

		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					io.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' } );

		reveals.forEach( function ( el, index ) {
			el.style.transitionDelay = ( index % 4 ) * 0.08 + 's';
			io.observe( el );
		} );

	} );
} )();
