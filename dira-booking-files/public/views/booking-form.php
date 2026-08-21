<?php
/**
 * Formulaire de réservation multi-étapes.
 * Variables disponibles : $services (array), $atts (array, shortcode).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$preselect = isset( $atts['service'] ) ? sanitize_text_field( $atts['service'] ) : '';
?>
<div class="dira-booking-app" data-preselect="<?php echo esc_attr( $preselect ); ?>">

	<ol class="dira-booking-progress">
		<li class="dira-booking-progress-item is-active" data-progress="1"><?php esc_html_e( 'Service', 'dira-booking-files' ); ?></li>
		<li class="dira-booking-progress-item" data-progress="2"><?php esc_html_e( 'Date & heure', 'dira-booking-files' ); ?></li>
		<li class="dira-booking-progress-item" data-progress="3"><?php esc_html_e( 'Vos informations', 'dira-booking-files' ); ?></li>
		<li class="dira-booking-progress-item" data-progress="4"><?php esc_html_e( 'Confirmation', 'dira-booking-files' ); ?></li>
	</ol>

	<div class="dira-booking-alert" data-role="alert" hidden></div>

	<!-- Étape 1 : service -->
	<section class="dira-booking-step" data-step="1">
		<h3 class="dira-booking-step-title"><?php esc_html_e( 'Choisissez un service', 'dira-booking-files' ); ?></h3>
		<div class="dira-booking-service-grid">
			<?php foreach ( $services as $service ) : ?>
				<button type="button" class="dira-booking-service-card" data-service-id="<?php echo esc_attr( $service->id ); ?>" data-service-name="<?php echo esc_attr( $service->name ); ?>" style="--dira-service-color: <?php echo esc_attr( $service->color ); ?>">
					<span class="dira-booking-service-name"><?php echo esc_html( $service->name ); ?></span>
					<span class="dira-booking-service-meta"><?php echo esc_html( $service->duration_minutes ); ?> min · <?php echo esc_html( Dira_BF_Settings::format_price( $service->price ) ); ?></span>
					<?php if ( $service->description ) : ?><span class="dira-booking-service-desc"><?php echo esc_html( wp_trim_words( $service->description, 16 ) ); ?></span><?php endif; ?>
				</button>
			<?php endforeach; ?>
			<?php if ( empty( $services ) ) : ?>
				<p><?php esc_html_e( 'Aucun service disponible pour le moment.', 'dira-booking-files' ); ?></p>
			<?php endif; ?>
		</div>
	</section>

	<!-- Étape 2 : date + créneau -->
	<section class="dira-booking-step" data-step="2" hidden>
		<h3 class="dira-booking-step-title"><?php esc_html_e( 'Choisissez une date et un horaire', 'dira-booking-files' ); ?></h3>
		<p class="dira-booking-selected-service" data-role="selected-service"></p>
		<label class="dira-booking-field">
			<span><?php esc_html_e( 'Date', 'dira-booking-files' ); ?></span>
			<input type="date" data-role="date-input" min="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>">
		</label>
		<div class="dira-booking-slots" data-role="slots"></div>
		<div class="dira-booking-nav">
			<button type="button" class="dira-booking-btn dira-booking-btn-ghost" data-action="back">‹ <?php esc_html_e( 'Retour', 'dira-booking-files' ); ?></button>
		</div>
	</section>

	<!-- Étape 3 : informations client -->
	<section class="dira-booking-step" data-step="3" hidden>
		<h3 class="dira-booking-step-title"><?php esc_html_e( 'Vos informations', 'dira-booking-files' ); ?></h3>
		<div class="dira-booking-form-grid">
			<label class="dira-booking-field"><span><?php esc_html_e( 'Nom', 'dira-booking-files' ); ?> *</span><input type="text" data-field="last_name" required></label>
			<label class="dira-booking-field"><span><?php esc_html_e( 'Prénom', 'dira-booking-files' ); ?> *</span><input type="text" data-field="first_name" required></label>
			<label class="dira-booking-field"><span><?php esc_html_e( 'Téléphone', 'dira-booking-files' ); ?> *</span><input type="tel" data-field="phone" required></label>
			<label class="dira-booking-field"><span><?php esc_html_e( 'WhatsApp', 'dira-booking-files' ); ?></span><input type="tel" data-field="whatsapp"></label>
			<label class="dira-booking-field"><span><?php esc_html_e( 'Email', 'dira-booking-files' ); ?> *</span><input type="email" data-field="email" required></label>
			<label class="dira-booking-field"><span><?php esc_html_e( 'Adresse', 'dira-booking-files' ); ?></span><input type="text" data-field="address"></label>
			<label class="dira-booking-field dira-booking-field-full"><span><?php esc_html_e( 'Commentaire', 'dira-booking-files' ); ?></span><textarea data-field="comment" rows="2"></textarea></label>
			<label class="dira-booking-field dira-booking-field-full"><span><?php esc_html_e( 'Informations complémentaires', 'dira-booking-files' ); ?></span><textarea data-field="extra_info" rows="2"></textarea></label>
		</div>
		<div class="dira-booking-nav">
			<button type="button" class="dira-booking-btn dira-booking-btn-ghost" data-action="back">‹ <?php esc_html_e( 'Retour', 'dira-booking-files' ); ?></button>
			<button type="button" class="dira-booking-btn dira-booking-btn-primary" data-action="to-recap"><?php esc_html_e( 'Voir le récapitulatif', 'dira-booking-files' ); ?></button>
		</div>
	</section>

	<!-- Étape 4 : récapitulatif + confirmation -->
	<section class="dira-booking-step" data-step="4" hidden>
		<h3 class="dira-booking-step-title"><?php esc_html_e( 'Récapitulatif', 'dira-booking-files' ); ?></h3>
		<div class="dira-booking-recap" data-role="recap"></div>
		<div class="dira-booking-nav">
			<button type="button" class="dira-booking-btn dira-booking-btn-ghost" data-action="back"><?php esc_html_e( 'Modifier', 'dira-booking-files' ); ?></button>
			<button type="button" class="dira-booking-btn dira-booking-btn-primary" data-action="submit"><?php esc_html_e( 'Confirmer le rendez-vous', 'dira-booking-files' ); ?></button>
		</div>
	</section>

	<!-- Étape 5 : succès -->
	<section class="dira-booking-step" data-step="5" hidden>
		<div class="dira-booking-success">
			<h3><?php esc_html_e( 'Votre demande de rendez-vous a bien été enregistrée.', 'dira-booking-files' ); ?></h3>
			<p data-role="success-details"></p>
		</div>
	</section>

</div>
