<?php
/**
 * Plugin Name:       Dira Booking & Files
 * Plugin URI:        https://diradev.com
 * Description:       Prise de rendez-vous en ligne et vente/téléchargement de fichiers numériques pour WordPress, indépendant du thème actif.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            DiraDev
 * Author URI:        https://diradev.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dira-booking-files
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Accès direct interdit.
}

define( 'DIRA_BF_VERSION', '1.0.0' );
define( 'DIRA_BF_FILE', __FILE__ );
define( 'DIRA_BF_DIR', plugin_dir_path( __FILE__ ) );
define( 'DIRA_BF_URL', plugin_dir_url( __FILE__ ) );
define( 'DIRA_BF_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Charge les classes du cœur de l'extension.
 */
function dira_bf_load_includes() {
	$includes = array(
		'includes/class-database.php',
		'includes/class-settings.php',
		'includes/class-customers.php',
		'includes/class-services.php',
		'includes/class-availability.php',
		'includes/class-bookings.php',
		'includes/class-files.php',
		'includes/class-orders.php',
		'includes/class-payment-gateway.php',
		'includes/gateways/class-gateway-manual.php',
		'includes/gateways/class-gateway-woocommerce.php',
		'includes/class-notifications.php',
		'includes/class-cron.php',
		'includes/class-ajax.php',
		'includes/class-shortcodes.php',
		'includes/class-plugin.php',
	);

	foreach ( $includes as $file ) {
		$path = DIRA_BF_DIR . $file;
		if ( file_exists( $path ) ) {
			require_once $path;
		}
	}

	if ( is_admin() ) {
		require_once DIRA_BF_DIR . 'admin/class-admin.php';
	}
}
dira_bf_load_includes();

/**
 * Activation : crée les tables, le dossier protégé, les pages et les réglages par défaut.
 */
function dira_bf_activate() {
	Dira_BF_Plugin::activate();
}
register_activation_hook( __FILE__, 'dira_bf_activate' );

/**
 * Désactivation : retire les tâches planifiées, ne touche pas aux données.
 */
function dira_bf_deactivate() {
	Dira_BF_Plugin::deactivate();
}
register_deactivation_hook( __FILE__, 'dira_bf_deactivate' );

/**
 * Démarrage de l'extension une fois tous les plugins chargés.
 */
function dira_bf_init() {
	Dira_BF_Plugin::instance()->init();
}
add_action( 'plugins_loaded', 'dira_bf_init' );
