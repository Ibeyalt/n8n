<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$orders = Dira_BF_Orders::get_all( array( 'limit' => 200 ) );
$statuses = Dira_BF_Orders::STATUSES;
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Commandes de fichiers', 'dira-booking-files' ); ?></h1>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Référence', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Fichier', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Client', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Montant', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Moyen', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Statut', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Date', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Action', 'dira-booking-files' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $orders ) ) : ?>
			<tr><td colspan="8"><?php esc_html_e( 'Aucune commande.', 'dira-booking-files' ); ?></td></tr>
		<?php else : foreach ( $orders as $order ) :
			$file     = Dira_BF_Files::get( $order->file_id );
			$customer = Dira_BF_Customers::get( $order->customer_id );
			?>
			<tr>
				<td><?php echo esc_html( $order->order_ref ); ?></td>
				<td><?php echo esc_html( $file->title ?? '' ); ?></td>
				<td><?php echo esc_html( Dira_BF_Customers::full_name( $customer ) ); ?><br><small><?php echo esc_html( $customer->email ?? '' ); ?></small></td>
				<td><?php echo esc_html( $order->amount . ' ' . $order->currency ); ?></td>
				<td><?php echo esc_html( $order->payment_method ); ?></td>
				<td><span class="dira-bf-status dira-bf-status-<?php echo esc_attr( $order->status ); ?>"><?php echo esc_html( $order->status ); ?></span></td>
				<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $order->created_at ) ); ?></td>
				<td>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="dira-bf-inline-form">
						<?php wp_nonce_field( 'dira_bf_update_order' ); ?>
						<input type="hidden" name="action" value="dira_bf_update_order">
						<input type="hidden" name="id" value="<?php echo esc_attr( $order->id ); ?>">
						<select name="status">
							<?php foreach ( $statuses as $status ) : ?>
								<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $order->status, $status ); ?>><?php echo esc_html( ucfirst( $status ) ); ?></option>
							<?php endforeach; ?>
						</select>
						<button type="submit" class="button button-small"><?php esc_html_e( 'Mettre à jour', 'dira-booking-files' ); ?></button>
					</form>
				</td>
			</tr>
		<?php endforeach; endif; ?>
		</tbody>
	</table>
	<p class="description"><?php esc_html_e( 'Passer une commande à « Payé » génère automatiquement un lien de téléchargement sécurisé envoyé au client.', 'dira-booking-files' ); ?></p>
</div>
