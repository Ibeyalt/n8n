<?php
/**
 * Réglages de l'extension, stockés dans une seule option WordPress.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Settings {

	const OPTION_KEY = 'dira_bf_settings';

	/**
	 * Valeurs par défaut.
	 */
	public static function defaults() {
		return array(
			// Général.
			'company_name'          => get_bloginfo( 'name' ),
			'company_email'         => get_bloginfo( 'admin_email' ),
			'company_phone'         => '',
			'company_whatsapp'      => '',
			'company_address'       => '',
			'currency'              => 'XOF',
			'accent_color'          => '#4f46e5',

			// Rendez-vous.
			'default_duration'      => 30,
			'min_notice_hours'      => 2,
			'max_notice_days'       => 60,
			'auto_confirm'          => 0,
			'allow_client_cancel'   => 1,

			// Notifications.
			'notify_admin_email'    => get_bloginfo( 'admin_email' ),
			'reminders_enabled'     => 1,
			'reminder_hours_before' => array( 24, 2 ),

			// Fichiers.
			'max_upload_mb'         => 50,
			'allowed_extensions'    => array( 'pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'mp4' ),
			'default_max_downloads' => 5,
			'default_link_days'     => 7,

			// Paiement.
			'payment_gateway'       => 'manual',

			// Divers.
			'keep_data_on_uninstall' => 1,
		);
	}

	/**
	 * Retourne tous les réglages fusionnés avec les valeurs par défaut.
	 */
	public static function get_all() {
		$stored = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return wp_parse_args( $stored, self::defaults() );
	}

	/**
	 * Retourne un réglage précis.
	 */
	public static function get( $key, $default = null ) {
		$all = self::get_all();
		if ( array_key_exists( $key, $all ) ) {
			return $all[ $key ];
		}
		return $default;
	}

	/**
	 * Met à jour plusieurs réglages en une fois.
	 */
	public static function update_many( array $values ) {
		$all = self::get_all();
		foreach ( $values as $key => $value ) {
			$all[ $key ] = $value;
		}
		update_option( self::OPTION_KEY, $all );
		return $all;
	}

	/**
	 * Installe les valeurs par défaut si aucun réglage n'existe encore.
	 */
	public static function maybe_set_defaults() {
		if ( false === get_option( self::OPTION_KEY, false ) ) {
			update_option( self::OPTION_KEY, self::defaults() );
		}
	}

	/**
	 * Formate un montant avec la devise configurée.
	 */
	public static function format_price( $amount ) {
		$currency = self::get( 'currency', 'XOF' );
		$amount   = number_format( (float) $amount, in_array( $currency, array( 'XOF', 'XAF' ), true ) ? 0 : 2, ',', ' ' );
		return $amount . ' ' . $currency;
	}
}
