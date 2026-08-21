<?php
/**
 * Page 404.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="dr-page">
	<div class="dr-container" style="text-align:center;">
		<span class="dr-eyebrow">404</span>
		<h1><?php esc_html_e( 'Page introuvable', 'diradev' ); ?></h1>
		<p><?php esc_html_e( "La page que vous cherchez n'existe pas ou plus.", 'diradev' ); ?></p>
		<a class="dr-btn dr-btn-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( "Retour à l'accueil", 'diradev' ); ?></a>
	</div>
</main>
<?php get_footer(); ?>
