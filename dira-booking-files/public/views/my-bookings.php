<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$default_email = '';
if ( is_user_logged_in() ) {
	$current = wp_get_current_user();
	$default_email = $current->user_email;
}
?>
<div class="dira-booking-app dira-booking-lookup">
	<h3 class="dira-booking-step-title"><?php esc_html_e( 'Mes rendez-vous', 'dira-booking-files' ); ?></h3>
	<p class="dira-booking-lookup-intro"><?php esc_html_e( 'Renseignez votre adresse email pour retrouver vos rendez-vous.', 'dira-booking-files' ); ?></p>

	<div class="dira-booking-lookup-form">
		<input type="email" data-role="lookup-email" placeholder="<?php esc_attr_e( 'Votre email', 'dira-booking-files' ); ?>" value="<?php echo esc_attr( $default_email ); ?>">
		<button type="button" class="dira-booking-btn dira-booking-btn-primary" data-action="lookup-bookings"><?php esc_html_e( 'Afficher', 'dira-booking-files' ); ?></button>
	</div>

	<div class="dira-booking-alert" data-role="lookup-alert" hidden></div>

	<div class="dira-booking-list" data-role="bookings-list"></div>
</div>
