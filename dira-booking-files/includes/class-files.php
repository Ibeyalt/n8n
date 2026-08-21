<?php
/**
 * Gestion des fichiers numériques (œuvres/documents), catégories et téléchargements sécurisés.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Files {

	const PROTECTED_DIR = 'dira-protected';

	/**
	 * Chemin absolu du dossier protégé de stockage des fichiers.
	 */
	public static function protected_dir() {
		$uploads = wp_upload_dir();
		return trailingslashit( $uploads['basedir'] ) . self::PROTECTED_DIR;
	}

	/**
	 * Crée le dossier protégé + règles d'interdiction d'accès direct (idempotent).
	 */
	public static function ensure_protected_dir() {
		$dir = self::protected_dir();

		if ( ! file_exists( $dir ) ) {
			wp_mkdir_p( $dir );
		}

		$htaccess = $dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			file_put_contents(
				$htaccess,
				"Order deny,allow\nDeny from all\n\n<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n"
			);
		}

		$index = $dir . '/index.php';
		if ( ! file_exists( $index ) ) {
			file_put_contents( $index, "<?php\n// Silence is golden.\n" );
		}
	}

	/* ---------------------------------------------------------------
	 * Catégories
	 * ------------------------------------------------------------- */

	public static function create_category( $name ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'file_categories' );

		$name = sanitize_text_field( $name );
		$wpdb->insert( $table, array( 'name' => $name, 'slug' => sanitize_title( $name ) ) );
		return (int) $wpdb->insert_id;
	}

	public static function delete_category( $id ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'file_categories' );
		return $wpdb->delete( $table, array( 'id' => (int) $id ) );
	}

	public static function get_categories() {
		global $wpdb;
		$table = Dira_BF_Database::table( 'file_categories' );
		return $wpdb->get_results( "SELECT * FROM $table ORDER BY name ASC" );
	}

	/* ---------------------------------------------------------------
	 * Fichiers
	 * ------------------------------------------------------------- */

	public static function create( array $data ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'files' );
		$now   = current_time( 'mysql' );

		$title = sanitize_text_field( $data['title'] ?? '' );

		$wpdb->insert(
			$table,
			array(
				'title'            => $title,
				'slug'             => self::unique_slug( $title ),
				'description'      => sanitize_textarea_field( $data['description'] ?? '' ),
				'cover_image_id'   => isset( $data['cover_image_id'] ) ? (int) $data['cover_image_id'] : null,
				'file_path'        => sanitize_text_field( $data['file_path'] ?? '' ),
				'file_size'        => isset( $data['file_size'] ) ? (int) $data['file_size'] : null,
				'file_type'        => sanitize_text_field( $data['file_type'] ?? '' ),
				'category_id'      => ! empty( $data['category_id'] ) ? (int) $data['category_id'] : null,
				'author'           => sanitize_text_field( $data['author'] ?? '' ),
				'price'            => isset( $data['price'] ) ? (float) $data['price'] : 0,
				'is_free'          => empty( $data['price'] ) ? 1 : 0,
				'status'           => in_array( $data['status'] ?? 'draft', array( 'publish', 'draft' ), true ) ? $data['status'] : 'draft',
				'max_downloads'    => isset( $data['max_downloads'] ) ? (int) $data['max_downloads'] : 0,
				'link_expiry_days' => isset( $data['link_expiry_days'] ) ? (int) $data['link_expiry_days'] : 0,
				'created_at'       => $now,
				'updated_at'       => $now,
			)
		);

		return (int) $wpdb->insert_id;
	}

	public static function update( $id, array $data ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'files' );

		$fields = array( 'updated_at' => current_time( 'mysql' ) );

		if ( isset( $data['title'] ) ) {
			$fields['title'] = sanitize_text_field( $data['title'] );
		}
		foreach ( array( 'description' => 'sanitize_textarea_field', 'file_type' => 'sanitize_text_field', 'author' => 'sanitize_text_field', 'file_path' => 'sanitize_text_field' ) as $key => $fn ) {
			if ( isset( $data[ $key ] ) ) {
				$fields[ $key ] = call_user_func( $fn, $data[ $key ] );
			}
		}
		if ( isset( $data['cover_image_id'] ) ) {
			$fields['cover_image_id'] = (int) $data['cover_image_id'];
		}
		if ( isset( $data['file_size'] ) ) {
			$fields['file_size'] = (int) $data['file_size'];
		}
		if ( isset( $data['category_id'] ) ) {
			$fields['category_id'] = $data['category_id'] ? (int) $data['category_id'] : null;
		}
		if ( isset( $data['price'] ) ) {
			$fields['price']   = (float) $data['price'];
			$fields['is_free'] = ( (float) $data['price'] > 0 ) ? 0 : 1;
		}
		if ( isset( $data['status'] ) && in_array( $data['status'], array( 'publish', 'draft' ), true ) ) {
			$fields['status'] = $data['status'];
		}
		if ( isset( $data['max_downloads'] ) ) {
			$fields['max_downloads'] = (int) $data['max_downloads'];
		}
		if ( isset( $data['link_expiry_days'] ) ) {
			$fields['link_expiry_days'] = (int) $data['link_expiry_days'];
		}

		return $wpdb->update( $table, $fields, array( 'id' => (int) $id ) );
	}

	public static function delete( $id ) {
		global $wpdb;
		$file = self::get( $id );
		if ( $file && $file->file_path ) {
			$path = self::protected_dir() . '/' . ltrim( $file->file_path, '/' );
			if ( file_exists( $path ) ) {
				@unlink( $path );
			}
		}
		$table = Dira_BF_Database::table( 'files' );
		return $wpdb->delete( $table, array( 'id' => (int) $id ) );
	}

	public static function get( $id ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'files' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", (int) $id ) );
	}

	public static function get_by_slug( $slug ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'files' );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE slug = %s", sanitize_title( $slug ) ) );
	}

	public static function query( array $args = array() ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'files' );

		$where  = array( "1=1" );
		$params = array();

		if ( ! ( $args['include_drafts'] ?? false ) ) {
			$where[] = "status = 'publish'";
		}
		if ( ! empty( $args['category_id'] ) ) {
			$where[]  = 'category_id = %d';
			$params[] = (int) $args['category_id'];
		}
		if ( isset( $args['is_free'] ) && '' !== $args['is_free'] ) {
			$where[]  = 'is_free = %d';
			$params[] = (int) $args['is_free'];
		}
		if ( ! empty( $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(title LIKE %s OR description LIKE %s)';
			$params[] = $like;
			$params[] = $like;
		}

		$order_by = 'created_at DESC';
		if ( ( $args['orderby'] ?? '' ) === 'popular' ) {
			$order_by = 'download_count DESC';
		}

		$limit  = isset( $args['limit'] ) ? (int) $args['limit'] : 24;
		$offset = isset( $args['offset'] ) ? (int) $args['offset'] : 0;

		$sql = "SELECT * FROM $table WHERE " . implode( ' AND ', $where ) . " ORDER BY $order_by LIMIT %d OFFSET %d";
		$params[] = $limit;
		$params[] = $offset;

		return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
	}

	public static function count( array $args = array() ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'files' );
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
	}

	private static function unique_slug( $title ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'files' );

		$base = sanitize_title( $title );
		$slug = $base;
		$i    = 2;
		while ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE slug = %s", $slug ) ) ) {
			$slug = $base . '-' . $i;
			$i++;
		}
		return $slug;
	}

	/* ---------------------------------------------------------------
	 * Upload sécurisé
	 * ------------------------------------------------------------- */

	/**
	 * Déplace un fichier téléversé ($_FILES[...]) vers le dossier protégé.
	 *
	 * @return array|WP_Error [ 'path' => relatif, 'size' => int, 'type' => ext ].
	 */
	public static function handle_upload( array $file ) {
		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new WP_Error( 'upload_error', __( 'Téléversement invalide.', 'dira-booking-files' ) );
		}

		$allowed    = (array) Dira_BF_Settings::get( 'allowed_extensions', array() );
		$max_mb     = (int) Dira_BF_Settings::get( 'max_upload_mb', 50 );

		$filetype = wp_check_filetype( $file['name'] );
		$ext      = strtolower( $filetype['ext'] ?? '' );

		if ( ! $ext || ! in_array( $ext, $allowed, true ) ) {
			return new WP_Error( 'invalid_extension', __( 'Extension de fichier non autorisée.', 'dira-booking-files' ) );
		}

		if ( $file['size'] > $max_mb * MB_IN_BYTES ) {
			return new WP_Error( 'file_too_large', sprintf( __( 'Le fichier dépasse la taille maximale autorisée (%d Mo).', 'dira-booking-files' ), $max_mb ) );
		}

		self::ensure_protected_dir();

		$filename = wp_unique_filename( self::protected_dir(), sanitize_file_name( uniqid( 'dira_', true ) . '.' . $ext ) );
		$dest     = self::protected_dir() . '/' . $filename;

		if ( ! move_uploaded_file( $file['tmp_name'], $dest ) ) {
			return new WP_Error( 'move_failed', __( 'Impossible d\'enregistrer le fichier.', 'dira-booking-files' ) );
		}

		return array(
			'path' => $filename,
			'size' => filesize( $dest ),
			'type' => $ext,
		);
	}

	/* ---------------------------------------------------------------
	 * Jetons de téléchargement sécurisés
	 * ------------------------------------------------------------- */

	public static function create_download_token( $file_id, $order_id = null, $customer_id = null ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'downloads' );
		$file  = self::get( $file_id );

		if ( ! $file ) {
			return new WP_Error( 'not_found', __( 'Fichier introuvable.', 'dira-booking-files' ) );
		}

		$max_downloads = (int) $file->max_downloads;
		if ( $max_downloads <= 0 ) {
			$max_downloads = (int) Dira_BF_Settings::get( 'default_max_downloads', 5 );
		}

		$expiry_days = (int) $file->link_expiry_days;
		if ( $expiry_days <= 0 ) {
			$expiry_days = (int) Dira_BF_Settings::get( 'default_link_days', 7 );
		}

		$token = bin2hex( random_bytes( 24 ) );

		$wpdb->insert(
			$table,
			array(
				'file_id'        => (int) $file_id,
				'order_id'       => $order_id ? (int) $order_id : null,
				'customer_id'    => $customer_id ? (int) $customer_id : null,
				'ip_address'     => self::client_ip(),
				'token'          => $token,
				'download_count' => 0,
				'max_downloads'  => $max_downloads,
				'expires_at'     => $expiry_days > 0 ? gmdate( 'Y-m-d H:i:s', strtotime( "+$expiry_days days" ) ) : null,
				'created_at'     => current_time( 'mysql' ),
			)
		);

		return $token;
	}

	/**
	 * Valide un jeton et retourne l'enregistrement de téléchargement, ou WP_Error.
	 */
	public static function validate_token( $token ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'downloads' );

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE token = %s", $token ) );
		if ( ! $row ) {
			return new WP_Error( 'invalid_token', __( 'Lien de téléchargement invalide.', 'dira-booking-files' ) );
		}

		if ( $row->expires_at && strtotime( $row->expires_at ) < time() ) {
			return new WP_Error( 'expired', __( 'Ce lien de téléchargement a expiré.', 'dira-booking-files' ) );
		}

		if ( $row->max_downloads > 0 && $row->download_count >= $row->max_downloads ) {
			return new WP_Error( 'limit_reached', __( 'Limite de téléchargement atteinte.', 'dira-booking-files' ) );
		}

		if ( $row->order_id ) {
			$order = Dira_BF_Orders::get( $row->order_id );
			if ( ! $order || 'paid' !== $order->status ) {
				return new WP_Error( 'not_paid', __( 'Commande non réglée.', 'dira-booking-files' ) );
			}
		}

		return $row;
	}

	/**
	 * Diffuse le fichier au navigateur si le jeton est valide, incrémente les compteurs.
	 */
	public static function stream_download( $token ) {
		$download = self::validate_token( $token );
		if ( is_wp_error( $download ) ) {
			wp_die( esc_html( $download->get_error_message() ), esc_html__( 'Téléchargement impossible', 'dira-booking-files' ), array( 'response' => 403 ) );
		}

		$file = self::get( $download->file_id );
		if ( ! $file || ! $file->file_path ) {
			wp_die( esc_html__( 'Fichier introuvable.', 'dira-booking-files' ), '', array( 'response' => 404 ) );
		}

		$path = self::protected_dir() . '/' . ltrim( $file->file_path, '/' );
		if ( ! file_exists( $path ) ) {
			wp_die( esc_html__( 'Fichier introuvable sur le serveur.', 'dira-booking-files' ), '', array( 'response' => 404 ) );
		}

		global $wpdb;
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . Dira_BF_Database::table( 'downloads' ) . ' SET download_count = download_count + 1 WHERE id = %d', $download->id ) );
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . Dira_BF_Database::table( 'files' ) . ' SET download_count = download_count + 1 WHERE id = %d', $file->id ) );

		if ( class_exists( 'Dira_BF_Notifications' ) ) {
			Dira_BF_Notifications::log( 'file_downloaded', wp_get_current_user()->user_email ?: self::client_ip(), $file->title, $file->id );
		}

		nocache_headers();
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $file->title . '.' . pathinfo( $path, PATHINFO_EXTENSION ) ) . '"' );
		header( 'Content-Length: ' . filesize( $path ) );
		readfile( $path ); // phpcs:ignore
		exit;
	}

	private static function client_ip() {
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return '';
	}
}
