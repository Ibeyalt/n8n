<?php
/**
 * Horaires hebdomadaires, jours/horaires exceptionnels et créneaux bloqués.
 *
 * Stockage dans wp_dira_availability :
 * - type "weekly"           : day_of_week (0=dimanche..6=samedi) + start_time/end_time.
 * - type "exception_closed" : specific_date fermé toute la journée.
 * - type "exception_open"   : specific_date avec horaires spécifiques (remplace le hebdo).
 * - type "blocked"          : specific_date + start_time/end_time + reason, retiré des disponibilités.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Availability {

	public static function get_weekly_hours() {
		global $wpdb;
		$table = Dira_BF_Database::table( 'availability' );

		$rows = $wpdb->get_results( "SELECT * FROM $table WHERE type = 'weekly' ORDER BY day_of_week ASC, start_time ASC" );

		$by_day = array_fill( 0, 7, array() );
		foreach ( $rows as $row ) {
			$by_day[ (int) $row->day_of_week ][] = array(
				'id'    => (int) $row->id,
				'start' => substr( $row->start_time, 0, 5 ),
				'end'   => substr( $row->end_time, 0, 5 ),
			);
		}
		return $by_day;
	}

	/**
	 * Remplace entièrement les horaires hebdomadaires.
	 *
	 * @param array $by_day [ day_of_week => [ [start,end], ... ] ].
	 */
	public static function set_weekly_hours( array $by_day ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'availability' );

		$wpdb->query( "DELETE FROM $table WHERE type = 'weekly'" );

		$now = current_time( 'mysql' );
		foreach ( $by_day as $day => $ranges ) {
			$day = (int) $day;
			if ( $day < 0 || $day > 6 || empty( $ranges ) ) {
				continue;
			}
			foreach ( $ranges as $range ) {
				$start = self::sanitize_time( $range['start'] ?? '' );
				$end   = self::sanitize_time( $range['end'] ?? '' );
				if ( ! $start || ! $end || $start >= $end ) {
					continue;
				}
				$wpdb->insert(
					$table,
					array(
						'type'        => 'weekly',
						'day_of_week' => $day,
						'start_time'  => $start,
						'end_time'    => $end,
						'created_at'  => $now,
					)
				);
			}
		}
	}

	public static function add_exception( $date, $closed = true, $start = null, $end = null, $reason = '' ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'availability' );

		return $wpdb->insert(
			$table,
			array(
				'type'          => $closed ? 'exception_closed' : 'exception_open',
				'specific_date' => sanitize_text_field( $date ),
				'start_time'    => $start ? self::sanitize_time( $start ) : null,
				'end_time'      => $end ? self::sanitize_time( $end ) : null,
				'reason'        => sanitize_text_field( $reason ),
				'created_at'    => current_time( 'mysql' ),
			)
		);
	}

	public static function add_blocked_slot( $date, $start, $end, $reason = '' ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'availability' );

		return $wpdb->insert(
			$table,
			array(
				'type'          => 'blocked',
				'specific_date' => sanitize_text_field( $date ),
				'start_time'    => self::sanitize_time( $start ),
				'end_time'      => self::sanitize_time( $end ),
				'reason'        => sanitize_text_field( $reason ),
				'created_at'    => current_time( 'mysql' ),
			)
		);
	}

	public static function delete_entry( $id ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'availability' );
		return $wpdb->delete( $table, array( 'id' => (int) $id ) );
	}

	public static function get_entries( $type = null ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'availability' );

		if ( $type ) {
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE type = %s ORDER BY specific_date ASC, start_time ASC", $type ) );
		}
		return $wpdb->get_results( "SELECT * FROM $table WHERE type != 'weekly' ORDER BY specific_date ASC, start_time ASC" );
	}

	/**
	 * Calcule les plages ouvertes (en minutes depuis minuit) pour une date donnée,
	 * après application des jours fermés / horaires exceptionnels / créneaux bloqués.
	 */
	public static function get_open_ranges_minutes( $date ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'availability' );

		// 1. Jour fermé exceptionnellement ?
		$closed = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE type = 'exception_closed' AND specific_date = %s", $date ) );
		if ( $closed ) {
			return array();
		}

		// 2. Horaires exceptionnels pour cette date ?
		$open_exceptions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE type = 'exception_open' AND specific_date = %s", $date ) );

		if ( ! empty( $open_exceptions ) ) {
			$ranges = array();
			foreach ( $open_exceptions as $row ) {
				$ranges[] = array( self::time_to_minutes( $row->start_time ), self::time_to_minutes( $row->end_time ) );
			}
		} else {
			$day_of_week = (int) gmdate( 'w', strtotime( $date ) );
			$weekly      = self::get_weekly_hours();
			$ranges      = array();
			foreach ( $weekly[ $day_of_week ] as $range ) {
				$ranges[] = array( self::time_to_minutes( $range['start'] ), self::time_to_minutes( $range['end'] ) );
			}
		}

		// 3. Retirer les créneaux bloqués.
		$blocked = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table WHERE type = 'blocked' AND specific_date = %s", $date ) );
		foreach ( $blocked as $block ) {
			$ranges = self::subtract_range( $ranges, self::time_to_minutes( $block->start_time ), self::time_to_minutes( $block->end_time ) );
		}

		return $ranges;
	}

	/**
	 * Soustrait un intervalle [b_start, b_end] d'une liste de plages [start, end].
	 */
	public static function subtract_range( array $ranges, $b_start, $b_end ) {
		$result = array();
		foreach ( $ranges as $range ) {
			list( $start, $end ) = $range;
			if ( $b_end <= $start || $b_start >= $end ) {
				$result[] = $range;
				continue;
			}
			if ( $b_start > $start ) {
				$result[] = array( $start, min( $b_start, $end ) );
			}
			if ( $b_end < $end ) {
				$result[] = array( max( $b_end, $start ), $end );
			}
		}
		return $result;
	}

	public static function time_to_minutes( $time ) {
		$parts = explode( ':', substr( $time, 0, 5 ) );
		if ( count( $parts ) < 2 ) {
			return 0;
		}
		return ( (int) $parts[0] ) * 60 + (int) $parts[1];
	}

	public static function minutes_to_time( $minutes ) {
		$minutes = max( 0, (int) $minutes );
		return sprintf( '%02d:%02d', floor( $minutes / 60 ), $minutes % 60 );
	}

	private static function sanitize_time( $value ) {
		$value = sanitize_text_field( $value );
		if ( ! preg_match( '/^([01]\d|2[0-3]):([0-5]\d)/', $value, $m ) ) {
			return '';
		}
		return $m[1] . ':' . $m[2] . ':00';
	}
}
