<?php
/**
 * Fiche publique d'un fichier (œuvre/document).
 * Variable : $file.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$category = $file->category_id ? null : null;
if ( $file->category_id ) {
	foreach ( Dira_BF_Files::get_categories() as $cat ) {
		if ( (int) $cat->id === (int) $file->category_id ) { $category = $cat; break; }
	}
}
?>
<div class="dira-files-app dira-files-single">
	<article class="dira-files-single-card">
		<div class="dira-files-single-cover">
			<?php if ( $file->cover_image_id && wp_get_attachment_image_url( $file->cover_image_id, 'large' ) ) : ?>
				<img src="<?php echo esc_url( wp_get_attachment_image_url( $file->cover_image_id, 'large' ) ); ?>" alt="<?php echo esc_attr( $file->title ); ?>">
			<?php else : ?>
				<span class="dira-files-cover-placeholder" aria-hidden="true"><?php echo esc_html( strtoupper( substr( $file->file_type ?: '?', 0, 3 ) ) ); ?></span>
			<?php endif; ?>
		</div>
		<div class="dira-files-single-body">
			<h1 class="dira-files-single-title"><?php echo esc_html( $file->title ); ?></h1>
			<p class="dira-files-single-meta">
				<?php if ( $file->author ) : ?><span><?php esc_html_e( 'Auteur :', 'dira-booking-files' ); ?> <?php echo esc_html( $file->author ); ?></span><?php endif; ?>
				<?php if ( $category ) : ?><span><?php echo esc_html( $category->name ); ?></span><?php endif; ?>
				<?php if ( $file->file_type ) : ?><span><?php echo esc_html( strtoupper( $file->file_type ) ); ?></span><?php endif; ?>
				<?php if ( $file->file_size ) : ?><span><?php echo esc_html( size_format( (int) $file->file_size ) ); ?></span><?php endif; ?>
				<span><?php echo esc_html( sprintf( _n( '%d téléchargement', '%d téléchargements', (int) $file->download_count, 'dira-booking-files' ), $file->download_count ) ); ?></span>
			</p>
			<p class="dira-files-single-desc"><?php echo esc_html( $file->description ); ?></p>

			<?php if ( $file->is_free ) : ?>
				<button type="button" class="dira-files-btn" data-action="download" data-file-id="<?php echo esc_attr( $file->id ); ?>"><?php esc_html_e( 'Télécharger', 'dira-booking-files' ); ?></button>
			<?php else : ?>
				<p class="dira-files-single-price"><?php echo esc_html( Dira_BF_Settings::format_price( $file->price ) ); ?></p>
				<button type="button" class="dira-files-btn dira-files-btn-buy" data-action="buy" data-file-id="<?php echo esc_attr( $file->id ); ?>" data-file-title="<?php echo esc_attr( $file->title ); ?>"><?php esc_html_e( 'Acheter maintenant', 'dira-booking-files' ); ?></button>
			<?php endif; ?>
		</div>
	</article>

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
