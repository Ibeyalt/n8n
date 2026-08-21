<?php
/**
 * Désinstallation de l'extension.
 *
 * N'est exécuté que lorsque l'utilisateur choisit "Supprimer" depuis
 * WordPress → Extensions (jamais lors d'une simple désactivation).
 * Respecte le choix "Conserver mes données" fait dans Paramètres → Avancé.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'dira_bf_settings', array() );
$keep_data = isset( $settings['keep_data_on_uninstall'] ) ? (bool) $settings['keep_data_on_uninstall'] : true;

if ( $keep_data ) {
	return; // L'administrateur a choisi de conserver ses données.
}

global $wpdb;

$tables = array(
	$wpdb->prefix . 'dira_services',
	$wpdb->prefix . 'dira_customers',
	$wpdb->prefix . 'dira_bookings',
	$wpdb->prefix . 'dira_availability',
	$wpdb->prefix . 'dira_file_categories',
	$wpdb->prefix . 'dira_files',
	$wpdb->prefix . 'dira_file_orders',
	$wpdb->prefix . 'dira_downloads',
	$wpdb->prefix . 'dira_notifications',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" ); // phpcs:ignore -- nom de table interne, non issu d'une entrée utilisateur.
}

// Options.
delete_option( 'dira_bf_settings' );
delete_option( 'dira_bf_email_templates' );
delete_option( 'dira_bf_pages' );

// Options des produits WooCommerce liés (le cas échéant).
$wc_product_options = $wpdb->get_col( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE 'dira_bf_wc_product_%'" );
foreach ( $wc_product_options as $option_name ) {
	delete_option( $option_name );
}

// Dossier protégé de stockage des fichiers.
$uploads = wp_upload_dir();
$dir     = trailingslashit( $uploads['basedir'] ) . 'dira-protected';
if ( is_dir( $dir ) ) {
	$files = glob( $dir . '/*' );
	foreach ( (array) $files as $file ) {
		if ( is_file( $file ) ) {
			@unlink( $file ); // phpcs:ignore
		}
	}
	@rmdir( $dir ); // phpcs:ignore
}

wp_clear_scheduled_hook( 'dira_bf_hourly_event' );
