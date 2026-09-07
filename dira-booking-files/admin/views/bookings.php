<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$status_filter = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
$args = array( 'limit' => 100 );
if ( $status_filter ) {
	$args['status'] = $status_filter;
}
$bookings = Dira_BF_Bookings::get_all( $args );
$statuses = Dira_BF_Bookings::STATUSES;
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Rendez-vous', 'dira-booking-files' ); ?></h1>

	<ul class="subsubsub">
		<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=dira-booking-bookings' ) ); ?>" class="<?php echo '' === $status_filter ? 'current' : ''; ?>"><?php esc_html_e( 'Tous', 'dira-booking-files' ); ?></a> |</li>
		<?php foreach ( $statuses as $i => $status ) : ?>
			<li><a href="<?php echo esc_url( add_query_arg( 'status', $status, admin_url( 'admin.php?page=dira-booking-bookings' ) ) ); ?>" class="<?php echo $status_filter === $status ? 'current' : ''; ?>"><?php echo esc_html( ucfirst( $status ) ); ?></a><?php echo $i < count( $statuses ) - 1 ? ' |' : ''; ?></li>
		<?php endforeach; ?>
	</ul>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Référence', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Client', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Téléphone', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Service', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Date', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Heure', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Statut', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Action', 'dira-booking-files' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $bookings ) ) : ?>
			<tr><td colspan="8"><?php esc_html_e( 'Aucun rendez-vous.', 'dira-booking-files' ); ?></td></tr>
		<?php else : foreach ( $bookings as $booking ) :
			$service  = Dira_BF_Services::get( $booking->service_id );
			$customer = Dira_BF_Customers::get( $booking->customer_id );
			?>
			<tr>
				<td><?php echo esc_html( $booking->booking_ref ); ?></td>
				<td><?php echo esc_html( Dira_BF_Customers::full_name( $customer ) ); ?><br><small><?php echo esc_html( $customer->email ?? '' ); ?></small></td>
				<td><?php echo esc_html( $customer->phone ?? '' ); ?></td>
				<td><?php echo esc_html( $service->name ?? '' ); ?></td>
				<td><?php echo esc_html( $booking->booking_date ); ?></td>
				<td><?php echo esc_html( substr( $booking->start_time, 0, 5 ) . '–' . substr( $booking->end_time, 0, 5 ) ); ?></td>
				<td><span class="dira-bf-status dira-bf-status-<?php echo esc_attr( $booking->status ); ?>"><?php echo esc_html( $booking->status ); ?></span></td>
				<td>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="dira-bf-inline-form">
						<?php wp_nonce_field( 'dira_bf_update_booking' ); ?>
						<input type="hidden" name="action" value="dira_bf_update_booking">
						<input type="hidden" name="id" value="<?php echo esc_attr( $booking->id ); ?>">
						<select name="status">
							<?php foreach ( $statuses as $status ) : ?>
								<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $booking->status, $status ); ?>><?php echo esc_html( ucfirst( $status ) ); ?></option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="button button-small"><?php esc_html_e( 'Mettre à jour', 'dira-booking-files' ); ?></button>
					</form>
				</td>
			</tr>
		<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
