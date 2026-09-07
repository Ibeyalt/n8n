<?php
/**
 * Intégration WooCommerce optionnelle. L'extension continue de fonctionner
 * sans WooCommerce : cette passerelle ne s'enregistre que si WooCommerce est actif.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dira_BF_Gateway_WooCommerce extends Dira_BF_Payment_Gateway {

	public function get_id() {
		return 'woocommerce';
	}

	public function get_title() {
		return __( 'WooCommerce', 'dira-booking-files' );
	}

	public function is_available() {
		return class_exists( 'WooCommerce' );
	}

	public function process_order( $order ) {
		if ( ! $this->is_available() ) {
			return new WP_Error( 'woocommerce_unavailable', __( 'WooCommerce n\'est pas actif.', 'dira-booking-files' ) );
		}

		$product_id = $this->get_or_create_product( $order );
		if ( is_wp_error( $product_id ) ) {
			return $product_id;
		}

		WC()->cart->empty_cart();
		WC()->cart->add_to_cart( $product_id, 1, 0, array(), array( 'dira_bf_order_id' => $order->id ) );

		return array(
			'type' => 'redirect',
			'url'  => wc_get_checkout_url(),
		);
	}

	private function get_or_create_product( $order ) {
		$file = Dira_BF_Files::get( $order->file_id );
		if ( ! $file ) {
			return new WP_Error( 'invalid_file', __( 'Fichier introuvable.', 'dira-booking-files' ) );
		}

		$option_key = 'dira_bf_wc_product_' . $file->id;
		$product_id = (int) get_option( $option_key );

		if ( $product_id && get_post( $product_id ) ) {
			return $product_id;
		}

		$product = new WC_Product_Simple();
		$product->set_name( $file->title );
		$product->set_regular_price( (string) $order->amount );
		$product->set_price( (string) $order->amount );
		$product->set_virtual( true );
		$product->set_catalog_visibility( 'hidden' );
		$product->set_status( 'publish' );
		$product_id = $product->save();

		update_option( $option_key, $product_id );

		return $product_id;
	}
}

add_action(
	'plugins_loaded',
	function () {
		if ( class_exists( 'WooCommerce' ) && class_exists( 'Dira_BF_Gateway_Manager' ) ) {
			Dira_BF_Gateway_Manager::register( new Dira_BF_Gateway_WooCommerce() );

			// Reporte l'ID de commande Dira sur la ligne de commande WooCommerce.
			add_action(
				'woocommerce_checkout_create_order_line_item',
				function ( $item, $cart_item_key, $values ) {
					if ( ! empty( $values['dira_bf_order_id'] ) ) {
						$item->add_meta_data( '_dira_bf_order_id', (int) $values['dira_bf_order_id'], true );
					}
				},
				10,
				3
			);

			// Marque la commande Dira comme payée une fois la commande WooCommerce complétée/traitée.
			$mark_paid = function ( $wc_order_id ) {
				$wc_order = wc_get_order( $wc_order_id );
				if ( ! $wc_order ) {
					return;
				}
				foreach ( $wc_order->get_items() as $item ) {
					$dira_order_id = $item->get_meta( '_dira_bf_order_id' );
					if ( $dira_order_id ) {
						Dira_BF_Orders::mark_paid( (int) $dira_order_id, (string) $wc_order_id );
					}
				}
			};
			add_action( 'woocommerce_order_status_completed', $mark_paid );
			add_action( 'woocommerce_order_status_processing', $mark_paid );
		}
	},
	20
);
