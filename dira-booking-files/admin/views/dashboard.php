<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$counts       = Dira_BF_Bookings::counts();
$services_n   = Dira_BF_Services::count();
$files_n      = Dira_BF_Files::count();
$customers_n  = Dira_BF_Customers::count();
$revenue      = Dira_BF_Orders::revenue_total();

global $wpdb;
$downloads_n = (int) $wpdb->get_var( 'SELECT COALESCE(SUM(download_count),0) FROM ' . Dira_BF_Database::table( 'files' ) );

$recent = Dira_BF_Bookings::get_all( array( 'limit' => 8 ) );
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Dira Booking — Tableau de bord', 'dira-booking-files' ); ?></h1>

	<div class="dira-bf-cards">
		<div class="dira-bf-card"><span class="dira-bf-card-value"><?php echo esc_html( $counts['today'] ); ?></span><span class="dira-bf-card-label"><?php esc_html_e( "Rendez-vous aujourd'hui", 'dira-booking-files' ); ?></span></div>
		<div class="dira-bf-card"><span class="dira-bf-card-value"><?php echo esc_html( $counts['upcoming'] ); ?></span><span class="dira-bf-card-label"><?php esc_html_e( 'Rendez-vous à venir', 'dira-booking-files' ); ?></span></div>
		<div class="dira-bf-card"><span class="dira-bf-card-value"><?php echo esc_html( $counts['pending'] ); ?></span><span class="dira-bf-card-label"><?php esc_html_e( 'En attente', 'dira-booking-files' ); ?></span></div>
		<div class="dira-bf-card"><span class="dira-bf-card-value"><?php echo esc_html( $counts['confirmed'] ); ?></span><span class="dira-bf-card-label"><?php esc_html_e( 'Confirmés', 'dira-booking-files' ); ?></span></div>
		<div class="dira-bf-card"><span class="dira-bf-card-value"><?php echo esc_html( $counts['cancelled'] ); ?></span><span class="dira-bf-card-label"><?php esc_html_e( 'Annulés', 'dira-booking-files' ); ?></span></div>
		<div class="dira-bf-card"><span class="dira-bf-card-value"><?php echo esc_html( $customers_n ); ?></span><span class="dira-bf-card-label"><?php esc_html_e( 'Clients', 'dira-booking-files' ); ?></span></div>
		<div class="dira-bf-card"><span class="dira-bf-card-value"><?php echo esc_html( $services_n ); ?></span><span class="dira-bf-card-label"><?php esc_html_e( 'Services', 'dira-booking-files' ); ?></span></div>
		<div class="dira-bf-card"><span class="dira-bf-card-value"><?php echo esc_html( $files_n ); ?></span><span class="dira-bf-card-label"><?php esc_html_e( 'Fichiers', 'dira-booking-files' ); ?></span></div>
		<div class="dira-bf-card"><span class="dira-bf-card-value"><?php echo esc_html( $downloads_n ); ?></span><span class="dira-bf-card-label"><?php esc_html_e( 'Téléchargements', 'dira-booking-files' ); ?></span></div>
		<div class="dira-bf-card dira-bf-card-accent"><span class="dira-bf-card-value"><?php echo esc_html( Dira_BF_Settings::format_price( $revenue ) ); ?></span><span class="dira-bf-card-label"><?php esc_html_e( 'Revenus fichiers payants', 'dira-booking-files' ); ?></span></div>
	</div>

	<h2><?php esc_html_e( 'Derniers rendez-vous', 'dira-booking-files' ); ?></h2>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Référence', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Client', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Service', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Date', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Statut', 'dira-booking-files' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $recent ) ) : ?>
			<tr><td colspan="5"><?php esc_html_e( 'Aucun rendez-vous pour le moment.', 'dira-booking-files' ); ?></td></tr>
		<?php else : foreach ( $recent as $booking ) :
			$service  = Dira_BF_Services::get( $booking->service_id );
			$customer = Dira_BF_Customers::get( $booking->customer_id );
			?>
			<tr>
				<td><?php echo esc_html( $booking->booking_ref ); ?></td>
				<td><?php echo esc_html( Dira_BF_Customers::full_name( $customer ) ); ?></td>
				<td><?php echo esc_html( $service->name ?? '' ); ?></td>
				<td><?php echo esc_html( $booking->booking_date . ' ' . substr( $booking->start_time, 0, 5 ) ); ?></td>
				<td><span class="dira-bf-status dira-bf-status-<?php echo esc_attr( $booking->status ); ?>"><?php echo esc_html( $booking->status ); ?></span></td>
			</tr>
		<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
