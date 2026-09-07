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

	function hideAlert( el ) {
		if ( el ) { el.hidden = true; }
	}

	/* -------------------- Assistant de réservation -------------------- */

	function initBookingWizard( root ) {
		var state = { service_id: null, service_name: '', duration: 0, date: '', start_time: '' };

		var steps    = root.querySelectorAll( '.dira-booking-step' );
		var progress = root.querySelectorAll( '.dira-booking-progress-item' );
		var alertEl  = root.querySelector( '[data-role="alert"]' );

		function goTo( stepNumber ) {
			steps.forEach( function ( step ) {
				step.hidden = parseInt( step.getAttribute( 'data-step' ), 10 ) !== stepNumber;
			} );
			progress.forEach( function ( item ) {
				var n = parseInt( item.getAttribute( 'data-progress' ), 10 );
				item.classList.toggle( 'is-active', n === Math.min( stepNumber, 4 ) );
				item.classList.toggle( 'is-done', n < Math.min( stepNumber, 4 ) );
			} );
			hideAlert( alertEl );
		}

		root.querySelectorAll( '.dira-booking-service-card' ).forEach( function ( card ) {
			card.addEventListener( 'click', function () {
				state.service_id = card.getAttribute( 'data-service-id' );
				state.service_name = card.getAttribute( 'data-service-name' );
				var label = root.querySelector( '[data-role="selected-service"]' );
				if ( label ) { label.textContent = state.service_name; }
				goTo( 2 );
			} );
		} );

		var dateInput = root.querySelector( '[data-role="date-input"]' );
		var slotsBox  = root.querySelector( '[data-role="slots"]' );

		if ( dateInput ) {
			dateInput.addEventListener( 'change', function () {
				state.date = dateInput.value;
				state.start_time = '';
				if ( ! state.date || ! state.service_id ) { return; }

				slotsBox.innerHTML = '<p class="dira-booking-loading">' + DiraBF.i18n.loading + '</p>';

				post( 'dira_get_availability', { service_id: state.service_id, date: state.date } ).then( function ( res ) {
					slotsBox.innerHTML = '';
					if ( ! res.success || ! res.data.slots.length ) {
						slotsBox.innerHTML = '<p class="dira-booking-empty">' + DiraBF.i18n.noSlots + '</p>';
						return;
					}
					res.data.slots.forEach( function ( slot ) {
						var btn = document.createElement( 'button' );
						btn.type = 'button';
						btn.className = 'dira-booking-slot';
						btn.textContent = slot;
						btn.addEventListener( 'click', function () {
							slotsBox.querySelectorAll( '.dira-booking-slot' ).forEach( function ( b ) { b.classList.remove( 'is-selected' ); } );
							btn.classList.add( 'is-selected' );
							state.start_time = slot;
							goTo( 3 );
						} );
						slotsBox.appendChild( btn );
					} );
				} );
			} );
		}

		root.querySelectorAll( '[data-action="back"]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var current = parseInt( btn.closest( '.dira-booking-step' ).getAttribute( 'data-step' ), 10 );
				goTo( Math.max( 1, current - 1 ) );
			} );
		} );

		var toRecapBtn = root.querySelector( '[data-action="to-recap"]' );
		if ( toRecapBtn ) {
			toRecapBtn.addEventListener( 'click', function () {
				var required = [ 'last_name', 'first_name', 'phone', 'email' ];
				var missing = required.some( function ( field ) {
					var input = root.querySelector( '[data-field="' + field + '"]' );
					return ! input || ! input.value.trim();
				} );
				if ( missing ) {
					showAlert( alertEl, DiraBF.i18n.error, 'error' );
					return;
				}

				var recap = root.querySelector( '[data-role="recap"]' );
				var fields = {};
				root.querySelectorAll( '[data-field]' ).forEach( function ( input ) {
					fields[ input.getAttribute( 'data-field' ) ] = input.value;
				} );
				state.fields = fields;

				recap.innerHTML =
					'<dl>' +
					'<dt>' + DiraBF.i18n.confirm + '</dt>' +
					'<dd>' + escapeHtml( state.service_name ) + '</dd>' +
					'<dt>Date</dt><dd>' + escapeHtml( state.date ) + '</dd>' +
					'<dt>Heure</dt><dd>' + escapeHtml( state.start_time ) + '</dd>' +
					'<dt>Nom</dt><dd>' + escapeHtml( fields.first_name + ' ' + fields.last_name ) + '</dd>' +
					'<dt>Téléphone</dt><dd>' + escapeHtml( fields.phone ) + '</dd>' +
					'<dt>Email</dt><dd>' + escapeHtml( fields.email ) + '</dd>' +
					'</dl>';

				goTo( 4 );
			} );
		}

		var submitBtn = root.querySelector( '[data-action="submit"]' );
		if ( submitBtn ) {
			submitBtn.addEventListener( 'click', function () {
				submitBtn.disabled = true;
				submitBtn.textContent = DiraBF.i18n.processing;

				var payload = Object.assign( { service_id: state.service_id, date: state.date, start_time: state.start_time }, state.fields || {} );

				post( 'dira_create_booking', payload ).then( function ( res ) {
					submitBtn.disabled = false;
					submitBtn.textContent = DiraBF.i18n.confirm;

					if ( ! res.success ) {
						showAlert( alertEl, res.data && res.data.message ? res.data.message : DiraBF.i18n.error, 'error' );
						return;
					}

					var details = root.querySelector( '[data-role="success-details"]' );
					if ( details ) {
						details.textContent = 'Référence : ' + res.data.booking_ref;
					}
					goTo( 5 );
				} );
			} );
		}
	}

	function escapeHtml( str ) {
		var div = document.createElement( 'div' );
		div.textContent = String( str || '' );
		return div.innerHTML;
	}

	/* -------------------- Mes rendez-vous -------------------- */

	function initBookingLookup( root ) {
		var btn   = root.querySelector( '[data-action="lookup-bookings"]' );
		var input = root.querySelector( '[data-role="lookup-email"]' );
		var list  = root.querySelector( '[data-role="bookings-list"]' );
		var alertEl = root.querySelector( '[data-role="lookup-alert"]' );

		if ( ! btn ) { return; }

		btn.addEventListener( 'click', function () {
			var email = input.value.trim();
			if ( ! email ) { return; }

			post( 'dira_lookup_bookings', { email: email } ).then( function ( res ) {
				if ( ! res.success ) {
					showAlert( alertEl, res.data.message, 'error' );
					return;
				}
				hideAlert( alertEl );
				list.innerHTML = '';

				if ( ! res.data.bookings.length ) {
					list.innerHTML = '<p class="dira-booking-empty">Aucun rendez-vous trouvé.</p>';
					return;
				}

				res.data.bookings.forEach( function ( booking ) {
					var row = document.createElement( 'div' );
					row.className = 'dira-booking-list-item';
					row.innerHTML =
						'<strong>' + escapeHtml( booking.service ) + '</strong>' +
						'<span>' + escapeHtml( booking.date ) + ' ' + escapeHtml( booking.time ) + '</span>' +
						'<span class="dira-booking-status dira-booking-status-' + escapeHtml( booking.status ) + '">' + escapeHtml( booking.status ) + '</span>' +
						'<span>' + escapeHtml( booking.ref ) + '</span>';
					list.appendChild( row );
				} );
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.dira-booking-app' ).forEach( function ( root ) {
			if ( root.querySelector( '.dira-booking-progress' ) ) {
				initBookingWizard( root );
			}
			if ( root.querySelector( '[data-action="lookup-bookings"]' ) ) {
				initBookingLookup( root );
			}
		} );
	} );
} )();
