<?php
/**
 * Gabarit d'un article de blog.
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
				<span class="dr-eyebrow"><?php echo esc_html( get_the_date() ); ?></span>
				<h1 class="entry-title"><?php the_title(); ?></h1>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="dr-single-thumb"><?php the_post_thumbnail( 'large' ); ?></div>
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
