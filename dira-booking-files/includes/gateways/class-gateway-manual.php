<?php
/**
 * Passerelle "manuelle" : la commande reste en attente jusqu'à confirmation
 * du paiement par l'administrateur (virement, espèces, Mobile Money hors-API, etc.).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Gateway_Manual extends Dira_BF_Payment_Gateway {

	public function get_id() {
		return 'manual';
	}

	public function get_title() {
		return __( 'Paiement manuel / hors-ligne', 'dira-booking-files' );
	}

	public function process_order( $order ) {
		return array(
			'type'    => 'message',
			'message' => sprintf(
				/* translators: %s: order reference. */
				__( 'Votre commande %s a été enregistrée. Vous recevrez vos accès de téléchargement dès confirmation du paiement.', 'dira-booking-files' ),
				$order->order_ref
			),
		);
	}
}

add_action(
	'plugins_loaded',
	function () {
		if ( class_exists( 'Dira_BF_Gateway_Manager' ) ) {
			Dira_BF_Gateway_Manager::register( new Dira_BF_Gateway_Manual() );
		}
	},
	20
);
