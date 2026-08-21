<?php
/**
 * Emails de notification (client + administrateur) et journal des envois.
 *
 * Architecture prête pour ajouter WhatsApp / SMS / Telegram plus tard
 * via le hook "dira_bf_notify" (voir méthode notify()).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Notifications {

	const TEMPLATES_OPTION = 'dira_bf_email_templates';

	public static function default_templates() {
		return array(
			'booking_pending_customer' => array(
				'subject' => __( 'Votre demande de rendez-vous a bien été reçue', 'dira-booking-files' ),
				'body'    => __( "Bonjour {customer_name},\n\nVotre demande de rendez-vous a bien été enregistrée et est en attente de confirmation.\n\nService : {service_name}\nDate : {booking_date}\nHeure : {booking_time}\nRéférence : {booking_ref}\n\nNous revenons vers vous rapidement.\n\n{company_name}", 'dira-booking-files' ),
			),
			'booking_confirmed_customer' => array(
				'subject' => __( 'Votre rendez-vous est confirmé', 'dira-booking-files' ),
				'body'    => __( "Bonjour {customer_name},\n\nVotre rendez-vous est confirmé.\n\nService : {service_name}\nDate : {booking_date}\nHeure : {booking_time}\nRéférence : {booking_ref}\n\nÀ bientôt,\n{company_name}", 'dira-booking-files' ),
			),
			'booking_cancelled_customer' => array(
				'subject' => __( 'Votre rendez-vous a été annulé', 'dira-booking-files' ),
				'body'    => __( "Bonjour {customer_name},\n\nVotre rendez-vous du {booking_date} à {booking_time} (réf. {booking_ref}) a été annulé.\n\n{company_name}", 'dira-booking-files' ),
			),
			'booking_refused_customer' => array(
				'subject' => __( 'Votre demande de rendez-vous n\'a pas pu être acceptée', 'dira-booking-files' ),
				'body'    => __( "Bonjour {customer_name},\n\nNous ne sommes malheureusement pas en mesure d'honorer votre demande de rendez-vous du {booking_date} à {booking_time} (réf. {booking_ref}). N'hésitez pas à choisir un autre créneau.\n\n{company_name}", 'dira-booking-files' ),
			),
			'booking_completed_customer' => array(
				'subject' => __( 'Merci pour votre rendez-vous', 'dira-booking-files' ),
				'body'    => __( "Bonjour {customer_name},\n\nVotre rendez-vous du {booking_date} (réf. {booking_ref}) est marqué comme terminé. Merci de votre confiance.\n\n{company_name}", 'dira-booking-files' ),
			),
			'booking_reminder_customer' => array(
				'subject' => __( 'Rappel de votre rendez-vous', 'dira-booking-files' ),
				'body'    => __( "Bonjour {customer_name},\n\nPetit rappel : votre rendez-vous « {service_name} » est prévu le {booking_date} à {booking_time}.\nRéférence : {booking_ref}\n\n{company_name}", 'dira-booking-files' ),
			),
			'booking_new_admin' => array(
				'subject' => __( 'Nouveau rendez-vous : {booking_ref}', 'dira-booking-files' ),
				'body'    => __( "Nouveau rendez-vous reçu.\n\nService : {service_name}\nDate : {booking_date}\nHeure : {booking_time}\nClient : {customer_name}\nTéléphone : {customer_phone}\nEmail : {customer_email}\nRéférence : {booking_ref}", 'dira-booking-files' ),
			),
			'order_paid_customer' => array(
				'subject' => __( 'Votre achat est prêt à être téléchargé', 'dira-booking-files' ),
				'body'    => __( "Bonjour {customer_name},\n\nMerci pour votre commande {order_ref}.\nVous pouvez télécharger votre fichier ici :\n{download_url}\n\n{company_name}", 'dira-booking-files' ),
			),
		);
	}

	public static function get_template( $key ) {
		$templates = get_option( self::TEMPLATES_OPTION, array() );
		$defaults  = self::default_templates();
		if ( isset( $templates[ $key ] ) ) {
			return wp_parse_args( $templates[ $key ], $defaults[ $key ] ?? array() );
		}
		return $defaults[ $key ] ?? array( 'subject' => '', 'body' => '' );
	}

	private static function render( $text, array $vars ) {
		$search  = array();
		$replace = array();
		foreach ( $vars as $key => $value ) {
			$search[]  = '{' . $key . '}';
			$replace[] = (string) $value;
		}
		return str_replace( $search, $replace, $text );
	}

	private static function booking_vars( $booking ) {
		$service  = Dira_BF_Services::get( $booking->service_id );
		$customer = Dira_BF_Customers::get( $booking->customer_id );

		return array(
			'customer_name'  => Dira_BF_Customers::full_name( $customer ),
			'customer_email' => $customer->email ?? '',
			'customer_phone' => $customer->phone ?? '',
			'service_name'   => $service->name ?? '',
			'booking_date'   => date_i18n( get_option( 'date_format' ), strtotime( $booking->booking_date ) ),
			'booking_time'   => substr( $booking->start_time, 0, 5 ),
			'booking_ref'    => $booking->booking_ref,
			'company_name'   => Dira_BF_Settings::get( 'company_name' ),
		);
	}

	public static function send_booking_created( $booking ) {
		$vars     = self::booking_vars( $booking );
		$customer = Dira_BF_Customers::get( $booking->customer_id );

		$key = 'confirmed' === $booking->status ? 'booking_confirmed_customer' : 'booking_pending_customer';
		self::notify( $key, $customer->email ?? '', $vars, $booking->id );

		$admin_email = Dira_BF_Settings::get( 'notify_admin_email' );
		self::notify( 'booking_new_admin', $admin_email, $vars, $booking->id );
	}

	public static function send_status_changed( $booking, $old_status, $new_status ) {
		$map = array(
			'confirmed' => 'booking_confirmed_customer',
			'cancelled' => 'booking_cancelled_customer',
			'refused'   => 'booking_refused_customer',
			'completed' => 'booking_completed_customer',
		);

		if ( ! isset( $map[ $new_status ] ) ) {
			return;
		}

		$customer = Dira_BF_Customers::get( $booking->customer_id );
		self::notify( $map[ $new_status ], $customer->email ?? '', self::booking_vars( $booking ), $booking->id );
	}

	public static function send_reminder( $booking ) {
		$customer = Dira_BF_Customers::get( $booking->customer_id );
		self::notify( 'booking_reminder_customer', $customer->email ?? '', self::booking_vars( $booking ), $booking->id );
	}

	public static function send_order_paid( $order, $token ) {
		$file     = Dira_BF_Files::get( $order->file_id );
		$customer = Dira_BF_Customers::get( $order->customer_id );

		$vars = array(
			'customer_name' => Dira_BF_Customers::full_name( $customer ),
			'order_ref'     => $order->order_ref,
			'download_url'  => add_query_arg( 'dira_download', $token, home_url( '/' ) ),
			'company_name'  => Dira_BF_Settings::get( 'company_name' ),
		);

		self::notify( 'order_paid_customer', $customer->email ?? '', $vars, $order->id );
	}

	/**
	 * Point d'entrée unique d'envoi, avec hook pour brancher d'autres canaux (WhatsApp, SMS, Telegram...).
	 */
	private static function notify( $template_key, $to, array $vars, $context_id = null ) {
		if ( ! $to || ! is_email( $to ) ) {
			return;
		}

		$template = self::get_template( $template_key );
		$subject  = self::render( $template['subject'], $vars );
		$body     = self::render( $template['body'], $vars );

		$sent = wp_mail( $to, $subject, $body );

		self::log( $template_key, $to, $subject, $context_id, $sent ? 'sent' : 'failed' );

		/**
		 * Permet à d'autres intégrations (WhatsApp, SMS, Telegram, agent IA...) de réagir à la même notification.
		 */
		do_action( 'dira_bf_notify', $template_key, $to, $subject, $body, $vars, $context_id );
	}

	public static function log( $type, $recipient, $subject, $context_id = null, $status = 'sent' ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'notifications' );

		$wpdb->insert(
			$table,
			array(
				'type'       => sanitize_key( $type ),
				'recipient'  => sanitize_text_field( $recipient ),
				'subject'    => sanitize_text_field( $subject ),
				'status'     => $status,
				'context_id' => $context_id ? (int) $context_id : null,
				'created_at' => current_time( 'mysql' ),
			)
		);
	}

	public static function get_log( $limit = 50 ) {
		global $wpdb;
		$table = Dira_BF_Database::table( 'notifications' );
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table ORDER BY id DESC LIMIT %d", (int) $limit ) );
	}
}
