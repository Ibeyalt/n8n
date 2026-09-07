<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$settings = Dira_BF_Settings::get_all();
$gateways = class_exists( 'Dira_BF_Gateway_Manager' ) ? Dira_BF_Gateway_Manager::get_available() : array();
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Paramètres', 'dira-booking-files' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'dira_bf_save_settings' ); ?>
		<input type="hidden" name="action" value="dira_bf_save_settings">

		<h2 class="nav-tab-wrapper dira-bf-tabs">
			<a href="#general" class="nav-tab nav-tab-active" data-tab="general"><?php esc_html_e( 'Général', 'dira-booking-files' ); ?></a>
			<a href="#booking" class="nav-tab" data-tab="booking"><?php esc_html_e( 'Rendez-vous', 'dira-booking-files' ); ?></a>
			<a href="#notifications" class="nav-tab" data-tab="notifications"><?php esc_html_e( 'Notifications', 'dira-booking-files' ); ?></a>
			<a href="#files" class="nav-tab" data-tab="files"><?php esc_html_e( 'Fichiers', 'dira-booking-files' ); ?></a>
			<a href="#payment" class="nav-tab" data-tab="payment"><?php esc_html_e( 'Paiement', 'dira-booking-files' ); ?></a>
			<a href="#advanced" class="nav-tab" data-tab="advanced"><?php esc_html_e( 'Avancé', 'dira-booking-files' ); ?></a>
		</h2>

		<div class="dira-bf-tab-panel" data-panel="general">
			<table class="form-table">
				<tr><th><?php esc_html_e( "Nom de l'entreprise", 'dira-booking-files' ); ?></th><td><input type="text" class="regular-text" name="company_name" value="<?php echo esc_attr( $settings['company_name'] ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'Email', 'dira-booking-files' ); ?></th><td><input type="email" class="regular-text" name="company_email" value="<?php echo esc_attr( $settings['company_email'] ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'Téléphone', 'dira-booking-files' ); ?></th><td><input type="text" class="regular-text" name="company_phone" value="<?php echo esc_attr( $settings['company_phone'] ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'WhatsApp', 'dira-booking-files' ); ?></th><td><input type="text" class="regular-text" name="company_whatsapp" value="<?php echo esc_attr( $settings['company_whatsapp'] ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'Adresse', 'dira-booking-files' ); ?></th><td><textarea class="regular-text" name="company_address" rows="2"><?php echo esc_textarea( $settings['company_address'] ); ?></textarea></td></tr>
				<tr><th><?php esc_html_e( 'Devise', 'dira-booking-files' ); ?></th><td>
					<select name="currency">
						<?php foreach ( array( 'XOF', 'XAF', 'EUR', 'USD', 'GBP', 'CAD' ) as $currency ) : ?>
							<option value="<?php echo esc_attr( $currency ); ?>" <?php selected( $settings['currency'], $currency ); ?>><?php echo esc_html( $currency ); ?></option>
						<?php endforeach; ?>
					</select>
				</td></tr>
				<tr><th><?php esc_html_e( 'Couleur principale', 'dira-booking-files' ); ?></th><td><input type="text" name="accent_color" value="<?php echo esc_attr( $settings['accent_color'] ); ?>"></td></tr>
			</table>
		</div>

		<div class="dira-bf-tab-panel" data-panel="booking" hidden>
			<table class="form-table">
				<tr><th><?php esc_html_e( 'Durée par défaut (minutes)', 'dira-booking-files' ); ?></th><td><input type="number" name="default_duration" value="<?php echo esc_attr( $settings['default_duration'] ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'Délai minimum (heures)', 'dira-booking-files' ); ?></th><td><input type="number" name="min_notice_hours" value="<?php echo esc_attr( $settings['min_notice_hours'] ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'Délai maximum (jours)', 'dira-booking-files' ); ?></th><td><input type="number" name="max_notice_days" value="<?php echo esc_attr( $settings['max_notice_days'] ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'Confirmation automatique', 'dira-booking-files' ); ?></th><td><label><input type="checkbox" name="auto_confirm" value="1" <?php checked( $settings['auto_confirm'], 1 ); ?>> <?php esc_html_e( 'Confirmer automatiquement chaque nouvelle demande', 'dira-booking-files' ); ?></label></td></tr>
				<tr><th><?php esc_html_e( 'Annulation client', 'dira-booking-files' ); ?></th><td><label><input type="checkbox" name="allow_client_cancel" value="1" <?php checked( $settings['allow_client_cancel'], 1 ); ?>> <?php esc_html_e( 'Autoriser le client à annuler son rendez-vous en ligne', 'dira-booking-files' ); ?></label></td></tr>
			</table>
		</div>

		<div class="dira-bf-tab-panel" data-panel="notifications" hidden>
			<table class="form-table">
				<tr><th><?php esc_html_e( 'Email administrateur', 'dira-booking-files' ); ?></th><td><input type="email" class="regular-text" name="notify_admin_email" value="<?php echo esc_attr( $settings['notify_admin_email'] ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'Rappels automatiques', 'dira-booking-files' ); ?></th><td><label><input type="checkbox" name="reminders_enabled" value="1" <?php checked( $settings['reminders_enabled'], 1 ); ?>> <?php esc_html_e( 'Envoyer un rappel 24h et 2h avant le rendez-vous', 'dira-booking-files' ); ?></label></td></tr>
			</table>
		</div>

		<div class="dira-bf-tab-panel" data-panel="files" hidden>
			<table class="form-table">
				<tr><th><?php esc_html_e( 'Taille maximale (Mo)', 'dira-booking-files' ); ?></th><td><input type="number" name="max_upload_mb" value="<?php echo esc_attr( $settings['max_upload_mb'] ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'Extensions autorisées', 'dira-booking-files' ); ?></th><td><input type="text" class="regular-text" name="allowed_extensions" value="<?php echo esc_attr( implode( ', ', (array) $settings['allowed_extensions'] ) ); ?>"><p class="description"><?php esc_html_e( 'Séparées par des virgules.', 'dira-booking-files' ); ?></p></td></tr>
				<tr><th><?php esc_html_e( 'Limite de téléchargement par défaut', 'dira-booking-files' ); ?></th><td><input type="number" name="default_max_downloads" value="<?php echo esc_attr( $settings['default_max_downloads'] ); ?>"></td></tr>
				<tr><th><?php esc_html_e( 'Durée de validité des liens (jours)', 'dira-booking-files' ); ?></th><td><input type="number" name="default_link_days" value="<?php echo esc_attr( $settings['default_link_days'] ); ?>"></td></tr>
			</table>
		</div>

		<div class="dira-bf-tab-panel" data-panel="payment" hidden>
			<table class="form-table">
				<tr><th><?php esc_html_e( 'Passerelle par défaut', 'dira-booking-files' ); ?></th><td>
					<select name="payment_gateway">
						<?php foreach ( $gateways as $gateway ) : ?>
							<option value="<?php echo esc_attr( $gateway->get_id() ); ?>" <?php selected( $settings['payment_gateway'], $gateway->get_id() ); ?>><?php echo esc_html( $gateway->get_title() ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'WooCommerce apparaît automatiquement ici s\'il est actif. D\'autres passerelles (Mobile Money, API personnalisée…) peuvent être ajoutées sans modifier le reste de l\'extension.', 'dira-booking-files' ); ?></p>
				</td></tr>
			</table>
		</div>

		<div class="dira-bf-tab-panel" data-panel="advanced" hidden>
			<table class="form-table">
				<tr><th><?php esc_html_e( 'À la désinstallation', 'dira-booking-files' ); ?></th><td>
					<label><input type="checkbox" name="keep_data_on_uninstall" value="1" <?php checked( $settings['keep_data_on_uninstall'], 1 ); ?>> <?php esc_html_e( 'Conserver mes données si l\'extension est supprimée', 'dira-booking-files' ); ?></label>
					<p class="description"><?php esc_html_e( 'Décochez pour que la suppression complète de l\'extension efface aussi rendez-vous, clients, fichiers et commandes.', 'dira-booking-files' ); ?></p>
				</td></tr>
			</table>
		</div>

		<p><button class="button button-primary"><?php esc_html_e( 'Enregistrer les réglages', 'dira-booking-files' ); ?></button></p>
	</form>
</div>
