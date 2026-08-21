<?php
/**
 * Bootstrap principal de l'extension (singleton).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Plugin {

	/** @var Dira_BF_Plugin */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function init() {
		load_plugin_textdomain( 'dira-booking-files', false, dirname( DIRA_BF_BASENAME ) . '/languages' );

		Dira_BF_Cron::init();
		Dira_BF_Ajax::init();
		Dira_BF_Shortcodes::init();

		add_filter( 'query_vars', array( __CLASS__, 'register_query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_handle_download' ) );

		if ( is_admin() && class_exists( 'Dira_BF_Admin' ) ) {
			Dira_BF_Admin::instance()->init();
		}
	}

	public static function register_query_vars( $vars ) {
		$vars[] = 'dira_download';
		return $vars;
	}

	/**
	 * Intercepte ?dira_download=TOKEN sur n'importe quelle page pour diffuser le fichier
	 * de façon sécurisée (jamais d'URL publique directe vers le fichier réel).
	 */
	public static function maybe_handle_download() {
		$token = get_query_var( 'dira_download' );
		if ( ! $token && ! empty( $_GET['dira_download'] ) ) {
			$token = sanitize_text_field( wp_unslash( $_GET['dira_download'] ) );
		}
		if ( $token ) {
			Dira_BF_Files::stream_download( $token );
		}
	}

	/* -----------------------------------------------------------
	 * Activation / désactivation
	 * --------------------------------------------------------- */

	public static function activate() {
		Dira_BF_Database::create_tables();
		Dira_BF_Files::ensure_protected_dir();
		Dira_BF_Settings::maybe_set_defaults();
		self::maybe_seed_default_availability();
		self::maybe_create_pages();

		Dira_BF_Cron::schedule();
		flush_rewrite_rules();
	}

	public static function deactivate() {
		Dira_BF_Cron::unschedule();
		flush_rewrite_rules();
	}

	private static function maybe_seed_default_availability() {
		$existing = Dira_BF_Availability::get_weekly_hours();
		$has_hours = false;
		foreach ( $existing as $day => $ranges ) {
			if ( ! empty( $ranges ) ) {
				$has_hours = true;
				break;
			}
		}

		if ( $has_hours ) {
			return;
		}

		// Lundi (1) à vendredi (5), 08:00 - 17:00 par défaut.
		$defaults = array();
		foreach ( array( 1, 2, 3, 4, 5 ) as $day ) {
			$defaults[ $day ] = array( array( 'start' => '08:00', 'end' => '17:00' ) );
		}
		Dira_BF_Availability::set_weekly_hours( $defaults );
	}

	private static function maybe_create_pages() {
		$pages = get_option( 'dira_bf_pages', array() );
		if ( ! is_array( $pages ) ) {
			$pages = array();
		}

		$to_create = array(
			'booking'   => array( 'title' => __( 'Rendez-vous', 'dira-booking-files' ), 'content' => '[dira_booking]' ),
			'files'     => array( 'title' => __( 'Œuvres', 'dira-booking-files' ), 'content' => '[dira_files]' ),
			'account'   => array(
				'title'   => __( 'Mon compte', 'dira-booking-files' ),
				'content' => __( "Retrouvez ici l'accès à vos rendez-vous et à vos téléchargements.", 'dira-booking-files' )
					. "\n\n[dira_my_bookings]\n\n[dira_my_downloads]",
			),
			'my_bookings'  => array( 'title' => __( 'Mes rendez-vous', 'dira-booking-files' ), 'content' => '[dira_my_bookings]' ),
			'my_downloads' => array( 'title' => __( 'Mes téléchargements', 'dira-booking-files' ), 'content' => '[dira_my_downloads]' ),
		);

		foreach ( $to_create as $key => $page_data ) {
			if ( ! empty( $pages[ $key ] ) && get_post( $pages[ $key ] ) ) {
				continue; // Déjà créée.
			}

			$found_id = self::find_page_by_title( $page_data['title'] );
			if ( $found_id ) {
				$pages[ $key ] = $found_id;
				continue;
			}

			$page_id = wp_insert_post(
				array(
					'post_title'   => $page_data['title'],
					'post_content' => $page_data['content'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
				)
			);

			if ( $page_id && ! is_wp_error( $page_id ) ) {
				$pages[ $key ] = $page_id;
			}
		}

		update_option( 'dira_bf_pages', $pages );
	}

	private static function find_page_by_title( $title ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'page',
				'title'                  => $title,
				'posts_per_page'         => 1,
				'post_status'            => array( 'publish', 'draft', 'private' ),
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		return $query->have_posts() ? $query->posts[0]->ID : 0;
	}
}
