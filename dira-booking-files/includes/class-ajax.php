<?php
/**
 * Points d'entrée AJAX publics (front-end), pour éviter de recharger la page à chaque action.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Ajax {

	const NONCE_ACTION = 'dira_bf_public';

	public static function init() {
		$actions = array(
			'dira_get_availability' => 'get_availability',
			'dira_create_booking'   => 'create_booking',
			'dira_cancel_booking'   => 'cancel_booking',
			'dira_lookup_bookings'  => 'lookup_bookings',
			'dira_download_free'    => 'download_free',
			'dira_buy_file'         => 'buy_file',
			'dira_lookup_downloads' => 'lookup_downloads',
		);

		foreach ( $actions as $action => $method ) {
			add_action( "wp_ajax_$action", array( __CLASS__, $method ) );
			add_action( "wp_ajax_nopriv_$action", array( __CLASS__, $method ) );
		}
	}

	private static function verify_nonce() {
		if ( ! check_ajax_referer( self::NONCE_ACTION, 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Session expirée, merci de recharger la page.', 'dira-booking-files' ) ), 403 );
		}
	}

	public static function get_availability() {
		self::verify_nonce();

		$service_id = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
		$date       = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

		if ( ! $service_id || ! $date ) {
			wp_send_json_error( array( 'message' => __( 'Paramètres manquants.', 'dira-booking-files' ) ) );
		}

		$slots = Dira_BF_Bookings::get_available_slots( $service_id, $date );
		wp_send_json_success( array( 'slots' => $slots ) );
	}

	public static function create_booking() {
		self::verify_nonce();

		$fields = array( 'service_id', 'date', 'start_time', 'first_name', 'last_name', 'phone', 'whatsapp', 'email', 'address', 'comment', 'extra_info' );
		$data   = array();
		foreach ( $fields as $field ) {
			$data[ $field ] = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
		}
		$data['service_id'] = absint( $data['service_id'] );

		$result = Dira_BF_Bookings::create_booking( $data );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'booking_ref' => $result->booking_ref,
				'status'      => $result->status,
				'message'     => __( 'Votre demande de rendez-vous a bien été enregistrée.', 'dira-booking-files' ),
			)
		);
	}

	public static function cancel_booking() {
		self::verify_nonce();

		if ( ! Dira_BF_Settings::get( 'allow_client_cancel', 1 ) ) {
			wp_send_json_error( array( 'message' => __( 'L\'annulation en ligne n\'est pas autorisée, merci de nous contacter.', 'dira-booking-files' ) ) );
		}

		$ref   = isset( $_POST['booking_ref'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_ref'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

		$booking = Dira_BF_Bookings::get_by_reference( $ref );
		if ( ! $booking ) {
			wp_send_json_error( array( 'message' => __( 'Rendez-vous introuvable.', 'dira-booking-files' ) ) );
		}

		$customer = Dira_BF_Customers::get( $booking->customer_id );
		if ( ! $customer || strtolower( $customer->email ) !== strtolower( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Vérification impossible avec ces informations.', 'dira-booking-files' ) ) );
		}

		Dira_BF_Bookings::update_status( $booking->id, 'cancelled' );
		wp_send_json_success( array( 'message' => __( 'Votre rendez-vous a été annulé.', 'dira-booking-files' ) ) );
	}

	public static function lookup_bookings() {
		self::verify_nonce();

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Adresse email invalide.', 'dira-booking-files' ) ) );
		}

		$customer = Dira_BF_Customers::get_by_email( $email );
		if ( ! $customer ) {
			wp_send_json_success( array( 'bookings' => array() ) );
		}

		$bookings = Dira_BF_Bookings::get_all( array( 'customer_id' => $customer->id, 'limit' => 100 ) );
		$out = array();
		foreach ( $bookings as $booking ) {
			$service  = Dira_BF_Services::get( $booking->service_id );
			$out[] = array(
				'ref'     => $booking->booking_ref,
				'service' => $service->name ?? '',
				'date'    => $booking->booking_date,
				'time'    => substr( $booking->start_time, 0, 5 ),
				'status'  => $booking->status,
			);
		}

		wp_send_json_success( array( 'bookings' => $out ) );
	}

	public static function download_free() {
		self::verify_nonce();

		$file_id = isset( $_POST['file_id'] ) ? absint( $_POST['file_id'] ) : 0;
		$file    = Dira_BF_Files::get( $file_id );

		if ( ! $file || 'publish' !== $file->status || ! $file->is_free ) {
			wp_send_json_error( array( 'message' => __( 'Fichier indisponible.', 'dira-booking-files' ) ) );
		}

		$token = Dira_BF_Files::create_download_token( $file_id );
		if ( is_wp_error( $token ) ) {
			wp_send_json_error( array( 'message' => $token->get_error_message() ) );
		}

		wp_send_json_success( array( 'url' => add_query_arg( 'dira_download', $token, home_url( '/' ) ) ) );
	}

	public static function buy_file() {
		self::verify_nonce();

		$file_id = isset( $_POST['file_id'] ) ? absint( $_POST['file_id'] ) : 0;
		$gateway_id = isset( $_POST['gateway'] ) ? sanitize_key( $_POST['gateway'] ) : '';

		$customer_data = array(
			'first_name' => isset( $_POST['first_name'] ) ? wp_unslash( $_POST['first_name'] ) : '',
			'last_name'  => isset( $_POST['last_name'] ) ? wp_unslash( $_POST['last_name'] ) : '',
			'email'      => isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '',
			'phone'      => isset( $_POST['phone'] ) ? wp_unslash( $_POST['phone'] ) : '',
		);

		$order = Dira_BF_Orders::create( $file_id, $customer_data, $gateway_id ?: Dira_BF_Settings::get( 'payment_gateway', 'manual' ) );
		if ( is_wp_error( $order ) ) {
			wp_send_json_error( array( 'message' => $order->get_error_message() ) );
		}

		$gateway = $gateway_id ? Dira_BF_Gateway_Manager::get( $gateway_id ) : Dira_BF_Gateway_Manager::get_default();
		if ( ! $gateway ) {
			wp_send_json_error( array( 'message' => __( 'Aucun moyen de paiement disponible.', 'dira-booking-files' ) ) );
		}

		$result = $gateway->process_order( $order );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		$result['order_ref'] = $order->order_ref;
		wp_send_json_success( $result );
	}

	public static function lookup_downloads() {
		self::verify_nonce();

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Adresse email invalide.', 'dira-booking-files' ) ) );
		}

		$customer = Dira_BF_Customers::get_by_email( $email );
		if ( ! $customer ) {
			wp_send_json_success( array( 'downloads' => array() ) );
		}

		global $wpdb;
		$downloads_table = Dira_BF_Database::table( 'downloads' );
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $downloads_table WHERE customer_id = %d ORDER BY id DESC", $customer->id ) );

		$out = array();
		foreach ( $rows as $row ) {
			$file = Dira_BF_Files::get( $row->file_id );
			$out[] = array(
				'title'          => $file->title ?? '',
				'download_count' => (int) $row->download_count,
				'max_downloads'  => (int) $row->max_downloads,
				'expires_at'     => $row->expires_at,
				'url'            => add_query_arg( 'dira_download', $row->token, home_url( '/' ) ),
			);
		}

		wp_send_json_success( array( 'downloads' => $out ) );
	}
}
