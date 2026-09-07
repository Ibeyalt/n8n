<?php
/**
 * Moteur de réservation : calcul des créneaux disponibles, création et gestion des rendez-vous.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Bookings {

	const STATUSES = array( 'pending', 'confirmed', 'completed', 'cancelled', 'refused', 'no_show' );

	/**
	 * Calcule les créneaux disponibles pour un service à une date donnée.
	 *
	 * @return array Liste de créneaux "HH:MM".
	 */
	public static function get_available_slots( $service_id, $date ) {
		$service = Dira_BF_Services::get( $service_id );
		if ( ! $service || 'active' !== $service->status ) {
			return array();
		}

		if ( ! self::is_valid_date( $date ) ) {
			return array();
		}

		$duration = (int) $service->duration_minutes + (int) $service->buffer_minutes;
		if ( $duration <= 0 ) {
			return array();
		}

		$ranges = Dira_BF_Availability::get_open_ranges_minutes( $date );
		if ( empty( $ranges ) ) {
			return array();
		}

		// Retirer les rendez-vous déjà pris sur ce jour (calendrier partagé, tous services confondus).
		$busy = self::get_busy_ranges_minutes( $date );
		foreach ( $busy as $range ) {
			$ranges = Dira_BF_Availability::subtract_range( $ranges, $range[0], $range[1] );
		}

		$now_ts       = current_time( 'timestamp' );
		$min_notice   = $now_ts + ( (int) $service->min_notice_hours * HOUR_IN_SECONDS );
		$max_notice   = $now_ts + ( (int) $service->max_notice_days * DAY_IN_SECONDS );
		$day_start_ts = strtotime( $date . ' 00:00:00' );

		if ( $day_start_ts > $max_notice ) {
			return array();
		}

		$slots = array();
		foreach ( $ranges as $range ) {
			list( $start, $end ) = $range;
			for ( $slot_start = $start; $slot_start + (int) $service->duration_minutes <= $end; $slot_start += $duration ) {
				$slot_ts = $day_start_ts + ( $slot_start * MINUTE_IN_SECONDS );
				if ( $slot_ts < $min_notice || $slot_ts > $max_notice ) {
					continue;
				}
				$slots[] = Dira_BF_Availability::minutes_to_time( $slot_start );
			}
		}

		return $slots;
	}

	private static function get_busy_ranges_minutes( $date ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'bookings' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT start_time, end_time FROM $table WHERE booking_date = %s AND status IN ('pending','confirmed')",
				$date
			)
		);

		$ranges = array();
		foreach ( $rows as $row ) {
			$ranges[] = array(
				Dira_BF_Availability::time_to_minutes( $row->start_time ),
				Dira_BF_Availability::time_to_minutes( $row->end_time ),
			);
		}
		return $ranges;
	}

	/**
	 * Crée un rendez-vous après re-vérification de la disponibilité du créneau.
	 *
	 * @return array|WP_Error
	 */
	public static function create_booking( array $data ) {
		$service = Dira_BF_Services::get( (int) ( $data['service_id'] ?? 0 ) );
		if ( ! $service || 'active' !== $service->status ) {
			return new WP_Error( 'invalid_service', __( 'Service invalide.', 'dira-booking-files' ) );
		}

		$date  = sanitize_text_field( $data['date'] ?? '' );
		$start = sanitize_text_field( $data['start_time'] ?? '' );

		if ( ! self::is_valid_date( $date ) || ! preg_match( '/^([01]\d|2[0-3]):([0-5]\d)$/', $start ) ) {
			return new WP_Error( 'invalid_slot', __( 'Créneau invalide.', 'dira-booking-files' ) );
		}

		$available = self::get_available_slots( $service->id, $date );
		if ( ! in_array( $start, $available, true ) ) {
			return new WP_Error( 'slot_unavailable', __( 'Ce créneau vient d\'être réservé, merci d\'en choisir un autre.', 'dira-booking-files' ) );
		}

		if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
			return new WP_Error( 'invalid_email', __( 'Adresse email invalide.', 'dira-booking-files' ) );
		}

		$customer_id = Dira_BF_Customers::find_or_create( $data );

		$start_minutes = Dira_BF_Availability::time_to_minutes( $start );
		$end_minutes   = $start_minutes + (int) $service->duration_minutes;

		global $wpdb;
		$table = Dira_BF_Database::table( 'bookings' );
		$now   = current_time( 'mysql' );

		$auto_confirm = (int) Dira_BF_Settings::get( 'auto_confirm', 0 );

		$wpdb->insert(
			$table,
			array(
				'booking_ref' => self::generate_reference(),
				'service_id'  => $service->id,
				'customer_id' => $customer_id,
				'booking_date' => $date,
				'start_time'  => Dira_BF_Availability::minutes_to_time( $start_minutes ) . ':00',
				'end_time'    => Dira_BF_Availability::minutes_to_time( $end_minutes ) . ':00',
				'status'      => $auto_confirm ? 'confirmed' : 'pending',
				'price'       => $service->price,
				'comment'     => sanitize_textarea_field( $data['comment'] ?? '' ),
				'extra_info'  => sanitize_textarea_field( $data['extra_info'] ?? '' ),
				'created_at'  => $now,
				'updated_at'  => $now,
			)
		);

		$booking_id = (int) $wpdb->insert_id;
		$booking    = self::get( $booking_id );

		if ( $booking && class_exists( 'Dira_BF_Notifications' ) ) {
			Dira_BF_Notifications::send_booking_created( $booking );
		}

		do_action( 'dira_bf_booking_created', $booking );

		return $booking;
	}

	public static function update_status( $id, $status ) {
		if ( ! in_array( $status, self::STATUSES, true ) ) {
			return new WP_Error( 'invalid_status', __( 'Statut invalide.', 'dira-booking-files' ) );
		}

		$booking = self::get( $id );
		if ( ! $booking ) {
			return new WP_Error( 'not_found', __( 'Rendez-vous introuvable.', 'dira-booking-files' ) );
		}

		global $wpdb;
		$table = Dira_BF_Database::table( 'bookings' );
		$old_status = $booking->status;

		$wpdb->update(
			$table,
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => (int) $id )
		);

		$booking = self::get( $id );

		if ( $old_status !== $status && class_exists( 'Dira_BF_Notifications' ) ) {
			Dira_BF_Notifications::send_status_changed( $booking, $old_status, $status );
		}

		do_action( 'dira_bf_booking_status_changed', $booking, $old_status, $status );

		return $booking;
	}

	public static function get( $id ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'bookings' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", (int) $id ) );
	}

	public static function get_by_reference( $ref ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'bookings' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE booking_ref = %s", sanitize_text_field( $ref ) ) );
	}

	public static function get_all( array $args = array() ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'bookings' );

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['date'] ) ) {
			$where[]  = 'booking_date = %s';
			$params[] = $args['date'];
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'booking_date >= %s';
			$params[] = $args['date_from'];
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'booking_date <= %s';
			$params[] = $args['date_to'];
		}
		if ( ! empty( $args['customer_id'] ) ) {
			$where[]  = 'customer_id = %d';
			$params[] = (int) $args['customer_id'];
		}

		$limit  = isset( $args['limit'] ) ? (int) $args['limit'] : 100;
		$offset = isset( $args['offset'] ) ? (int) $args['offset'] : 0;

		$sql = "SELECT * FROM $table WHERE " . implode( ' AND ', $where ) . ' ORDER BY booking_date DESC, start_time DESC LIMIT %d OFFSET %d';
		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	public static function counts() {
		global $wpdb;
		$table = Dira_BF_Database::table( 'bookings' );
		$today = current_time( 'Y-m-d' );

		return array(
			'today'     => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE booking_date = %s AND status IN ('pending','confirmed')", $today ) ),
			'upcoming'  => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE booking_date > %s AND status IN ('pending','confirmed')", $today ) ),
			'pending'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'pending'" ),
			'confirmed' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'confirmed'" ),
			'cancelled' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'cancelled'" ),
		);
	}

	private static function generate_reference() {
		global $wpdb;
		$table = Dira_BF_Database::table( 'bookings' );

		do {
			$ref     = 'DRB-' . wp_rand( 10000, 99999 );
			$exists  = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE booking_ref = %s", $ref ) );
		} while ( $exists );

		return $ref;
	}

	private static function is_valid_date( $date ) {
		$d = DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}
}
