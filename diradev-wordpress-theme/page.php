<?php
/**
 * Gabarit de page standard — utilisé notamment par les pages créées par
 * l'extension Dira Booking & Files (Rendez-vous, Œuvres, Mon compte…)
 * et pleinement compatible avec l'éditeur Elementor.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<main id="main" class="dr-page">
	<div class="dr-container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<?php if ( ! has_post_thumbnail() ) : ?>
					<h1 class="entry-title"><?php the_title(); ?></h1>
				<?php else : ?>
					<h1 class="entry-title"><?php the_title(); ?></h1>
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
</main>
<?php get_footer(); ?>
