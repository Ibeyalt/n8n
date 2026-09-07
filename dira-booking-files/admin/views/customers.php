<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
$customers = Dira_BF_Customers::get_all( array( 'search' => $search, 'limit' => 200 ) );
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Clients', 'dira-booking-files' ); ?></h1>

	<form method="get">
		<input type="hidden" name="page" value="dira-booking-customers">
		<p class="search-box">
			<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Rechercher un client…', 'dira-booking-files' ); ?>">
			<button class="button"><?php esc_html_e( 'Rechercher', 'dira-booking-files' ); ?></button>
		</p>
	</form>

	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Nom', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Email', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Téléphone', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'WhatsApp', 'dira-booking-files' ); ?></th>
				<th><?php esc_html_e( 'Client depuis', 'dira-booking-files' ); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php if ( empty( $customers ) ) : ?>
			<tr><td colspan="6"><?php esc_html_e( 'Aucun client.', 'dira-booking-files' ); ?></td></tr>
		<?php else : foreach ( $customers as $customer ) : ?>
			<tr>
				<td><?php echo esc_html( Dira_BF_Customers::full_name( $customer ) ); ?></td>
				<td><?php echo esc_html( $customer->email ); ?></td>
				<td><?php echo esc_html( $customer->phone ); ?></td>
				<td><?php echo esc_html( $customer->whatsapp ); ?></td>
				<td><?php echo esc_html( mysql2date( get_option( 'date_format' ), $customer->created_at ) ); ?></td>
				<td>
					<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dira_bf_delete_customer&id=' . $customer->id ), 'dira_bf_delete_customer' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Supprimer ce client ?', 'dira-booking-files' ) ); ?>');"><?php esc_html_e( 'Supprimer', 'dira-booking-files' ); ?></a>
				</td>
			</tr>
		<?php endforeach; endif; ?>
		</tbody>
	</table>
</div>
