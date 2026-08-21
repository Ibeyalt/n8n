<?php
/**
 * Gabarit de secours (liste d'articles / résultats de recherche).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="dr-page">
	<div class="dr-container">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'dr-index-item' ); ?>>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p><?php echo esc_html( get_the_excerpt() ); ?></p>
				</article>
			<?php endwhile; ?>

			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p><?php esc_html_e( 'Aucun contenu à afficher pour le moment.', 'diradev' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
