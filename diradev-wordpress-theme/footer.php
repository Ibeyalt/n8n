<?php
/**
 * Pied de page du thème DiraDev.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$phone = diradev_company( 'phone', '' );
$email = diradev_company( 'email', get_bloginfo( 'admin_email' ) );
?>
	<footer class="dr-footer">
		<div class="dr-container">
			<div class="dr-footer-top">
				<div class="dr-footer-about">
					<a class="dr-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<span class="dr-logo-mark"><?php echo esc_html( mb_substr( get_bloginfo( 'name' ) ?: 'D', 0, 1 ) ); ?></span>
						<span class="dr-logo-text"><?php bloginfo( 'name' ); ?></span>
					</a>
					<p><?php esc_html_e( "Sites web et applications, marketing digital, agents IA et sourcing produit international — une seule agence pour faire avancer votre entreprise.", 'diradev' ); ?></p>
				</div>

				<div class="dr-footer-columns">
					<div class="dr-footer-col">
						<h5><?php esc_html_e( 'Navigation', 'diradev' ); ?></h5>
						<?php
						wp_nav_menu(
							array(
								'theme_location' => 'footer',
								'container'      => false,
								'menu_class'     => '',
								'fallback_cb'    => 'diradev_nav_fallback',
								'items_wrap'     => '<ul>%3$s</ul>',
							)
						);
						?>
					</div>

					<div class="dr-footer-col">
						<h5><?php esc_html_e( 'Services', 'diradev' ); ?></h5>
						<ul>
							<li><a href="<?php echo esc_url( home_url( '/#services' ) ); ?>"><?php esc_html_e( 'Sites & Applications', 'diradev' ); ?></a></li>
							<li><a href="<?php echo esc_url( home_url( '/#services' ) ); ?>"><?php esc_html_e( 'Marketing Digital', 'diradev' ); ?></a></li>
							<li><a href="<?php echo esc_url( home_url( '/#services' ) ); ?>"><?php esc_html_e( 'Agents IA', 'diradev' ); ?></a></li>
							<li><a href="<?php echo esc_url( home_url( '/#services' ) ); ?>"><?php esc_html_e( 'Sourcing International', 'diradev' ); ?></a></li>
						</ul>
					</div>

					<div class="dr-footer-col">
						<h5><?php esc_html_e( 'Contact', 'diradev' ); ?></h5>
						<ul>
							<li><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
							<?php if ( $phone ) : ?><li><a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li><?php else : ?><li><span><?php esc_html_e( '[votre téléphone]', 'diradev' ); ?></span></li><?php endif; ?>
							<li><a class="dr-footer-cta-link" href="<?php echo esc_url( diradev_booking_url() ); ?>"><?php esc_html_e( 'Démarrer un projet →', 'diradev' ); ?></a></li>
						</ul>
					</div>
				</div>
			</div>

			<div class="dr-footer-bottom">
				<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> DIRAHOLDINGS LLC. <?php esc_html_e( 'Tous droits réservés.', 'diradev' ); ?></span>
				<span><?php esc_html_e( 'DIRADEV est une marque de DIRAHOLDINGS LLC', 'diradev' ); ?></span>
			</div>
		</div>
	</footer>

<?php wp_footer(); ?>
</body>
</html>
