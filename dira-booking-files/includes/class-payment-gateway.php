<?php
/**
 * Abstraction des moyens de paiement, pour ne dépendre d'aucune passerelle en particulier.
 *
 * Payment Gateway
 *     ├── Manual (par défaut, hors-ligne)
 *     ├── WooCommerce (si WooCommerce est actif)
 *     ├── Mobile Money (à implémenter ultérieurement)
 *     └── Autres passerelles futures
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Dira_BF_Payment_Gateway {

	/** @return string Identifiant unique, ex. "manual". */
	abstract public function get_id();

	/** @return string Nom affiché dans les réglages. */
	abstract public function get_title();

	/**
	 * Démarre le paiement d'une commande.
	 *
	 * @param object $order Ligne wp_dira_file_orders.
	 * @return array { 'type' => 'redirect'|'message', 'url' => string, 'message' => string }
	 */
	abstract public function process_order( $order );

	public function is_available() {
		return true;
	}
}

/**
 * Registre central des passerelles de paiement disponibles.
 */
class Dira_BF_Gateway_Manager {

	/** @var Dira_BF_Payment_Gateway[] */
	private static $gateways = array();

	public static function register( Dira_BF_Payment_Gateway $gateway ) {
		self::$gateways[ $gateway->get_id() ] = $gateway;
	}

	/** @return Dira_BF_Payment_Gateway[] */
	public static function get_available() {
		return array_filter( self::$gateways, function ( $gateway ) {
			return $gateway->is_available();
		} );
	}

	public static function get( $id ) {
		return self::$gateways[ $id ] ?? null;
	}

	public static function get_default() {
		$id = Dira_BF_Settings::get( 'payment_gateway', 'manual' );
		return self::get( $id ) ?: self::get( 'manual' );
	}
}
