<?php
/**
 * DiraDev — fonctions du thème.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DIRADEV_VERSION', '1.1.0' );
define( 'DIRADEV_DIR', get_template_directory() );
define( 'DIRADEV_URI', get_template_directory_uri() );

/**
 * Réglages de base du thème.
 */
function diradev_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 60, 'width' => 60, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	register_nav_menus(
		array(
			'primary' => __( 'Menu principal', 'diradev' ),
			'footer'  => __( 'Menu du pied de page', 'diradev' ),
		)
	);
}
add_action( 'after_setup_theme', 'diradev_setup' );

/**
 * Styles et scripts.
 */
function diradev_scripts() {
	wp_enqueue_style( 'diradev-fonts', 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap', array(), null );
	wp_enqueue_style( 'diradev-style', get_stylesheet_uri(), array(), DIRADEV_VERSION );
	wp_enqueue_script( 'diradev-main', DIRADEV_URI . '/assets/js/main.js', array(), DIRADEV_VERSION, true );

	if ( is_singular() ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'diradev_scripts' );

require DIRADEV_DIR . '/inc/template-tags.php';
require DIRADEV_DIR . '/inc/homepage-content.php';
require DIRADEV_DIR . '/inc/homepage-setup.php';

/**
 * Crée un menu principal par défaut à l'activation, sans jamais écraser
 * un menu déjà configuré par l'administrateur.
 */
function diradev_after_switch_theme() {
	if ( has_nav_menu( 'primary' ) ) {
		return;
	}

	$existing = wp_get_nav_menu_object( __( 'Menu principal DiraDev', 'diradev' ) );
	$menu_id  = $existing ? $existing->term_id : wp_create_nav_menu( __( 'Menu principal DiraDev', 'diradev' ) );

	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	$items = array(
		array( 'title' => __( 'Accueil', 'diradev' ), 'url' => home_url( '/' ) ),
		array( 'title' => __( 'Services', 'diradev' ), 'url' => home_url( '/#services' ) ),
		array( 'title' => __( 'Notre process', 'diradev' ), 'url' => home_url( '/#process' ) ),
		array( 'title' => __( 'Contact', 'diradev' ), 'url' => home_url( '/#contact' ) ),
	);

	if ( empty( wp_get_nav_menu_items( $menu_id ) ) ) {
		foreach ( $items as $item ) {
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'  => $item['title'],
					'menu-item-url'    => $item['url'],
					'menu-item-status' => 'publish',
				)
			);
		}
	}

	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary']   = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}
add_action( 'after_switch_theme', 'diradev_after_switch_theme' );

/**
 * Le thème ne doit jamais planter si l'extension Dira Booking & Files est désactivée :
 * toutes les intégrations passent par des fonctions "diradev_*" qui vérifient
 * class_exists()/function_exists() avant d'appeler quoi que ce soit côté extension.
 */
