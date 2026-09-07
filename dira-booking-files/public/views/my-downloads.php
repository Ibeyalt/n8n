<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$default_email = '';
if ( is_user_logged_in() ) {
	$current = wp_get_current_user();
	$default_email = $current->user_email;
}
?>
<div class="dira-files-app dira-files-lookup">
	<h3 class="dira-files-step-title"><?php esc_html_e( 'Mes téléchargements', 'dira-booking-files' ); ?></h3>
	<p class="dira-files-lookup-intro"><?php esc_html_e( 'Renseignez votre adresse email pour retrouver vos fichiers.', 'dira-booking-files' ); ?></p>

	<div class="dira-files-lookup-form">
		<input type="email" data-role="lookup-email" placeholder="<?php esc_attr_e( 'Votre email', 'dira-booking-files' ); ?>" value="<?php echo esc_attr( $default_email ); ?>">
		<button type="button" class="dira-files-btn" data-action="lookup-downloads"><?php esc_html_e( 'Afficher', 'dira-booking-files' ); ?></button>
	</div>

	<div class="dira-files-alert" data-role="lookup-alert" hidden></div>

	<div class="dira-files-list" data-role="downloads-list"></div>
</div>
