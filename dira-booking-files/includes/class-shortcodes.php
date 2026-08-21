<?php
/**
 * Shortcodes publics de l'extension.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Shortcodes {

	public static function init() {
		add_shortcode( 'dira_booking', array( __CLASS__, 'booking' ) );
		add_shortcode( 'dira_booking_form', array( __CLASS__, 'booking' ) );
		add_shortcode( 'dira_files', array( __CLASS__, 'files' ) );
		add_shortcode( 'dira_file', array( __CLASS__, 'file_single' ) );
		add_shortcode( 'dira_my_bookings', array( __CLASS__, 'my_bookings' ) );
		add_shortcode( 'dira_my_downloads', array( __CLASS__, 'my_downloads' ) );
	}

	private static function enqueue_public_assets() {
		wp_enqueue_style( 'dira-bf-public', DIRA_BF_URL . 'assets/css/public.css', array(), DIRA_BF_VERSION );

		$accent = sanitize_hex_color( Dira_BF_Settings::get( 'accent_color', '#4f46e5' ) ) ?: '#4f46e5';
		wp_add_inline_style( 'dira-bf-public', ':root{--dira-bf-accent:' . esc_attr( $accent ) . ';}' );

		$shared = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( Dira_BF_Ajax::NONCE_ACTION ),
			'accent'  => Dira_BF_Settings::get( 'accent_color', '#4f46e5' ),
			'i18n'    => array(
				'loading'    => __( 'Chargement…', 'dira-booking-files' ),
				'noSlots'    => __( 'Aucun créneau disponible à cette date.', 'dira-booking-files' ),
				'error'      => __( 'Une erreur est survenue, merci de réessayer.', 'dira-booking-files' ),
				'confirm'    => __( 'Confirmer le rendez-vous', 'dira-booking-files' ),
				'processing' => __( 'Envoi en cours…', 'dira-booking-files' ),
			),
		);

		wp_enqueue_script( 'dira-bf-booking', DIRA_BF_URL . 'assets/js/booking.js', array(), DIRA_BF_VERSION, true );
		wp_localize_script( 'dira-bf-booking', 'DiraBF', $shared );

		wp_enqueue_script( 'dira-bf-files', DIRA_BF_URL . 'assets/js/files.js', array(), DIRA_BF_VERSION, true );
		wp_localize_script( 'dira-bf-files', 'DiraBF', $shared );
	}

	public static function booking( $atts ) {
		self::enqueue_public_assets();

		$atts = shortcode_atts( array( 'service' => '' ), $atts, 'dira_booking' );

		$services = Dira_BF_Services::get_all( true );

		ob_start();
		include DIRA_BF_DIR . 'public/views/booking-form.php';
		return ob_get_clean();
	}

	public static function files( $atts ) {
		self::enqueue_public_assets();

		// URL du type /oeuvres/?dira_file=mon-document : affiche la fiche au lieu de la grille.
		if ( ! empty( $_GET['dira_file'] ) ) {
			return self::file_single( array( 'slug' => sanitize_title( wp_unslash( $_GET['dira_file'] ) ) ) );
		}

		$atts = shortcode_atts(
			array(
				'category' => '',
				'type'     => '', // free | paid | ''.
				'limit'    => 24,
			),
			$atts,
			'dira_files'
		);

		$categories = Dira_BF_Files::get_categories();

		$query_args = array( 'limit' => (int) $atts['limit'] );
		if ( 'free' === $atts['type'] ) {
			$query_args['is_free'] = 1;
		} elseif ( 'paid' === $atts['type'] ) {
			$query_args['is_free'] = 0;
		}
		if ( $atts['category'] ) {
			$query_args['category_id'] = (int) $atts['category'];
		}

		$files = Dira_BF_Files::query( $query_args );

		ob_start();
		include DIRA_BF_DIR . 'public/views/files-page.php';
		return ob_get_clean();
	}

	public static function file_single( $atts ) {
		self::enqueue_public_assets();

		$atts = shortcode_atts( array( 'id' => 0, 'slug' => '' ), $atts, 'dira_file' );

		$file = $atts['slug'] ? Dira_BF_Files::get_by_slug( $atts['slug'] ) : Dira_BF_Files::get( (int) $atts['id'] );
		if ( ! $file || 'publish' !== $file->status ) {
			return '';
		}

		ob_start();
		include DIRA_BF_DIR . 'public/views/file-single.php';
		return ob_get_clean();
	}

	public static function my_bookings( $atts ) {
		self::enqueue_public_assets();
		ob_start();
		include DIRA_BF_DIR . 'public/views/my-bookings.php';
		return ob_get_clean();
	}

	public static function my_downloads( $atts ) {
		self::enqueue_public_assets();
		ob_start();
		include DIRA_BF_DIR . 'public/views/my-downloads.php';
		return ob_get_clean();
	}
}
