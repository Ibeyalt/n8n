<?php
/**
 * Tâches planifiées : rappels de rendez-vous, nettoyage des liens/téléchargements expirés.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Cron {

	const HOOK = 'dira_bf_hourly_event';

	public static function schedule() {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'hourly', self::HOOK );
		}
	}

	public static function unschedule() {
		$timestamp = wp_next_scheduled( self::HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::HOOK );
		}
	}

	public static function init() {
		add_action( self::HOOK, array( __CLASS__, 'run' ) );
	}

	public static function run() {
		if ( Dira_BF_Settings::get( 'reminders_enabled', 1 ) ) {
			self::send_reminders();
		}
		self::cleanup_expired_downloads();
	}

	private static function send_reminders() {
		global $wpdb;
		$table = Dira_BF_Database::table( 'bookings' );
		$hours_list = (array) Dira_BF_Settings::get( 'reminder_hours_before', array( 24, 2 ) );

		foreach ( $hours_list as $hours ) {
			$hours  = (int) $hours;
			$column = 24 === $hours ? 'reminder_24h_sent' : 'reminder_2h_sent';
			if ( ! in_array( $column, array( 'reminder_24h_sent', 'reminder_2h_sent' ), true ) ) {
				continue; // Seuls 24h et 2h sont câblés en base pour l'instant.
			}

			$target = current_time( 'timestamp' ) + ( $hours * HOUR_IN_SECONDS );

			$bookings = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM $table
					WHERE status = 'confirmed'
					AND $column = 0
					AND TIMESTAMP(booking_date, start_time) BETWEEN %s AND %s",
					gmdate( 'Y-m-d H:i:s', $target - 30 * MINUTE_IN_SECONDS ),
					gmdate( 'Y-m-d H:i:s', $target + 30 * MINUTE_IN_SECONDS )
				)
			);

			foreach ( $bookings as $booking ) {
				Dira_BF_Notifications::send_reminder( $booking );
				$wpdb->update( $table, array( $column => 1 ), array( 'id' => $booking->id ) );
			}
		}
	}

	private static function cleanup_expired_downloads() {
		global $wpdb;
		$table = Dira_BF_Database::table( 'downloads' );
		$wpdb->query( "DELETE FROM $table WHERE expires_at IS NOT NULL AND expires_at < NOW() AND download_count = 0" );
	}
}
