<?php
/**
 * Gabarit de page standard — utilisé notamment par les pages créées par
 * l'extension Dira Booking & Files (Rendez-vous, Œuvres, Mon compte…) et,
 * en repli, par la page d'accueil "Accueil" si Elementor n'est pas actif.
 * Pleinement compatible avec l'éditeur Elementor (appel à the_content()).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// La page d'accueil gère déjà son propre conteneur/titre section par section :
// pas de conteneur ni de <h1> générique par-dessus, pour ne pas casser sa mise en page pleine largeur.
$is_home = is_front_page();
?>
<main id="main" class="dr-page">
	<?php if ( $is_home ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	<?php else : ?>
		<div class="dr-container">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class(); ?>>
					<h1 class="entry-title"><?php the_title(); ?></h1>
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'large' ); ?>
					<?php endif; ?>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			endwhile;
			?>
		</div>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
