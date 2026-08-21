( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var tabs = document.querySelectorAll( '.dira-bf-tabs .nav-tab' );
		if ( ! tabs.length ) {
			return;
		}

		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function ( e ) {
				e.preventDefault();

				tabs.forEach( function ( t ) { t.classList.remove( 'nav-tab-active' ); } );
				tab.classList.add( 'nav-tab-active' );

				var target = tab.getAttribute( 'data-tab' );
				document.querySelectorAll( '.dira-bf-tab-panel' ).forEach( function ( panel ) {
					panel.hidden = panel.getAttribute( 'data-panel' ) !== target;
				} );
			} );
		} );
	} );
} )();
