<?php
/**
 * Commandes de fichiers payants.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Orders {

	const STATUSES = array( 'pending', 'paid', 'failed', 'refunded', 'cancelled' );

	public static function create( $file_id, array $customer_data, $gateway = 'manual' ) {
		$file = Dira_BF_Files::get( $file_id );
		if ( ! $file || 'publish' !== $file->status ) {
			return new WP_Error( 'invalid_file', __( 'Fichier invalide.', 'dira-booking-files' ) );
		}
		if ( $file->is_free ) {
			return new WP_Error( 'file_is_free', __( 'Ce fichier est gratuit.', 'dira-booking-files' ) );
		}
		if ( empty( $customer_data['email'] ) || ! is_email( $customer_data['email'] ) ) {
			return new WP_Error( 'invalid_email', __( 'Adresse email invalide.', 'dira-booking-files' ) );
		}

		$customer_id = Dira_BF_Customers::find_or_create( $customer_data );

		global $wpdb;
		$table = Dira_BF_Database::table( 'file_orders' );
		$now   = current_time( 'mysql' );

		$wpdb->insert(
			$table,
			array(
				'order_ref'      => self::generate_reference(),
				'file_id'        => (int) $file_id,
				'customer_id'    => $customer_id,
				'amount'         => $file->price,
				'currency'       => Dira_BF_Settings::get( 'currency', 'XOF' ),
				'payment_method' => sanitize_key( $gateway ),
				'status'         => 'pending',
				'created_at'     => $now,
				'updated_at'     => $now,
			)
		);

		$order = self::get( (int) $wpdb->insert_id );
		do_action( 'dira_bf_order_created', $order );

		return $order;
	}

	public static function mark_paid( $order_id, $transaction_id = '' ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'file_orders' );

		$wpdb->update(
			$table,
			array(
				'status'         => 'paid',
				'transaction_id' => sanitize_text_field( $transaction_id ),
				'updated_at'     => current_time( 'mysql' ),
			),
			array( 'id' => (int) $order_id )
		);

		$order = self::get( $order_id );

		if ( $order ) {
			$token = Dira_BF_Files::create_download_token( $order->file_id, $order->id, $order->customer_id );
			if ( ! is_wp_error( $token ) && class_exists( 'Dira_BF_Notifications' ) ) {
				Dira_BF_Notifications::send_order_paid( $order, $token );
			}
			do_action( 'dira_bf_order_paid', $order );
		}

		return $order;
	}

	public static function mark_status( $order_id, $status ) {
		if ( ! in_array( $status, self::STATUSES, true ) ) {
			return new WP_Error( 'invalid_status', __( 'Statut invalide.', 'dira-booking-files' ) );
		}
		if ( 'paid' === $status ) {
			return self::mark_paid( $order_id );
		}

		global $wpdb;
		$table = Dira_BF_Database::table( 'file_orders' );
		$wpdb->update( $table, array( 'status' => $status, 'updated_at' => current_time( 'mysql' ) ), array( 'id' => (int) $order_id ) );

		return self::get( $order_id );
	}

	public static function get( $id ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'file_orders' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", (int) $id ) );
	}

	public static function get_by_reference( $ref ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'file_orders' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE order_ref = %s", sanitize_text_field( $ref ) ) );
	}

	public static function get_all( array $args = array() ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'file_orders' );

		$where  = array( '1=1' );
		$params = array();

		if ( ! empty( $args['status'] ) ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( ! empty( $args['customer_id'] ) ) {
			$where[]  = 'customer_id = %d';
			$params[] = (int) $args['customer_id'];
		}

		$limit  = isset( $args['limit'] ) ? (int) $args['limit'] : 100;
		$offset = isset( $args['offset'] ) ? (int) $args['offset'] : 0;

		$sql = "SELECT * FROM $table WHERE " . implode( ' AND ', $where ) . ' ORDER BY id DESC LIMIT %d OFFSET %d';
		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	public static function revenue_total() {
		global $wpdb;
		$table = Dira_BF_Database::table( 'file_orders' );
		return (float) $wpdb->get_var( "SELECT COALESCE(SUM(amount),0) FROM $table WHERE status = 'paid'" );
	}

	private static function generate_reference() {
		global $wpdb;
		$table = Dira_BF_Database::table( 'file_orders' );

		do {
			$ref    = 'DFO-' . wp_rand( 10000, 99999 );
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE order_ref = %s", $ref ) );
		} while ( $exists );

		return $ref;
	}
}
