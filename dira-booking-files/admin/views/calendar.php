<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$view = isset( $_GET['view'] ) && in_array( $_GET['view'], array( 'day', 'week', 'month' ), true ) ? $_GET['view'] : 'month';
$ref_date = isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : current_time( 'Y-m-d' );
$ref_ts   = strtotime( $ref_date ) ?: current_time( 'timestamp' );

$base_url = admin_url( 'admin.php?page=dira-booking-calendar' );

function dira_bf_calendar_nav_url( $base_url, $view, $date ) {
	return add_query_arg( array( 'view' => $view, 'date' => $date ), $base_url );
}

if ( 'day' === $view ) {
	$from = $to = date( 'Y-m-d', $ref_ts );
	$prev = dira_bf_calendar_nav_url( $base_url, 'day', date( 'Y-m-d', strtotime( '-1 day', $ref_ts ) ) );
	$next = dira_bf_calendar_nav_url( $base_url, 'day', date( 'Y-m-d', strtotime( '+1 day', $ref_ts ) ) );
	$title = date_i18n( 'l j F Y', $ref_ts );
} elseif ( 'week' === $view ) {
	$week_start = strtotime( 'monday this week', $ref_ts );
	$from = date( 'Y-m-d', $week_start );
	$to   = date( 'Y-m-d', strtotime( '+6 days', $week_start ) );
	$prev = dira_bf_calendar_nav_url( $base_url, 'week', date( 'Y-m-d', strtotime( '-7 days', $ref_ts ) ) );
	$next = dira_bf_calendar_nav_url( $base_url, 'week', date( 'Y-m-d', strtotime( '+7 days', $ref_ts ) ) );
	$title = sprintf( '%s — %s', date_i18n( 'j M', strtotime( $from ) ), date_i18n( 'j M Y', strtotime( $to ) ) );
} else {
	$from = date( 'Y-m-01', $ref_ts );
	$to   = date( 'Y-m-t', $ref_ts );
	$prev = dira_bf_calendar_nav_url( $base_url, 'month', date( 'Y-m-d', strtotime( '-1 month', $ref_ts ) ) );
	$next = dira_bf_calendar_nav_url( $base_url, 'month', date( 'Y-m-d', strtotime( '+1 month', $ref_ts ) ) );
	$title = date_i18n( 'F Y', $ref_ts );
}

$bookings = Dira_BF_Bookings::get_all( array( 'date_from' => $from, 'date_to' => $to, 'limit' => 500 ) );
$by_date = array();
foreach ( $bookings as $booking ) {
	$by_date[ $booking->booking_date ][] = $booking;
}
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Calendrier', 'dira-booking-files' ); ?></h1>

	<div class="dira-bf-calendar-toolbar">
		<div class="dira-bf-view-switch">
			<a class="button <?php echo 'day' === $view ? 'button-primary' : ''; ?>" href="<?php echo esc_url( dira_bf_calendar_nav_url( $base_url, 'day', $ref_date ) ); ?>"><?php esc_html_e( 'Jour', 'dira-booking-files' ); ?></a>
			<a class="button <?php echo 'week' === $view ? 'button-primary' : ''; ?>" href="<?php echo esc_url( dira_bf_calendar_nav_url( $base_url, 'week', $ref_date ) ); ?>"><?php esc_html_e( 'Semaine', 'dira-booking-files' ); ?></a>
			<a class="button <?php echo 'month' === $view ? 'button-primary' : ''; ?>" href="<?php echo esc_url( dira_bf_calendar_nav_url( $base_url, 'month', $ref_date ) ); ?>"><?php esc_html_e( 'Mois', 'dira-booking-files' ); ?></a>
		</div>
		<div class="dira-bf-date-nav">
			<a class="button" href="<?php echo esc_url( $prev ); ?>">‹</a>
			<strong><?php echo esc_html( $title ); ?></strong>
			<a class="button" href="<?php echo esc_url( $next ); ?>">›</a>
		</div>
	</div>

	<?php if ( 'month' === $view ) :
		$first_weekday = (int) date( 'N', strtotime( $from ) ); // 1 (lundi) .. 7 (dimanche)
		$days_in_month = (int) date( 't', $ref_ts );
		?>
		<table class="widefat dira-bf-month-grid">
			<thead>
				<tr>
					<?php foreach ( array( 'Lun','Mar','Mer','Jeu','Ven','Sam','Dim' ) as $d ) : ?><th><?php echo esc_html( $d ); ?></th><?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<tr>
				<?php
				for ( $i = 1; $i < $first_weekday; $i++ ) { echo '<td class="dira-bf-empty-cell"></td>'; }
				for ( $day = 1; $day <= $days_in_month; $day++ ) :
					$date_str = date( 'Y-m-', $ref_ts ) . str_pad( $day, 2, '0', STR_PAD_LEFT );
					$col = ( $first_weekday - 1 + $day - 1 ) % 7;
					if ( 0 === $col && $day !== 1 ) { echo '</tr><tr>'; }
					?>
					<td class="dira-bf-day-cell">
						<div class="dira-bf-day-number"><?php echo esc_html( $day ); ?></div>
						<?php foreach ( $by_date[ $date_str ] ?? array() as $booking ) :
							$customer = Dira_BF_Customers::get( $booking->customer_id );
							?>
							<div class="dira-bf-mini-event dira-bf-status-<?php echo esc_attr( $booking->status ); ?>" title="<?php echo esc_attr( Dira_BF_Customers::full_name( $customer ) ); ?>">
								<?php echo esc_html( substr( $booking->start_time, 0, 5 ) . ' ' . Dira_BF_Customers::full_name( $customer ) ); ?>
							</div>
						<?php endforeach; ?>
					</td>
				<?php endfor; ?>
				</tr>
			</tbody>
		</table>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Date', 'dira-booking-files' ); ?></th>
					<th><?php esc_html_e( 'Heure', 'dira-booking-files' ); ?></th>
					<th><?php esc_html_e( 'Client', 'dira-booking-files' ); ?></th>
					<th><?php esc_html_e( 'Service', 'dira-booking-files' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'dira-booking-files' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php if ( empty( $bookings ) ) : ?>
				<tr><td colspan="5"><?php esc_html_e( 'Aucun rendez-vous sur cette période.', 'dira-booking-files' ); ?></td></tr>
			<?php else : foreach ( $bookings as $booking ) :
				$service  = Dira_BF_Services::get( $booking->service_id );
				$customer = Dira_BF_Customers::get( $booking->customer_id );
				?>
				<tr>
					<td><?php echo esc_html( $booking->booking_date ); ?></td>
					<td><?php echo esc_html( substr( $booking->start_time, 0, 5 ) ); ?></td>
					<td><?php echo esc_html( Dira_BF_Customers::full_name( $customer ) ); ?></td>
					<td><?php echo esc_html( $service->name ?? '' ); ?></td>
					<td><span class="dira-bf-status dira-bf-status-<?php echo esc_attr( $booking->status ); ?>"><?php echo esc_html( $booking->status ); ?></span></td>
				</tr>
			<?php endforeach; endif; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
