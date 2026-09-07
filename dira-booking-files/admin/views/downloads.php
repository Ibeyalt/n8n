<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;
$table = Dira_BF_Database::table( 'downloads' );
$rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC LIMIT 200" );
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Téléchargements', 'dira-booking-files' ); ?></h1>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Fichier', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Client', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'IP', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Utilisés', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Expire le', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Créé le', 'dira-booking-files' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $rows ) ) : ?>
			<tr><td colspan="6"><?php esc_html_e( 'Aucun téléchargement.', 'dira-booking-files' ); ?></td></tr>
		<?php else : foreach ( $rows as $row ) :
			$file     = Dira_BF_Files::get( $row->file_id );
			$customer = $row->customer_id ? Dira_BF_Customers::get( $row->customer_id ) : null;
			?>
			<tr>
				<td><?php echo esc_html( $file->title ?? '' ); ?></td>
				<td><?php echo $customer ? esc_html( Dira_BF_Customers::full_name( $customer ) ) : '—'; ?></td>
				<td><?php echo esc_html( $row->ip_address ); ?></td>
				<td><?php echo esc_html( $row->download_count . ' / ' . ( $row->max_downloads ?: '∞' ) ); ?></td>
				<td><?php echo $row->expires_at ? esc_html( mysql2date( get_option( 'date_format' ), $row->expires_at ) ) : '—'; ?></td>
				<td><?php echo esc_html( mysql2date( get_option( 'date_format' ) . ' H:i', $row->created_at ) ); ?></td>
			</tr>
		<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
