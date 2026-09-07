<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$edit_id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
$editing = $edit_id ? Dira_BF_Services::get( $edit_id ) : null;
$services = Dira_BF_Services::get_all();
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Services', 'dira-booking-files' ); ?></h1>

	<div class="dira-bf-columns">
		<div class="dira-bf-col-main">
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Nom', 'dira-booking-files' ); ?></th>
						<th><?php esc_html_e( 'Durée', 'dira-booking-files' ); ?></th>
						<th><?php esc_html_e( 'Prix', 'dira-booking-files' ); ?></th>
						<th><?php esc_html_e( 'Statut', 'dira-booking-files' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $services ) ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'Aucun service. Ajoutez-en un ci-contre.', 'dira-booking-files' ); ?></td></tr>
				<?php else : foreach ( $services as $service ) : ?>
					<tr>
						<td><span class="dira-bf-color-dot" style="background:<?php echo esc_attr( $service->color ); ?>"></span> <?php echo esc_html( $service->name ); ?></td>
						<td><?php echo esc_html( $service->duration_minutes ); ?> min</td>
						<td><?php echo esc_html( Dira_BF_Settings::format_price( $service->price ) ); ?></td>
						<td><span class="dira-bf-status dira-bf-status-<?php echo esc_attr( $service->status ); ?>"><?php echo esc_html( $service->status ); ?></span></td>
						<td>
							<a class="button button-small" href="<?php echo esc_url( add_query_arg( 'edit', $service->id ) ); ?>"><?php esc_html_e( 'Modifier', 'dira-booking-files' ); ?></a>
							<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dira_bf_delete_service&id=' . $service->id ), 'dira_bf_delete_service' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Supprimer ce service ?', 'dira-booking-files' ) ); ?>');"><?php esc_html_e( 'Supprimer', 'dira-booking-files' ); ?></a>
						</td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>

		<div class="dira-bf-col-side">
			<div class="dira-bf-box">
				<h2><?php echo $editing ? esc_html__( 'Modifier le service', 'dira-booking-files' ) : esc_html__( 'Ajouter un service', 'dira-booking-files' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'dira_bf_save_service' ); ?>
					<input type="hidden" name="action" value="dira_bf_save_service">
					<input type="hidden" name="id" value="<?php echo esc_attr( $editing->id ?? 0 ); ?>">

					<p><label><?php esc_html_e( 'Nom', 'dira-booking-files' ); ?><br>
					<input type="text" class="widefat" name="name" required value="<?php echo esc_attr( $editing->name ?? '' ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Description', 'dira-booking-files' ); ?><br>
					<textarea class="widefat" name="description" rows="3"><?php echo esc_textarea( $editing->description ?? '' ); ?></textarea></label></p>

					<p><label><?php esc_html_e( 'Durée (minutes)', 'dira-booking-files' ); ?><br>
					<input type="number" min="5" class="widefat" name="duration_minutes" value="<?php echo esc_attr( $editing->duration_minutes ?? 30 ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Prix', 'dira-booking-files' ); ?><br>
					<input type="number" step="0.01" min="0" class="widefat" name="price" value="<?php echo esc_attr( $editing->price ?? 0 ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Couleur', 'dira-booking-files' ); ?><br>
					<input type="text" class="widefat" name="color" value="<?php echo esc_attr( $editing->color ?? '#4f46e5' ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Statut', 'dira-booking-files' ); ?><br>
					<select class="widefat" name="status">
						<option value="active" <?php selected( $editing->status ?? 'active', 'active' ); ?>><?php esc_html_e( 'Actif', 'dira-booking-files' ); ?></option>
						<option value="inactive" <?php selected( $editing->status ?? '', 'inactive' ); ?>><?php esc_html_e( 'Inactif', 'dira-booking-files' ); ?></option>
					</select></label></p>

					<p><label><?php esc_html_e( 'Délai minimum avant réservation (heures)', 'dira-booking-files' ); ?><br>
					<input type="number" min="0" class="widefat" name="min_notice_hours" value="<?php echo esc_attr( $editing->min_notice_hours ?? 2 ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Délai maximum de réservation (jours)', 'dira-booking-files' ); ?><br>
					<input type="number" min="1" class="widefat" name="max_notice_days" value="<?php echo esc_attr( $editing->max_notice_days ?? 60 ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Nombre maximal de personnes', 'dira-booking-files' ); ?><br>
					<input type="number" min="1" class="widefat" name="max_people" value="<?php echo esc_attr( $editing->max_people ?? 1 ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Temps de préparation avant le prochain rendez-vous (minutes)', 'dira-booking-files' ); ?><br>
					<input type="number" min="0" class="widefat" name="buffer_minutes" value="<?php echo esc_attr( $editing->buffer_minutes ?? 0 ); ?>"></label></p>

					<p><button class="button button-primary"><?php esc_html_e( 'Enregistrer', 'dira-booking-files' ); ?></button>
					<?php if ( $editing ) : ?>
						<a class="button" href="<?php echo esc_url( remove_query_arg( 'edit' ) ); ?>"><?php esc_html_e( 'Annuler', 'dira-booking-files' ); ?></a>
					<?php endif; ?>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>
