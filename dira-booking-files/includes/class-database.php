<?php
/**
 * Création et accès aux tables dédiées de l'extension.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Database {

	/**
	 * Retourne le nom complet (préfixé) d'une table de l'extension.
	 *
	 * @param string $name Nom court, ex. "services".
	 */
	public static function table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'dira_' . $name;
	}

	/**
	 * Crée ou met à jour les tables via dbDelta (idempotent).
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset_collate = $wpdb->get_charset_collate();

		$services = self::table( 'services' );
		$customers = self::table( 'customers' );
		$bookings = self::table( 'bookings' );
		$availability = self::table( 'availability' );
		$file_categories = self::table( 'file_categories' );
		$files = self::table( 'files' );
		$file_orders = self::table( 'file_orders' );
		$downloads = self::table( 'downloads' );
		$notifications = self::table( 'notifications' );

		$sql = array();

		$sql[] = "CREATE TABLE $services (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(190) NOT NULL,
			slug VARCHAR(190) NOT NULL,
			description TEXT NULL,
			image_id BIGINT UNSIGNED NULL,
			duration_minutes INT UNSIGNED NOT NULL DEFAULT 30,
			price DECIMAL(12,2) NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'active',
			color VARCHAR(20) NOT NULL DEFAULT '#4f46e5',
			min_notice_hours INT UNSIGNED NOT NULL DEFAULT 2,
			max_notice_days INT UNSIGNED NOT NULL DEFAULT 60,
			max_people INT UNSIGNED NOT NULL DEFAULT 1,
			buffer_minutes INT UNSIGNED NOT NULL DEFAULT 0,
			sort_order INT NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY status (status)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $customers (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT UNSIGNED NULL,
			first_name VARCHAR(120) NOT NULL DEFAULT '',
			last_name VARCHAR(120) NOT NULL DEFAULT '',
			email VARCHAR(190) NOT NULL DEFAULT '',
			phone VARCHAR(60) NOT NULL DEFAULT '',
			whatsapp VARCHAR(60) NOT NULL DEFAULT '',
			address TEXT NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY email (email),
			KEY user_id (user_id)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $bookings (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			booking_ref VARCHAR(40) NOT NULL,
			service_id BIGINT UNSIGNED NOT NULL,
			customer_id BIGINT UNSIGNED NOT NULL,
			booking_date DATE NOT NULL,
			start_time TIME NOT NULL,
			end_time TIME NOT NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			price DECIMAL(12,2) NOT NULL DEFAULT 0,
			comment TEXT NULL,
			extra_info TEXT NULL,
			reminder_24h_sent TINYINT(1) NOT NULL DEFAULT 0,
			reminder_2h_sent TINYINT(1) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY booking_ref (booking_ref),
			KEY service_id (service_id),
			KEY customer_id (customer_id),
			KEY booking_date (booking_date),
			KEY status (status)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $availability (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			service_id BIGINT UNSIGNED NULL,
			type VARCHAR(20) NOT NULL DEFAULT 'weekly',
			day_of_week TINYINT NULL,
			specific_date DATE NULL,
			start_time TIME NULL,
			end_time TIME NULL,
			reason VARCHAR(190) NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY type (type),
			KEY day_of_week (day_of_week),
			KEY specific_date (specific_date)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $file_categories (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(190) NOT NULL,
			slug VARCHAR(190) NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $files (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(190) NOT NULL,
			slug VARCHAR(190) NOT NULL,
			description TEXT NULL,
			cover_image_id BIGINT UNSIGNED NULL,
			file_path VARCHAR(500) NULL,
			file_size BIGINT UNSIGNED NULL,
			file_type VARCHAR(20) NULL,
			category_id BIGINT UNSIGNED NULL,
			author VARCHAR(190) NULL,
			price DECIMAL(12,2) NOT NULL DEFAULT 0,
			is_free TINYINT(1) NOT NULL DEFAULT 1,
			status VARCHAR(20) NOT NULL DEFAULT 'draft',
			max_downloads INT UNSIGNED NOT NULL DEFAULT 0,
			link_expiry_days INT UNSIGNED NOT NULL DEFAULT 0,
			download_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY slug (slug),
			KEY status (status),
			KEY category_id (category_id),
			KEY is_free (is_free)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $file_orders (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			order_ref VARCHAR(40) NOT NULL,
			file_id BIGINT UNSIGNED NOT NULL,
			customer_id BIGINT UNSIGNED NOT NULL,
			amount DECIMAL(12,2) NOT NULL DEFAULT 0,
			currency VARCHAR(10) NOT NULL DEFAULT 'XOF',
			payment_method VARCHAR(40) NOT NULL DEFAULT 'manual',
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			transaction_id VARCHAR(190) NULL,
			created_at DATETIME NOT NULL,
			updated_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY order_ref (order_ref),
			KEY file_id (file_id),
			KEY customer_id (customer_id),
			KEY status (status)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $downloads (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			file_id BIGINT UNSIGNED NOT NULL,
			order_id BIGINT UNSIGNED NULL,
			customer_id BIGINT UNSIGNED NULL,
			ip_address VARCHAR(60) NULL,
			token VARCHAR(64) NOT NULL,
			download_count INT UNSIGNED NOT NULL DEFAULT 0,
			max_downloads INT UNSIGNED NOT NULL DEFAULT 0,
			expires_at DATETIME NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY token (token),
			KEY file_id (file_id),
			KEY order_id (order_id),
			KEY customer_id (customer_id)
		) $charset_collate;";

		$sql[] = "CREATE TABLE $notifications (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			type VARCHAR(60) NOT NULL,
			recipient VARCHAR(190) NOT NULL,
			subject VARCHAR(255) NULL,
			status VARCHAR(20) NOT NULL DEFAULT 'sent',
			context_id BIGINT UNSIGNED NULL,
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id),
			KEY type (type),
			KEY context_id (context_id)
		) $charset_collate;";

		foreach ( $sql as $statement ) {
			dbDelta( $statement );
		}
	}
}
