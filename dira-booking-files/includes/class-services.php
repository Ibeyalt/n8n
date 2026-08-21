<?php
/**
 * Gestion des services réservables.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Services {

	public static function create( array $data ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'services' );

		$name = sanitize_text_field( $data['name'] ?? '' );
		$now  = current_time( 'mysql' );

		$wpdb->insert(
			$table,
			array(
				'name'             => $name,
				'slug'             => sanitize_title( $name ),
				'description'      => sanitize_textarea_field( $data['description'] ?? '' ),
				'image_id'         => isset( $data['image_id'] ) ? (int) $data['image_id'] : null,
				'duration_minutes' => isset( $data['duration_minutes'] ) ? (int) $data['duration_minutes'] : 30,
				'price'            => isset( $data['price'] ) ? (float) $data['price'] : 0,
				'status'           => in_array( $data['status'] ?? 'active', array( 'active', 'inactive' ), true ) ? $data['status'] : 'active',
				'color'            => sanitize_hex_color( $data['color'] ?? '' ) ?: '#4f46e5',
				'min_notice_hours' => isset( $data['min_notice_hours'] ) ? (int) $data['min_notice_hours'] : 2,
				'max_notice_days'  => isset( $data['max_notice_days'] ) ? (int) $data['max_notice_days'] : 60,
				'max_people'       => isset( $data['max_people'] ) ? max( 1, (int) $data['max_people'] ) : 1,
				'buffer_minutes'   => isset( $data['buffer_minutes'] ) ? (int) $data['buffer_minutes'] : 0,
				'sort_order'       => isset( $data['sort_order'] ) ? (int) $data['sort_order'] : 0,
				'created_at'       => $now,
				'updated_at'       => $now,
			)
		);

		return (int) $wpdb->insert_id;
	}

	public static function update( $id, array $data ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'services' );

		$fields = array( 'updated_at' => current_time( 'mysql' ) );

		if ( isset( $data['name'] ) ) {
			$fields['name'] = sanitize_text_field( $data['name'] );
			$fields['slug'] = sanitize_title( $data['name'] );
		}
		if ( isset( $data['description'] ) ) {
			$fields['description'] = sanitize_textarea_field( $data['description'] );
		}
		if ( isset( $data['image_id'] ) ) {
			$fields['image_id'] = (int) $data['image_id'];
		}
		if ( isset( $data['duration_minutes'] ) ) {
			$fields['duration_minutes'] = (int) $data['duration_minutes'];
		}
		if ( isset( $data['price'] ) ) {
			$fields['price'] = (float) $data['price'];
		}
		if ( isset( $data['status'] ) && in_array( $data['status'], array( 'active', 'inactive' ), true ) ) {
			$fields['status'] = $data['status'];
		}
		if ( isset( $data['color'] ) ) {
			$fields['color'] = sanitize_hex_color( $data['color'] ) ?: '#4f46e5';
		}
		if ( isset( $data['min_notice_hours'] ) ) {
			$fields['min_notice_hours'] = (int) $data['min_notice_hours'];
		}
		if ( isset( $data['max_notice_days'] ) ) {
			$fields['max_notice_days'] = (int) $data['max_notice_days'];
		}
		if ( isset( $data['max_people'] ) ) {
			$fields['max_people'] = max( 1, (int) $data['max_people'] );
		}
		if ( isset( $data['buffer_minutes'] ) ) {
			$fields['buffer_minutes'] = (int) $data['buffer_minutes'];
		}

		return $wpdb->update( $table, $fields, array( 'id' => (int) $id ) );
	}

	public static function delete( $id ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'services' );
		return $wpdb->delete( $table, array( 'id' => (int) $id ) );
	}

	public static function get( $id ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'services' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", (int) $id ) );
	}

	public static function get_all( $only_active = false ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'services' );

		if ( $only_active ) {
			return $wpdb->get_results( "SELECT * FROM $table WHERE status = 'active' ORDER BY sort_order ASC, name ASC" );
		}

		return $wpdb->get_results( "SELECT * FROM $table ORDER BY sort_order ASC, name ASC" );
	}

	public static function count() {
		global $wpdb;
		$table = Dira_BF_Database::table( 'services' );
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}
}
