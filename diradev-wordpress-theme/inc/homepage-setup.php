<?php
/**
 * Crée automatiquement la page "Accueil" à l'activation du thème et la configure
 * pour être :
 * 1. Affichée comme page d'accueil du site (Réglages → Lecture) ;
 * 2. Éditable visuellement dans Elementor (données _elementor_data + gabarit
 *    "Elementor Full Width", qui conserve l'en-tête/pied de page du thème) ;
 * 3. Correcte même si Elementor n'est pas installé : le contenu réel de la page
 *    (post_content) contient déjà le même rendu, affiché via page.php.
 *
 * Ne s'exécute qu'une seule fois (option "diradev_homepage_configured") pour ne
 * jamais écraser un travail déjà fait dans Elementor par l'administrateur.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function diradev_elementor_id() {
	return substr( md5( uniqid( (string) wp_rand(), true ) ), 0, 7 );
}

/**
 * Construit le contenu de repli (blocs HTML personnalisés) affiché par page.php
 * quand la page n'est pas (ou plus) éditée avec Elementor.
 */
function diradev_build_fallback_content( array $sections ) {
	$out = '';
	foreach ( $sections as $html ) {
		$out .= "<!-- wp:html -->\n" . $html . "\n<!-- /wp:html -->\n\n";
	}
	return $out;
}

/**
 * Construit la structure Elementor (sections → colonne pleine largeur → widget HTML)
 * à partir des mêmes fragments, pour rester éditable dans le constructeur.
 */
function diradev_build_elementor_data( array $sections ) {
	$data = array();

	foreach ( $sections as $html ) {
		$data[] = array(
			'id'       => diradev_elementor_id(),
			'elType'   => 'section',
			'settings' => array(),
			'elements' => array(
				array(
					'id'       => diradev_elementor_id(),
					'elType'   => 'column',
					'settings' => array( '_column_size' => 100 ),
					'isInner'  => false,
					'elements' => array(
						array(
							'id'         => diradev_elementor_id(),
							'elType'     => 'widget',
							'widgetType' => 'html',
							'settings'   => array( 'html' => $html ),
							'elements'   => array(),
						),
					),
				),
			),
			'isInner'  => false,
		);
	}

	return $data;
}

function diradev_setup_homepage() {
	if ( get_option( 'diradev_homepage_configured' ) ) {
		return;
	}

	if ( ! function_exists( 'diradev_homepage_sections' ) ) {
		require_once DIRADEV_DIR . '/inc/homepage-content.php';
	}

	$title = __( 'Accueil', 'diradev' );

	$query = new WP_Query(
		array(
			'post_type'              => 'page',
			'title'                  => $title,
			'posts_per_page'         => 1,
			'post_status'            => array( 'publish', 'draft', 'private' ),
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	$page_id = $query->have_posts() ? $query->posts[0]->ID : 0;

	$sections = diradev_homepage_sections();
	$content  = diradev_build_fallback_content( $sections );

	if ( $page_id ) {
		wp_update_post(
			array(
				'ID'           => $page_id,
				'post_content' => $content,
				'post_status'  => 'publish',
			)
		);
	} else {
		$page_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
			)
		);
	}

	if ( ! $page_id || is_wp_error( $page_id ) ) {
		return;
	}

	// Gabarit "Elementor Full Width" : conserve le header.php / footer.php du thème
	// autour du contenu Elementor. Si Elementor n'est pas actif, WordPress ignore
	// ce gabarit inconnu et retombe proprement sur page.php (aucune erreur).
	update_post_meta( $page_id, '_wp_page_template', 'elementor_header_footer' );

	// Données Elementor : la page reste éditable en glisser-déposer (sections,
	// réorganisation, ajout de nouveaux widgets) sans dépendre de front-page.php.
	$elementor_data = diradev_build_elementor_data( $sections );
	update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $elementor_data ) ) );
	update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $page_id, '_elementor_version', '3.25.0' );

	// Définit cette page comme page d'accueil du site.
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $page_id );

	update_option( 'diradev_homepage_id', $page_id );
	update_option( 'diradev_homepage_configured', 1 );
}
add_action( 'after_switch_theme', 'diradev_setup_homepage', 20 );
