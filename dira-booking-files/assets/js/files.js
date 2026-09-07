( function () {
	'use strict';

	if ( typeof DiraBF === 'undefined' ) {
		return;
	}

	function post( action, data ) {
		var formData = new FormData();
		formData.append( 'action', action );
		formData.append( 'nonce', DiraBF.nonce );
		Object.keys( data || {} ).forEach( function ( key ) {
			formData.append( key, data[ key ] );
		} );

		return fetch( DiraBF.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: formData } )
			.then( function ( res ) { return res.json(); } );
	}

	function showAlert( el, message, type ) {
		if ( ! el ) { return; }
		el.textContent = message;
		el.hidden = false;
		el.className = ( el.className.replace( /is-(error|success)/g, '' ) + ' is-' + ( type || 'error' ) ).trim();
	}

	function escapeHtml( str ) {
		var div = document.createElement( 'div' );
		div.textContent = String( str || '' );
		return div.innerHTML;
	}

	/* -------------------- Grille de fichiers -------------------- */

	function initFilesGrid( root ) {
		var form  = root.querySelector( '[data-role="filters"]' );
		var cards = root.querySelectorAll( '.dira-files-card' );

		function applyFilters() {
			var search   = ( form.querySelector( '[name="search"]' ).value || '' ).toLowerCase();
			var category = form.querySelector( '[name="category"]' ).value;
			var type     = form.querySelector( '[name="type"]' ).value;
			var orderby  = form.querySelector( '[name="orderby"]' ).value;

			var visible = [];

			cards.forEach( function ( card ) {
				var matches = true;
				if ( search && card.getAttribute( 'data-title' ).indexOf( search ) === -1 ) { matches = false; }
				if ( category && card.getAttribute( 'data-category' ) !== category ) { matches = false; }
				if ( type && card.getAttribute( 'data-type' ) !== type ) { matches = false; }
				card.style.display = matches ? '' : 'none';
				if ( matches ) { visible.push( card ); }
			} );

			var grid = root.querySelector( '[data-role="grid"]' );
			visible.sort( function ( a, b ) {
				if ( 'popular' === orderby ) {
					return parseInt( b.getAttribute( 'data-downloads' ), 10 ) - parseInt( a.getAttribute( 'data-downloads' ), 10 );
				}
				return parseInt( b.getAttribute( 'data-created' ), 10 ) - parseInt( a.getAttribute( 'data-created' ), 10 );
			} ).forEach( function ( card ) { grid.appendChild( card ); } );
		}

		if ( form ) {
			form.addEventListener( 'input', applyFilters );
			form.addEventListener( 'change', applyFilters );
			form.addEventListener( 'submit', function ( e ) { e.preventDefault(); } );
		}
	}

	/* -------------------- Téléchargement gratuit -------------------- */

	function initFreeDownloads( root ) {
		root.querySelectorAll( '[data-action="download"]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var fileId = btn.getAttribute( 'data-file-id' );
				btn.disabled = true;

				post( 'dira_download_free', { file_id: fileId } ).then( function ( res ) {
					btn.disabled = false;
					if ( res.success && res.data.url ) {
						window.location.href = res.data.url;
					}
				} );
			} );
		} );
	}

	/* -------------------- Achat de fichier -------------------- */

	function initBuyModal( root ) {
		var modal    = root.querySelector( '[data-role="buy-modal"]' );
		if ( ! modal ) { return; }

		var titleEl  = modal.querySelector( '[data-role="buy-title"]' );
		var alertEl  = modal.querySelector( '[data-role="buy-alert"]' );
		var currentFileId = null;

		root.querySelectorAll( '[data-action="buy"]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				currentFileId = btn.getAttribute( 'data-file-id' );
				titleEl.textContent = btn.getAttribute( 'data-file-title' );
				modal.hidden = false;
			} );
		} );

		modal.querySelectorAll( '[data-action="close-modal"]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () { modal.hidden = true; } );
		} );

		var confirmBtn = modal.querySelector( '[data-action="confirm-buy"]' );
		confirmBtn.addEventListener( 'click', function () {
			var fields = {};
			modal.querySelectorAll( '[data-field]' ).forEach( function ( input ) {
				fields[ input.getAttribute( 'data-field' ) ] = input.value;
			} );

			if ( ! fields.email ) {
				showAlert( alertEl, 'Merci de renseigner votre email.', 'error' );
				return;
			}

			confirmBtn.disabled = true;
			var payload = Object.assign( { file_id: currentFileId }, fields );

			post( 'dira_buy_file', payload ).then( function ( res ) {
				confirmBtn.disabled = false;

				if ( ! res.success ) {
					showAlert( alertEl, res.data && res.data.message ? res.data.message : DiraBF.i18n.error, 'error' );
					return;
				}

				if ( 'redirect' === res.data.type && res.data.url ) {
					window.location.href = res.data.url;
					return;
				}

				showAlert( alertEl, res.data.message || 'Commande enregistrée.', 'success' );
			} );
		} );
	}

	/* -------------------- Mes téléchargements -------------------- */

	function initDownloadsLookup( root ) {
		var btn = root.querySelector( '[data-action="lookup-downloads"]' );
		if ( ! btn ) { return; }

		var input   = root.querySelector( '[data-role="lookup-email"]' );
		var list    = root.querySelector( '[data-role="downloads-list"]' );
		var alertEl = root.querySelector( '[data-role="lookup-alert"]' );

		btn.addEventListener( 'click', function () {
			var email = input.value.trim();
			if ( ! email ) { return; }

			post( 'dira_lookup_downloads', { email: email } ).then( function ( res ) {
				if ( ! res.success ) {
					showAlert( alertEl, res.data.message, 'error' );
					return;
				}
				alertEl.hidden = true;
				list.innerHTML = '';

				if ( ! res.data.downloads.length ) {
					list.innerHTML = '<p class="dira-files-empty">Aucun fichier trouvé.</p>';
					return;
				}

				res.data.downloads.forEach( function ( item ) {
					var row = document.createElement( 'div' );
					row.className = 'dira-files-list-item';
					row.innerHTML =
						'<strong>' + escapeHtml( item.title ) + '</strong>' +
						'<span>' + item.download_count + ' / ' + ( item.max_downloads || '∞' ) + '</span>' +
						'<a class="dira-files-btn" href="' + item.url + '">Télécharger</a>';
					list.appendChild( row );
				} );
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.dira-files-app' ).forEach( function ( root ) {
			if ( root.querySelector( '[data-role="filters"]' ) ) { initFilesGrid( root ); }
			initFreeDownloads( root );
			initBuyModal( root );
			initDownloadsLookup( root );
		} );
	} );
} )();
