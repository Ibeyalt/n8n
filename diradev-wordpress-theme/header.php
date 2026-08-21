<?php
/**
 * En-tête du thème DiraDev.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="dr-header">
	<div class="dr-container dr-header-inner">
		<a class="dr-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<span class="dr-logo-mark"><?php the_custom_logo(); ?></span>
			<?php else : ?>
				<span class="dr-logo-mark"><?php echo esc_html( mb_substr( get_bloginfo( 'name' ) ?: 'D', 0, 1 ) ); ?></span>
			<?php endif; ?>
			<span class="dr-logo-text"><?php bloginfo( 'name' ); ?></span>
		</a>

		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'dr-nav-menu',
				'menu_id'        => 'primary-menu',
				'fallback_cb'    => 'diradev_nav_fallback',
			)
		);
		?>

		<a class="dr-btn dr-btn-primary dr-header-cta" href="<?php echo esc_url( diradev_booking_url() ); ?>"><?php esc_html_e( 'Démarrer un projet', 'diradev' ); ?></a>

		<button type="button" class="dr-nav-toggle" aria-expanded="false" aria-controls="primary-menu" aria-label="<?php esc_attr_e( 'Ouvrir le menu', 'diradev' ); ?>">
			<span></span><span></span>
		</button>
	</div>
</header>
