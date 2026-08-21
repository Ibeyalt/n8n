<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$edit_id = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0;
$editing = $edit_id ? Dira_BF_Files::get( $edit_id ) : null;
$files      = Dira_BF_Files::query( array( 'include_drafts' => true, 'limit' => 200 ) );
$categories = Dira_BF_Files::get_categories();
?>
<div class="wrap dira-bf-admin">
	<h1><?php esc_html_e( 'Fichiers / Œuvres', 'dira-booking-files' ); ?></h1>

	<div class="dira-bf-columns">
		<div class="dira-bf-col-main">
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Titre', 'dira-booking-files' ); ?></th>
						<th><?php esc_html_e( 'Catégorie', 'dira-booking-files' ); ?></th>
						<th><?php esc_html_e( 'Prix', 'dira-booking-files' ); ?></th>
						<th><?php esc_html_e( 'Téléchargements', 'dira-booking-files' ); ?></th>
						<th><?php esc_html_e( 'Statut', 'dira-booking-files' ); ?></th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $files ) ) : ?>
					<tr><td colspan="6"><?php esc_html_e( 'Aucun fichier. Ajoutez-en un ci-contre.', 'dira-booking-files' ); ?></td></tr>
				<?php else : foreach ( $files as $file ) :
					$cat = null;
					foreach ( $categories as $c ) { if ( (int) $c->id === (int) $file->category_id ) { $cat = $c; break; } }
					?>
					<tr>
						<td><?php echo esc_html( $file->title ); ?></td>
						<td><?php echo esc_html( $cat->name ?? '—' ); ?></td>
						<td><?php echo $file->is_free ? esc_html__( 'Gratuit', 'dira-booking-files' ) : esc_html( Dira_BF_Settings::format_price( $file->price ) ); ?></td>
						<td><?php echo esc_html( $file->download_count ); ?></td>
						<td><span class="dira-bf-status dira-bf-status-<?php echo esc_attr( $file->status ); ?>"><?php echo esc_html( $file->status ); ?></span></td>
						<td>
							<a class="button button-small" href="<?php echo esc_url( add_query_arg( 'edit', $file->id ) ); ?>"><?php esc_html_e( 'Modifier', 'dira-booking-files' ); ?></a>
							<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=dira_bf_delete_file&id=' . $file->id ), 'dira_bf_delete_file' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Supprimer ce fichier ?', 'dira-booking-files' ) ); ?>');"><?php esc_html_e( 'Supprimer', 'dira-booking-files' ); ?></a>
						</td>
					</tr>
				<?php endforeach; endif; ?>
				</tbody>
			</table>
		</div>

		<div class="dira-bf-col-side">
			<div class="dira-bf-box">
				<h2><?php echo $editing ? esc_html__( 'Modifier le fichier', 'dira-booking-files' ) : esc_html__( 'Ajouter un fichier', 'dira-booking-files' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
					<?php wp_nonce_field( 'dira_bf_save_file' ); ?>
					<input type="hidden" name="action" value="dira_bf_save_file">
					<input type="hidden" name="id" value="<?php echo esc_attr( $editing->id ?? 0 ); ?>">

					<p><label><?php esc_html_e( 'Titre', 'dira-booking-files' ); ?><br>
					<input type="text" class="widefat" name="title" required value="<?php echo esc_attr( $editing->title ?? '' ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Description', 'dira-booking-files' ); ?><br>
					<textarea class="widefat" name="description" rows="3"><?php echo esc_textarea( $editing->description ?? '' ); ?></textarea></label></p>

					<p><label><?php esc_html_e( 'Fichier', 'dira-booking-files' ); ?><br>
					<input type="file" class="widefat" name="file_upload"></label>
					<?php if ( $editing && $editing->file_path ) : ?>
						<span class="description"><?php esc_html_e( 'Fichier actuel :', 'dira-booking-files' ); ?> <?php echo esc_html( $editing->file_path ); ?> (<?php echo esc_html( size_format( (int) $editing->file_size ) ); ?>)</span>
					<?php endif; ?>
					</p>

					<p><label><?php esc_html_e( 'Catégorie', 'dira-booking-files' ); ?><br>
					<select class="widefat" name="category_id">
						<option value=""><?php esc_html_e( '— Aucune —', 'dira-booking-files' ); ?></option>
						<?php foreach ( $categories as $cat ) : ?>
							<option value="<?php echo esc_attr( $cat->id ); ?>" <?php selected( $editing->category_id ?? 0, $cat->id ); ?>><?php echo esc_html( $cat->name ); ?></option>
						<?php endforeach; ?>
					</select></label></p>

					<p><label><?php esc_html_e( 'Auteur', 'dira-booking-files' ); ?><br>
					<input type="text" class="widefat" name="author" value="<?php echo esc_attr( $editing->author ?? '' ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Prix (0 = gratuit)', 'dira-booking-files' ); ?><br>
					<input type="number" step="0.01" min="0" class="widefat" name="price" value="<?php echo esc_attr( $editing->price ?? 0 ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Statut', 'dira-booking-files' ); ?><br>
					<select class="widefat" name="status">
						<option value="draft" <?php selected( $editing->status ?? 'draft', 'draft' ); ?>><?php esc_html_e( 'Brouillon', 'dira-booking-files' ); ?></option>
						<option value="publish" <?php selected( $editing->status ?? '', 'publish' ); ?>><?php esc_html_e( 'Publié', 'dira-booking-files' ); ?></option>
					</select></label></p>

					<p><label><?php esc_html_e( 'Nombre maximum de téléchargements (0 = valeur par défaut des réglages)', 'dira-booking-files' ); ?><br>
					<input type="number" min="0" class="widefat" name="max_downloads" value="<?php echo esc_attr( $editing->max_downloads ?? 0 ); ?>"></label></p>

					<p><label><?php esc_html_e( 'Durée de validité du lien en jours (0 = valeur par défaut)', 'dira-booking-files' ); ?><br>
					<input type="number" min="0" class="widefat" name="link_expiry_days" value="<?php echo esc_attr( $editing->link_expiry_days ?? 0 ); ?>"></label></p>

					<p><button class="button button-primary"><?php esc_html_e( 'Enregistrer', 'dira-booking-files' ); ?></button>
					<?php if ( $editing ) : ?>
						<a class="button" href="<?php echo esc_url( remove_query_arg( 'edit' ) ); ?>"><?php esc_html_e( 'Annuler', 'dira-booking-files' ); ?></a>
					<?php endif; ?>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>
