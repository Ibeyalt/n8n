( function () {
	'use strict';

	// Ce script est chargé en pied de page (après le HTML) : le DOM est déjà prêt,
	// donc on exécute directement, sans attendre "DOMContentLoaded" (qui, à ce
	// stade, s'est déjà déclenché et ne se redéclenchera jamais — un écouteur
	// posé ici resterait sinon inerte, ce qui rendait le menu et les animations
	// silencieusement inactifs).
	function init() {

		// Ne cache/anime le contenu que si ce script s'exécute réellement :
		// sans cette classe, tout reste visible par défaut (voir style.css).
		document.documentElement.classList.add( 'dr-js' );

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

			// Filet de sécurité : si un élément n'entre jamais dans le viewport
			// (page très courte, robot, etc.), on l'affiche quand même après 3s
			// plutôt que de le laisser invisible indéfiniment.
			setTimeout( function () { el.classList.add( 'is-visible' ); }, 3000 );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
