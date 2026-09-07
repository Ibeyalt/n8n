<?php
/**
 * Grille publique des fichiers (œuvres/documents).
 * Variables : $files, $categories, $atts.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
?>
<div class="dira-files-app">

	<form class="dira-files-toolbar" data-role="filters">
		<input type="search" name="search" class="dira-files-search" placeholder="<?php esc_attr_e( 'Rechercher un fichier…', 'dira-booking-files' ); ?>">

		<select name="category">
			<option value=""><?php esc_html_e( 'Toutes les catégories', 'dira-booking-files' ); ?></option>
			<?php foreach ( $categories as $cat ) : ?>
				<option value="<?php echo esc_attr( $cat->id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
			<?php endforeach; ?>
		</select>

		<select name="type">
			<option value=""><?php esc_html_e( 'Tous', 'dira-booking-files' ); ?></option>
			<option value="free"><?php esc_html_e( 'Gratuit', 'dira-booking-files' ); ?></option>
			<option value="paid"><?php esc_html_e( 'Payant', 'dira-booking-files' ); ?></option>
		</select>

		<select name="orderby">
			<option value="recent"><?php esc_html_e( 'Nouveautés', 'dira-booking-files' ); ?></option>
			<option value="popular"><?php esc_html_e( 'Populaires', 'dira-booking-files' ); ?></option>
		</select>
	</form>

	<div class="dira-files-grid" data-role="grid">
		<?php foreach ( $files as $file ) : ?>
			<article class="dira-files-card" data-category="<?php echo esc_attr( $file->category_id ); ?>" data-type="<?php echo $file->is_free ? 'free' : 'paid'; ?>" data-title="<?php echo esc_attr( strtolower( $file->title . ' ' . $file->description ) ); ?>" data-downloads="<?php echo esc_attr( $file->download_count ); ?>" data-created="<?php echo esc_attr( strtotime( $file->created_at ) ); ?>">
				<div class="dira-files-cover">
					<?php if ( $file->cover_image_id && wp_get_attachment_image_url( $file->cover_image_id, 'medium' ) ) : ?>
						<img src="<?php echo esc_url( wp_get_attachment_image_url( $file->cover_image_id, 'medium' ) ); ?>" alt="<?php echo esc_attr( $file->title ); ?>">
					<?php else : ?>
						<span class="dira-files-cover-placeholder" aria-hidden="true"><?php echo esc_html( strtoupper( substr( $file->file_type ?: '?', 0, 3 ) ) ); ?></span>
					<?php endif; ?>
					<span class="dira-files-badge <?php echo $file->is_free ? 'is-free' : 'is-paid'; ?>"><?php echo $file->is_free ? esc_html__( 'Gratuit', 'dira-booking-files' ) : esc_html( Dira_BF_Settings::format_price( $file->price ) ); ?></span>
				</div>
				<div class="dira-files-body">
					<h3 class="dira-files-title"><?php echo esc_html( $file->title ); ?></h3>
					<p class="dira-files-desc"><?php echo esc_html( wp_trim_words( $file->description, 18 ) ); ?></p>
					<p class="dira-files-meta">
						<?php if ( $file->file_type ) : ?><span><?php echo esc_html( strtoupper( $file->file_type ) ); ?></span><?php endif; ?>
						<?php if ( $file->file_size ) : ?><span><?php echo esc_html( size_format( (int) $file->file_size ) ); ?></span><?php endif; ?>
					</p>
					<?php if ( $file->is_free ) : ?>
						<button type="button" class="dira-files-btn" data-action="download" data-file-id="<?php echo esc_attr( $file->id ); ?>"><?php esc_html_e( 'Télécharger', 'dira-booking-files' ); ?></button>
					<?php else : ?>
						<button type="button" class="dira-files-btn dira-files-btn-buy" data-action="buy" data-file-id="<?php echo esc_attr( $file->id ); ?>" data-file-title="<?php echo esc_attr( $file->title ); ?>"><?php esc_html_e( 'Acheter', 'dira-booking-files' ); ?></button>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>

		<?php if ( empty( $files ) ) : ?>
			<p class="dira-files-empty"><?php esc_html_e( 'Aucun fichier disponible pour le moment.', 'dira-booking-files' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="dira-files-modal" data-role="buy-modal" hidden>
		<div class="dira-files-modal-content">
			<button type="button" class="dira-files-modal-close" data-action="close-modal">&times;</button>
			<h3 data-role="buy-title"></h3>
			<div class="dira-files-alert" data-role="buy-alert" hidden></div>
			<label class="dira-files-field"><span><?php esc_html_e( 'Prénom', 'dira-booking-files' ); ?></span><input type="text" data-field="first_name"></label>
			<label class="dira-files-field"><span><?php esc_html_e( 'Nom', 'dira-booking-files' ); ?></span><input type="text" data-field="last_name"></label>
			<label class="dira-files-field"><span><?php esc_html_e( 'Email', 'dira-booking-files' ); ?> *</span><input type="email" data-field="email" required></label>
			<label class="dira-files-field"><span><?php esc_html_e( 'Téléphone', 'dira-booking-files' ); ?></span><input type="tel" data-field="phone"></label>
			<button type="button" class="dira-files-btn dira-files-btn-buy" data-action="confirm-buy"><?php esc_html_e( 'Acheter maintenant', 'dira-booking-files' ); ?></button>
		</div>
	</div>

</div>
