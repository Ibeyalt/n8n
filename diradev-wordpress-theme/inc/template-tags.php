<?php
/**
 * Fonctions utilitaires réutilisées dans les templates.
 * Toutes les intégrations avec l'extension Dira Booking & Files sont
 * protégées par class_exists() : le thème fonctionne sans elle.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * URL de la page de réservation créée par l'extension, avec repli sur l'ancre #contact.
 */
function diradev_booking_url() {
	$pages = get_option( 'dira_bf_pages', array() );
	if ( ! empty( $pages['booking'] ) && get_post( $pages['booking'] ) ) {
		return get_permalink( $pages['booking'] );
	}
	return home_url( '/#contact' );
}

/**
 * URL de la page publique des fichiers/œuvres créée par l'extension.
 */
function diradev_files_url() {
	$pages = get_option( 'dira_bf_pages', array() );
	if ( ! empty( $pages['files'] ) && get_post( $pages['files'] ) ) {
		return get_permalink( $pages['files'] );
	}
	return home_url( '/' );
}

/**
 * Coordonnées de l'entreprise : reprend les réglages de l'extension si elle est active,
 * sinon des valeurs de repli neutres.
 */
function diradev_company( $key, $fallback = '' ) {
	if ( class_exists( 'Dira_BF_Settings' ) ) {
		$value = Dira_BF_Settings::get( 'company_' . $key, '' );
		if ( $value ) {
			return $value;
		}
	}
	return $fallback;
}

/**
 * Menu de secours affiché tant qu'aucun menu WordPress n'est assigné à l'emplacement "primary".
 */
function diradev_nav_fallback() {
	echo '<ul id="primary-menu" class="dr-nav-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Accueil', 'diradev' ) . '</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/#services' ) ) . '">' . esc_html__( 'Services', 'diradev' ) . '</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/#process' ) ) . '">' . esc_html__( 'Notre process', 'diradev' ) . '</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/#contact' ) ) . '">' . esc_html__( 'Contact', 'diradev' ) . '</a></li>';
	echo '</ul>';
}

/**
 * Icônes SVG inline (trait, 24px) utilisées dans les templates. Jamais d'emoji.
 */
function diradev_icon( $name ) {
	$icons = array(
		'code'   => '<rect x="3" y="4" width="18" height="14" rx="2"></rect><path d="M8 21h8M12 18v3"></path>',
		'chart'  => '<path d="M3 3v18h18"></path><path d="M7 15l4-5 3 3 5-7"></path>',
		'robot'  => '<rect x="4" y="8" width="16" height="12" rx="2"></rect><path d="M8 8V6a4 4 0 0 1 8 0v2"></path><circle cx="9" cy="14" r="1"></circle><circle cx="15" cy="14" r="1"></circle>',
		'globe'  => '<path d="M21 8l-9-5-9 5 9 5 9-5z"></path><path d="M3 8v8l9 5 9-5V8"></path><path d="M12 13v8"></path>',
		'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"></path>',
		'arrow-diag' => '<path d="M7 17L17 7M7 7h10v10"></path>',
		'plus'   => '<path d="M12 4v16M4 12h16"></path>',
		'check'  => '<path d="M20 6L9 17l-5-5"></path>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	return '<svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">' . $icons[ $name ] . '</svg>';
}
