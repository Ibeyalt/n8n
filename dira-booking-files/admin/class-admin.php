<?php
/**
 * Interface d'administration : menu, écrans et traitement des formulaires.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Admin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'render_flash_notice' ) );

		$handlers = array(
			'dira_bf_save_service'     => 'handle_save_service',
			'dira_bf_delete_service'   => 'handle_delete_service',
			'dira_bf_update_booking'   => 'handle_update_booking',
			'dira_bf_save_availability'=> 'handle_save_availability',
			'dira_bf_add_exception'    => 'handle_add_exception',
			'dira_bf_delete_availability_entry' => 'handle_delete_availability_entry',
			'dira_bf_save_category'    => 'handle_save_category',
			'dira_bf_delete_category'  => 'handle_delete_category',
			'dira_bf_save_file'        => 'handle_save_file',
			'dira_bf_delete_file'      => 'handle_delete_file',
			'dira_bf_update_order'     => 'handle_update_order',
			'dira_bf_save_settings'    => 'handle_save_settings',
			'dira_bf_delete_customer'  => 'handle_delete_customer',
		);

		foreach ( $handlers as $action => $method ) {
			add_action( "admin_post_$action", array( $this, $method ) );
		}
	}

	public function register_menu() {
		$cap = 'manage_options';

		add_menu_page( __( 'Dira Booking', 'dira-booking-files' ), __( 'Dira Booking', 'dira-booking-files' ), $cap, 'dira-booking', array( $this, 'view_dashboard' ), 'dashicons-calendar-alt', 27 );

		add_submenu_page( 'dira-booking', __( 'Tableau de bord', 'dira-booking-files' ), __( 'Tableau de bord', 'dira-booking-files' ), $cap, 'dira-booking', array( $this, 'view_dashboard' ) );
		add_submenu_page( 'dira-booking', __( 'Rendez-vous', 'dira-booking-files' ), __( 'Rendez-vous', 'dira-booking-files' ), $cap, 'dira-booking-bookings', array( $this, 'view_bookings' ) );
		add_submenu_page( 'dira-booking', __( 'Calendrier', 'dira-booking-files' ), __( 'Calendrier', 'dira-booking-files' ), $cap, 'dira-booking-calendar', array( $this, 'view_calendar' ) );
		add_submenu_page( 'dira-booking', __( 'Clients', 'dira-booking-files' ), __( 'Clients', 'dira-booking-files' ), $cap, 'dira-booking-customers', array( $this, 'view_customers' ) );
		add_submenu_page( 'dira-booking', __( 'Services', 'dira-booking-files' ), __( 'Services', 'dira-booking-files' ), $cap, 'dira-booking-services', array( $this, 'view_services' ) );
		add_submenu_page( 'dira-booking', __( 'Disponibilités', 'dira-booking-files' ), __( 'Disponibilités', 'dira-booking-files' ), $cap, 'dira-booking-availability', array( $this, 'view_availability' ) );
		add_submenu_page( 'dira-booking', __( 'Fichiers', 'dira-booking-files' ), __( 'Fichiers', 'dira-booking-files' ), $cap, 'dira-booking-files-admin', array( $this, 'view_files' ) );
		add_submenu_page( 'dira-booking', __( 'Catégories', 'dira-booking-files' ), __( 'Catégories', 'dira-booking-files' ), $cap, 'dira-booking-categories', array( $this, 'view_categories' ) );
		add_submenu_page( 'dira-booking', __( 'Commandes', 'dira-booking-files' ), __( 'Commandes', 'dira-booking-files' ), $cap, 'dira-booking-orders', array( $this, 'view_orders' ) );
		add_submenu_page( 'dira-booking', __( 'Téléchargements', 'dira-booking-files' ), __( 'Téléchargements', 'dira-booking-files' ), $cap, 'dira-booking-downloads', array( $this, 'view_downloads' ) );
		add_submenu_page( 'dira-booking', __( 'Notifications', 'dira-booking-files' ), __( 'Notifications', 'dira-booking-files' ), $cap, 'dira-booking-notifications', array( $this, 'view_notifications' ) );
		add_submenu_page( 'dira-booking', __( 'Paramètres', 'dira-booking-files' ), __( 'Paramètres', 'dira-booking-files' ), $cap, 'dira-booking-settings', array( $this, 'view_settings' ) );
	}

	public function enqueue_assets( $hook ) {
		if ( strpos( (string) ( $_GET['page'] ?? '' ), 'dira-booking' ) !== 0 ) {
			return;
		}
		wp_enqueue_style( 'dira-bf-admin', DIRA_BF_URL . 'assets/css/admin.css', array(), DIRA_BF_VERSION );
		wp_enqueue_script( 'dira-bf-admin', DIRA_BF_URL . 'assets/js/admin.js', array(), DIRA_BF_VERSION, true );
	}

	/* -----------------------------------------------------------
	 * Vues
	 * --------------------------------------------------------- */

	public function view_dashboard() { $this->render_view( 'dashboard' ); }
	public function view_bookings() { $this->render_view( 'bookings' ); }
	public function view_calendar() { $this->render_view( 'calendar' ); }
	public function view_customers() { $this->render_view( 'customers' ); }
	public function view_services() { $this->render_view( 'services' ); }
	public function view_availability() { $this->render_view( 'availability' ); }
	public function view_files() { $this->render_view( 'files' ); }
	public function view_categories() { $this->render_view( 'categories' ); }
	public function view_orders() { $this->render_view( 'orders' ); }
	public function view_downloads() { $this->render_view( 'downloads' ); }
	public function view_notifications() { $this->render_view( 'notifications' ); }
	public function view_settings() { $this->render_view( 'settings' ); }

	private function render_view( $name ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Accès refusé.', 'dira-booking-files' ) );
		}
		$path = DIRA_BF_DIR . 'admin/views/' . $name . '.php';
		if ( file_exists( $path ) ) {
			include $path;
		}
	}

	/* -----------------------------------------------------------
	 * Messages flash (redirection après enregistrement)
	 * --------------------------------------------------------- */

	private function redirect_with_notice( $page, $type, $message ) {
		set_transient( 'dira_bf_notice_' . get_current_user_id(), array( 'type' => $type, 'message' => $message ), 60 );
		wp_safe_redirect( admin_url( 'admin.php?page=' . $page ) );
		exit;
	}

	public function render_flash_notice() {
		$key    = 'dira_bf_notice_' . get_current_user_id();
		$notice = get_transient( $key );
		if ( ! $notice ) {
			return;
		}
		delete_transient( $key );
		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $notice['type'] ),
			esc_html( $notice['message'] )
		);
	}

	/* -----------------------------------------------------------
	 * Handlers : Services
	 * --------------------------------------------------------- */

	public function handle_save_service() {
		check_admin_referer( 'dira_bf_save_service' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();

		$id   = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0;
		$data = array(
			'name'             => wp_unslash( $_POST['name'] ?? '' ),
			'description'      => wp_unslash( $_POST['description'] ?? '' ),
			'duration_minutes' => absint( $_POST['duration_minutes'] ?? 30 ),
			'price'            => floatval( $_POST['price'] ?? 0 ),
			'status'           => sanitize_key( $_POST['status'] ?? 'active' ),
			'color'            => sanitize_text_field( $_POST['color'] ?? '#4f46e5' ),
			'min_notice_hours' => absint( $_POST['min_notice_hours'] ?? 2 ),
			'max_notice_days'  => absint( $_POST['max_notice_days'] ?? 60 ),
			'max_people'       => absint( $_POST['max_people'] ?? 1 ),
			'buffer_minutes'   => absint( $_POST['buffer_minutes'] ?? 0 ),
		);

		if ( $id ) {
			Dira_BF_Services::update( $id, $data );
		} else {
			Dira_BF_Services::create( $data );
		}

		$this->redirect_with_notice( 'dira-booking-services', 'success', __( 'Service enregistré.', 'dira-booking-files' ) );
	}

	public function handle_delete_service() {
		check_admin_referer( 'dira_bf_delete_service' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();
		Dira_BF_Services::delete( absint( $_GET['id'] ?? 0 ) );
		$this->redirect_with_notice( 'dira-booking-services', 'success', __( 'Service supprimé.', 'dira-booking-files' ) );
	}

	/* -----------------------------------------------------------
	 * Handlers : Rendez-vous
	 * --------------------------------------------------------- */

	public function handle_update_booking() {
		check_admin_referer( 'dira_bf_update_booking' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();

		$id     = absint( $_POST['id'] ?? 0 );
		$status = sanitize_key( $_POST['status'] ?? '' );

		$result = Dira_BF_Bookings::update_status( $id, $status );
		$type   = is_wp_error( $result ) ? 'error' : 'success';
		$msg    = is_wp_error( $result ) ? $result->get_error_message() : __( 'Rendez-vous mis à jour.', 'dira-booking-files' );

		$this->redirect_with_notice( 'dira-booking-bookings', $type, $msg );
	}

	/* -----------------------------------------------------------
	 * Handlers : Disponibilités
	 * --------------------------------------------------------- */

	public function handle_save_availability() {
		check_admin_referer( 'dira_bf_save_availability' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();

		$days = array();
		for ( $day = 0; $day <= 6; $day++ ) {
			$starts = $_POST[ "start_$day" ] ?? array();
			$ends   = $_POST[ "end_$day" ] ?? array();
			$ranges = array();
			foreach ( (array) $starts as $i => $start ) {
				$end = $ends[ $i ] ?? '';
				if ( $start && $end ) {
					$ranges[] = array( 'start' => sanitize_text_field( $start ), 'end' => sanitize_text_field( $end ) );
				}
			}
			$days[ $day ] = $ranges;
		}

		Dira_BF_Availability::set_weekly_hours( $days );
		$this->redirect_with_notice( 'dira-booking-availability', 'success', __( 'Horaires mis à jour.', 'dira-booking-files' ) );
	}

	public function handle_add_exception() {
		check_admin_referer( 'dira_bf_add_exception' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();

		$type = sanitize_key( $_POST['entry_type'] ?? 'blocked' );
		$date = sanitize_text_field( $_POST['date'] ?? '' );
		$start = sanitize_text_field( $_POST['start_time'] ?? '' );
		$end   = sanitize_text_field( $_POST['end_time'] ?? '' );
		$reason = sanitize_text_field( $_POST['reason'] ?? '' );

		if ( 'closed' === $type ) {
			Dira_BF_Availability::add_exception( $date, true, null, null, $reason );
		} elseif ( 'open' === $type ) {
			Dira_BF_Availability::add_exception( $date, false, $start, $end, $reason );
		} else {
			Dira_BF_Availability::add_blocked_slot( $date, $start, $end, $reason );
		}

		$this->redirect_with_notice( 'dira-booking-availability', 'success', __( 'Entrée ajoutée.', 'dira-booking-files' ) );
	}

	public function handle_delete_availability_entry() {
		check_admin_referer( 'dira_bf_delete_availability_entry' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();
		Dira_BF_Availability::delete_entry( absint( $_GET['id'] ?? 0 ) );
		$this->redirect_with_notice( 'dira-booking-availability', 'success', __( 'Entrée supprimée.', 'dira-booking-files' ) );
	}

	/* -----------------------------------------------------------
	 * Handlers : Fichiers & catégories
	 * --------------------------------------------------------- */

	public function handle_save_category() {
		check_admin_referer( 'dira_bf_save_category' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();
		$name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		if ( $name ) {
			Dira_BF_Files::create_category( $name );
		}
		$this->redirect_with_notice( 'dira-booking-categories', 'success', __( 'Catégorie enregistrée.', 'dira-booking-files' ) );
	}

	public function handle_delete_category() {
		check_admin_referer( 'dira_bf_delete_category' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();
		Dira_BF_Files::delete_category( absint( $_GET['id'] ?? 0 ) );
		$this->redirect_with_notice( 'dira-booking-categories', 'success', __( 'Catégorie supprimée.', 'dira-booking-files' ) );
	}

	public function handle_save_file() {
		check_admin_referer( 'dira_bf_save_file' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();

		$id   = absint( $_POST['id'] ?? 0 );
		$data = array(
			'title'            => wp_unslash( $_POST['title'] ?? '' ),
			'description'      => wp_unslash( $_POST['description'] ?? '' ),
			'category_id'      => absint( $_POST['category_id'] ?? 0 ),
			'author'           => sanitize_text_field( $_POST['author'] ?? '' ),
			'price'            => floatval( $_POST['price'] ?? 0 ),
			'status'           => sanitize_key( $_POST['status'] ?? 'draft' ),
			'max_downloads'    => absint( $_POST['max_downloads'] ?? 0 ),
			'link_expiry_days' => absint( $_POST['link_expiry_days'] ?? 0 ),
		);

		if ( ! empty( $_FILES['file_upload']['name'] ) ) {
			$upload = Dira_BF_Files::handle_upload( $_FILES['file_upload'] );
			if ( is_wp_error( $upload ) ) {
				$this->redirect_with_notice( 'dira-booking-files-admin', 'error', $upload->get_error_message() );
			}
			$data['file_path'] = $upload['path'];
			$data['file_size'] = $upload['size'];
			$data['file_type'] = $upload['type'];
		}

		if ( $id ) {
			Dira_BF_Files::update( $id, $data );
		} else {
			if ( empty( $data['file_path'] ) ) {
				$this->redirect_with_notice( 'dira-booking-files-admin', 'error', __( 'Merci de sélectionner un fichier.', 'dira-booking-files' ) );
			}
			Dira_BF_Files::create( $data );
		}

		$this->redirect_with_notice( 'dira-booking-files-admin', 'success', __( 'Fichier enregistré.', 'dira-booking-files' ) );
	}

	public function handle_delete_file() {
		check_admin_referer( 'dira_bf_delete_file' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();
		Dira_BF_Files::delete( absint( $_GET['id'] ?? 0 ) );
		$this->redirect_with_notice( 'dira-booking-files-admin', 'success', __( 'Fichier supprimé.', 'dira-booking-files' ) );
	}

	/* -----------------------------------------------------------
	 * Handlers : Commandes
	 * --------------------------------------------------------- */

	public function handle_update_order() {
		check_admin_referer( 'dira_bf_update_order' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();

		$id     = absint( $_POST['id'] ?? 0 );
		$status = sanitize_key( $_POST['status'] ?? '' );

		$result = Dira_BF_Orders::mark_status( $id, $status );
		$type   = is_wp_error( $result ) ? 'error' : 'success';
		$msg    = is_wp_error( $result ) ? $result->get_error_message() : __( 'Commande mise à jour.', 'dira-booking-files' );

		$this->redirect_with_notice( 'dira-booking-orders', $type, $msg );
	}

	/* -----------------------------------------------------------
	 * Handlers : Clients & réglages
	 * --------------------------------------------------------- */

	public function handle_delete_customer() {
		check_admin_referer( 'dira_bf_delete_customer' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();
		Dira_BF_Customers::delete( absint( $_GET['id'] ?? 0 ) );
		$this->redirect_with_notice( 'dira-booking-customers', 'success', __( 'Client supprimé.', 'dira-booking-files' ) );
	}

	public function handle_save_settings() {
		check_admin_referer( 'dira_bf_save_settings' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die();

		$values = array(
			'company_name'           => sanitize_text_field( $_POST['company_name'] ?? '' ),
			'company_email'          => sanitize_email( $_POST['company_email'] ?? '' ),
			'company_phone'          => sanitize_text_field( $_POST['company_phone'] ?? '' ),
			'company_whatsapp'       => sanitize_text_field( $_POST['company_whatsapp'] ?? '' ),
			'company_address'        => sanitize_textarea_field( $_POST['company_address'] ?? '' ),
			'currency'               => sanitize_text_field( $_POST['currency'] ?? 'XOF' ),
			'accent_color'           => sanitize_hex_color( $_POST['accent_color'] ?? '' ) ?: '#4f46e5',
			'default_duration'       => absint( $_POST['default_duration'] ?? 30 ),
			'min_notice_hours'       => absint( $_POST['min_notice_hours'] ?? 2 ),
			'max_notice_days'        => absint( $_POST['max_notice_days'] ?? 60 ),
			'auto_confirm'           => empty( $_POST['auto_confirm'] ) ? 0 : 1,
			'allow_client_cancel'    => empty( $_POST['allow_client_cancel'] ) ? 0 : 1,
			'notify_admin_email'     => sanitize_email( $_POST['notify_admin_email'] ?? '' ),
			'reminders_enabled'      => empty( $_POST['reminders_enabled'] ) ? 0 : 1,
			'max_upload_mb'          => absint( $_POST['max_upload_mb'] ?? 50 ),
			'default_max_downloads'  => absint( $_POST['default_max_downloads'] ?? 5 ),
			'default_link_days'      => absint( $_POST['default_link_days'] ?? 7 ),
			'payment_gateway'        => sanitize_key( $_POST['payment_gateway'] ?? 'manual' ),
			'keep_data_on_uninstall' => empty( $_POST['keep_data_on_uninstall'] ) ? 0 : 1,
		);

		if ( ! empty( $_POST['allowed_extensions'] ) ) {
			$extensions = array_map( 'sanitize_text_field', explode( ',', wp_unslash( $_POST['allowed_extensions'] ) ) );
			$values['allowed_extensions'] = array_filter( array_map( 'trim', $extensions ) );
		}

		Dira_BF_Settings::update_many( $values );
		$this->redirect_with_notice( 'dira-booking-settings', 'success', __( 'Réglages enregistrés.', 'dira-booking-files' ) );
	}
}
