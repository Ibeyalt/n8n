<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$categories = Dira_BF_Files::get_categories();
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Catégories de fichiers', 'dira-booking-files' ); ?></h1>

	<div class="dira-bf-columns">
		<div class="dira-bf-col-main">
			<table class="widefat striped">
				<thead><tr><th><?php esc_html_e( 'Nom', 'dira-booking-files' ); ?></th><th><?php esc_html_e( 'Slug', 'dira-booking-files' ); ?></th><th></th></tr></thead>
				<tbody>
				<?php if ( empty( $categories ) ) : ?>
					<tr><td colspan="3"><?php esc_html_e( 'Aucune catégorie.', 'dira-booking-files' ); ?></td></tr>
				<?php else : foreach ( $categories as $cat ) : ?>
					<tr>
						<td><?php echo esc_html( $cat->name ); ?></td>
						<td><?php echo esc_html( $cat->slug ); ?></td>
						<td><a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dira_bf_delete_category&id=' . $cat->id ), 'dira_bf_delete_category' ) ); ?>"><?php esc_html_e( 'Supprimer', 'dira-booking-files' ); ?></a></td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>
		<div class="dira-bf-col-side">
			<div class="dira-bf-box">
				<h2><?php esc_html_e( 'Ajouter une catégorie', 'dira-booking-files' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'dira_bf_save_category' ); ?>
					<input type="hidden" name="action" value="dira_bf_save_category">
					<p><input type="text" class="widefat" name="name" required placeholder="<?php esc_attr_e( 'ex. Guides', 'dira-booking-files' ); ?>"></p>
					<p><button class="button button-primary"><?php esc_html_e( 'Ajouter', 'dira-booking-files' ); ?></button></p>
				</form>
			</div>
		</div>
	</div>
</div>
