<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$weekly = Dira_BF_Availability::get_weekly_hours();
$day_labels = array(
	1 => __( 'Lundi', 'dira-booking-files' ),
	2 => __( 'Mardi', 'dira-booking-files' ),
	3 => __( 'Mercredi', 'dira-booking-files' ),
	4 => __( 'Jeudi', 'dira-booking-files' ),
	5 => __( 'Vendredi', 'dira-booking-files' ),
	6 => __( 'Samedi', 'dira-booking-files' ),
	0 => __( 'Dimanche', 'dira-booking-files' ),
);

$entries = Dira_BF_Availability::get_entries();
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Disponibilités', 'dira-booking-files' ); ?></h1>

	<h2><?php esc_html_e( 'Horaires hebdomadaires', 'dira-booking-files' ); ?></h2>
	<p class="description"><?php esc_html_e( 'Ajoutez jusqu\'à deux créneaux par jour (ex. matin / après-midi pour une pause déjeuner). Laissez vide pour un jour fermé.', 'dira-booking-files' ); ?></p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="dira-bf-box">
		<?php wp_nonce_field( 'dira_bf_save_availability' ); ?>
		<input type="hidden" name="action" value="dira_bf_save_availability">

		<table class="widefat striped dira-bf-availability-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Jour', 'dira-booking-files' ); ?></th>
					<th><?php esc_html_e( 'Créneau 1', 'dira-booking-files' ); ?></th>
					<th><?php esc_html_e( 'Créneau 2', 'dira-booking-files' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $day_labels as $day => $label ) :
				$ranges = $weekly[ $day ] ?? array();
				$r1 = $ranges[0] ?? array( 'start' => '', 'end' => '' );
				$r2 = $ranges[1] ?? array( 'start' => '', 'end' => '' );
				?>
				<tr>
					<td><strong><?php echo esc_html( $label ); ?></strong></td>
					<td>
						<input type="time" name="start_<?php echo esc_attr( $day ); ?>[]" value="<?php echo esc_attr( $r1['start'] ); ?>">
						–
						<input type="time" name="end_<?php echo esc_attr( $day ); ?>[]" value="<?php echo esc_attr( $r1['end'] ); ?>">
					</td>
					<td>
						<input type="time" name="start_<?php echo esc_attr( $day ); ?>[]" value="<?php echo esc_attr( $r2['start'] ); ?>">
						–
						<input type="time" name="end_<?php echo esc_attr( $day ); ?>[]" value="<?php echo esc_attr( $r2['end'] ); ?>">
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<p><button class="button button-primary"><?php esc_html_e( 'Enregistrer les horaires', 'dira-booking-files' ); ?></button></p>
	</form>

	<h2><?php esc_html_e( 'Jours fermés, horaires exceptionnels et créneaux bloqués', 'dira-booking-files' ); ?></h2>

	<div class="dira-bf-columns">
		<div class="dira-bf-col-main">
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Type', 'dira-booking-files' ); ?></th>
						<th><?php esc_html_e( 'Date', 'dira-booking-files' ); ?></th>
						<th><?php esc_html_e( 'Horaires', 'dira-booking-files' ); ?></th>
						<th><?php esc_html_e( 'Motif', 'dira-booking-files' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php
				$type_labels = array(
					'exception_closed' => __( 'Jour fermé', 'dira-booking-files' ),
					'exception_open'   => __( 'Horaire exceptionnel', 'dira-booking-files' ),
					'blocked'          => __( 'Créneau bloqué', 'dira-booking-files' ),
				);
				?>
				<?php if ( empty( $entries ) ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'Aucune exception enregistrée.', 'dira-booking-files' ); ?></td></tr>
				<?php else : foreach ( $entries as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( $type_labels[ $entry->type ] ?? $entry->type ); ?></td>
						<td><?php echo esc_html( $entry->specific_date ); ?></td>
						<td><?php echo $entry->start_time ? esc_html( substr( $entry->start_time, 0, 5 ) . '–' . substr( $entry->end_time, 0, 5 ) ) : '—'; ?></td>
						<td><?php echo esc_html( $entry->reason ); ?></td>
						<td><a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dira_bf_delete_availability_entry&id=' . $entry->id ), 'dira_bf_delete_availability_entry' ) ); ?>"><?php esc_html_e( 'Supprimer', 'dira-booking-files' ); ?></a></td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<div class="dira-bf-col-side">
			<div class="dira-bf-box">
				<h2><?php esc_html_e( 'Ajouter une exception', 'dira-booking-files' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'dira_bf_add_exception' ); ?>
					<input type="hidden" name="action" value="dira_bf_add_exception">

					<p><label><?php esc_html_e( 'Type', 'dira-booking-files' ); ?><br>
					<select class="widefat" name="entry_type" id="dira-bf-exception-type">
						<option value="closed"><?php esc_html_e( 'Jour fermé (vacances, jour férié…)', 'dira-booking-files' ); ?></option>
						<option value="open"><?php esc_html_e( 'Horaire exceptionnel ce jour-là', 'dira-booking-files' ); ?></option>
						<option value="blocked"><?php esc_html_e( 'Bloquer une période précise', 'dira-booking-files' ); ?></option>
					</select></label></p>

					<p><label><?php esc_html_e( 'Date', 'dira-booking-files' ); ?><br>
					<input type="date" class="widefat" name="date" required></label></p>

					<p><label><?php esc_html_e( 'De', 'dira-booking-files' ); ?><br>
					<input type="time" class="widefat" name="start_time"></label></p>

					<p><label><?php esc_html_e( 'À', 'dira-booking-files' ); ?><br>
					<input type="time" class="widefat" name="end_time"></label></p>

					<p><label><?php esc_html_e( 'Motif', 'dira-booking-files' ); ?><br>
					<input type="text" class="widefat" name="reason" placeholder="<?php esc_attr_e( 'ex. réunion', 'dira-booking-files' ); ?>"></label></p>

					<p><button class="button button-primary"><?php esc_html_e( 'Ajouter', 'dira-booking-files' ); ?></button></p>
				</form>
			</div>
		</div>
	</div>
</div>
