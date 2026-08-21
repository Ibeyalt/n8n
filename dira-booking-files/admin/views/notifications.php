<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$logs = Dira_BF_Notifications::get_log( 100 );
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Journal des notifications', 'dira-booking-files' ); ?></h1>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Type', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Destinataire', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Objet', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Statut', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Date', 'dira-booking-files' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $logs ) ) : ?>
			<tr><td colspan="5"><?php esc_html_e( 'Aucune notification envoyée.', 'dira-booking-files' ); ?></td></tr>
		<?php else : foreach ( $logs as $log ) : ?>
			<tr>
				<td><?php echo esc_html( $log->type ); ?></td>
				<td><?php echo esc_html( $log->recipient ); ?></td>
				<td><?php echo esc_html( $log->subject ); ?></td>
				<td><span class="dira-bf-status dira-bf-status-<?php echo esc_attr( $log->status ); ?>"><?php echo esc_html( $log->status ); ?></span></td>
				<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' H:i', $log->created_at ) ); ?></td>
			</tr>
		<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
