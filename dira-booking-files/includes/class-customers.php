<?php
/**
 * Gestion des clients.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Customers {

	/**
	 * Trouve un client par email, sinon le crée.
	 *
	 * @param array $data first_name, last_name, email, phone, whatsapp, address.
	 * @return int ID du client.
	 */
	public static function find_or_create( array $data ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'customers' );

		$email = sanitize_email( $data['email'] ?? '' );

		if ( $email ) {
			$existing_id = $wpdb->get_var(
				$wpdb->prepare( "SELECT id FROM $table WHERE email = %s ORDER BY id DESC LIMIT 1", $email )
			);
			if ( $existing_id ) {
				self::update(
					(int) $existing_id,
					array(
						'first_name' => $data['first_name'] ?? '',
						'last_name'  => $data['last_name'] ?? '',
						'phone'      => $data['phone'] ?? '',
						'whatsapp'   => $data['whatsapp'] ?? '',
						'address'    => $data['address'] ?? '',
					)
				);
				return (int) $existing_id;
			}
		}

		return self::create( $data );
	}

	public static function create( array $data ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'customers' );

		$wpdb->insert(
			$table,
			array(
				'user_id'    => get_current_user_id() ?: null,
				'first_name' => sanitize_text_field( $data['first_name'] ?? '' ),
				'last_name'  => sanitize_text_field( $data['last_name'] ?? '' ),
				'email'      => sanitize_email( $data['email'] ?? '' ),
				'phone'      => sanitize_text_field( $data['phone'] ?? '' ),
				'whatsapp'   => sanitize_text_field( $data['whatsapp'] ?? '' ),
				'address'    => sanitize_textarea_field( $data['address'] ?? '' ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return (int) $wpdb->insert_id;
	}

	public static function update( $id, array $data ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'customers' );

		$fields = array();
		foreach ( array( 'first_name', 'last_name', 'phone', 'whatsapp' ) as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$fields[ $key ] = sanitize_text_field( $data[ $key ] );
			}
		}
		if ( isset( $data['address'] ) ) {
			$fields['address'] = sanitize_textarea_field( $data['address'] );
		}
		if ( isset( $data['email'] ) ) {
			$fields['email'] = sanitize_email( $data['email'] );
		}

		if ( empty( $fields ) ) {
			return false;
		}

		return $wpdb->update( $table, $fields, array( 'id' => (int) $id ) );
	}

	public static function get( $id ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'customers' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", (int) $id ) );
	}

	public static function get_by_email( $email ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'customers' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE email = %s ORDER BY id DESC LIMIT 1", sanitize_email( $email ) ) );
	}

	public static function get_all( $args = array() ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'customers' );

		$limit  = isset( $args['limit'] ) ? (int) $args['limit'] : 50;
		$offset = isset( $args['offset'] ) ? (int) $args['offset'] : 0;
		$search = isset( $args['search'] ) ? trim( $args['search'] ) : '';

		$where  = '1=1';
		$params = array();

		if ( '' !== $search ) {
			$like    = '%' . $wpdb->esc_like( $search ) . '%';
			$where  .= ' AND (first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR phone LIKE %s)';
			$params  = array( $like, $like, $like, $like );
		}

		$sql = "SELECT * FROM $table WHERE $where ORDER BY id DESC LIMIT %d OFFSET %d";
		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	public static function count( $args = array() ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'customers' );
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}

	public static function delete( $id ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'customers' );
		return $wpdb->delete( $table, array( 'id' => (int) $id ) );
	}

	public static function full_name( $customer ) {
		if ( ! $customer ) {
			return '';
		}
		return trim( $customer->first_name . ' ' . $customer->last_name );
	}
}
