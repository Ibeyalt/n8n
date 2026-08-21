<?php
/**
 * Page d'accueil DiraDev.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Services : repris de l'extension Dira Booking & Files si elle est active et configurée, sinon contenu de repli.
$services = array();
if ( class_exists( 'Dira_BF_Services' ) ) {
	foreach ( Dira_BF_Services::get_all( true ) as $service ) {
		$services[] = array(
			'name'        => $service->name,
			'description' => $service->description,
		);
	}
}
if ( empty( $services ) ) {
	$services = array(
		array( 'name' => __( 'Sites Web & Applications', 'diradev' ), 'description' => __( 'Conception et développement de sites vitrines, e-commerce et applications sur mesure.', 'diradev' ) ),
		array( 'name' => __( 'Marketing Digital', 'diradev' ), 'description' => __( 'Stratégies SEO, publicité et réseaux sociaux pour développer votre visibilité.', 'diradev' ) ),
		array( 'name' => __( 'Agents IA & Automatisation', 'diradev' ), 'description' => __( 'Des agents IA sur mesure qui automatisent vos tâches répétitives.', 'diradev' ) ),
		array( 'name' => __( 'Sourcing Produit International', 'diradev' ), 'description' => __( 'Recherche, négociation et gestion de votre sourcing produit à l\'international.', 'diradev' ) ),
	);
}
?>

<main id="main">

	<!-- Héros -->
	<section class="dr-hero">
		<div class="dr-container dr-hero-inner">
			<div class="dr-hero-copy dr-reveal">
				<span class="dr-eyebrow"><?php esc_html_e( 'Agence digitale & automatisation IA', 'diradev' ); ?></span>
				<h1><?php esc_html_e( 'Votre croissance, propulsée par le', 'diradev' ); ?> <span class="dr-accent-text"><?php esc_html_e( 'digital', 'diradev' ); ?></span> <?php esc_html_e( 'et l\'', 'diradev' ); ?><span class="dr-accent-text"><?php esc_html_e( 'IA', 'diradev' ); ?></span></h1>
				<p class="dr-hero-lead"><?php esc_html_e( 'DiraDev conçoit vos sites et applications, pilote votre marketing digital, automatise vos tâches avec des agents IA sur mesure, et sécurise votre sourcing produit à l\'international.', 'diradev' ); ?></p>
				<div class="dr-hero-actions">
					<a class="dr-btn dr-btn-primary" href="<?php echo esc_url( diradev_booking_url() ); ?>"><?php esc_html_e( 'Démarrer un projet', 'diradev' ); ?></a>
					<a class="dr-btn dr-btn-ghost" href="#services"><?php esc_html_e( 'Voir nos services', 'diradev' ); ?></a>
				</div>
			</div>

			<div class="dr-hero-visual dr-reveal">
				<div class="dr-hero-glow"></div>
				<div class="dr-hero-ring"></div>
				<div class="dr-hero-photo">
					<img src="<?php echo esc_url( DIRADEV_URI . '/assets/images/founder.jpg' ); ?>" alt="<?php esc_attr_e( 'Fondateur DiraDev', 'diradev' ); ?>">
				</div>
				<div class="dr-hero-chip dr-hero-chip-1"><?php echo diradev_icon( 'robot' ); // phpcs:ignore ?></div>
				<div class="dr-hero-chip dr-hero-chip-2"><?php echo diradev_icon( 'code' ); // phpcs:ignore ?></div>
				<div class="dr-hero-chip dr-hero-chip-3"><?php echo diradev_icon( 'globe' ); // phpcs:ignore ?></div>
				<div class="dr-hero-chip dr-hero-chip-4"><?php echo diradev_icon( 'chart' ); // phpcs:ignore ?></div>
				<div class="dr-hero-badge">
					<span class="dr-dot"></span>
					<span class="dr-label"><?php esc_html_e( '4 expertises réunies', 'diradev' ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<!-- Pôles d'expertise -->
	<div class="dr-container">
		<a href="#services" class="dr-pillars dr-reveal">
			<span class="dr-pillar"><?php echo diradev_icon( 'code' ); // phpcs:ignore ?><span><span class="dr-pillar-name"><?php esc_html_e( 'Sites & Applications', 'diradev' ); ?></span><br><span class="dr-pillar-meta"><?php esc_html_e( 'Web & mobile sur mesure', 'diradev' ); ?></span></span></span>
			<span class="dr-pillar"><?php echo diradev_icon( 'chart' ); // phpcs:ignore ?><span><span class="dr-pillar-name"><?php esc_html_e( 'Marketing Digital', 'diradev' ); ?></span><br><span class="dr-pillar-meta"><?php esc_html_e( 'Visibilité & croissance', 'diradev' ); ?></span></span></span>
			<span class="dr-pillar"><?php echo diradev_icon( 'robot' ); // phpcs:ignore ?><span><span class="dr-pillar-name"><?php esc_html_e( 'Agents IA', 'diradev' ); ?></span><br><span class="dr-pillar-meta"><?php esc_html_e( 'Automatisation des tâches', 'diradev' ); ?></span></span></span>
			<span class="dr-pillar"><?php echo diradev_icon( 'globe' ); // phpcs:ignore ?><span><span class="dr-pillar-name"><?php esc_html_e( 'Sourcing International', 'diradev' ); ?></span><br><span class="dr-pillar-meta"><?php esc_html_e( 'Produits & fournisseurs', 'diradev' ); ?></span></span></span>
		</a>
	</div>

	<!-- Mission -->
	<section class="dr-section">
		<div class="dr-container dr-mission">
			<div class="dr-mission-copy dr-reveal">
				<span class="dr-eyebrow"><?php esc_html_e( 'Notre mission', 'diradev' ); ?></span>
				<h2><?php esc_html_e( 'Une agence, quatre expertises, un seul objectif', 'diradev' ); ?></h2>
				<p><?php esc_html_e( 'Chez DiraDev, nous réunissons développement, marketing, intelligence artificielle et sourcing sous un même toit pour que votre entreprise avance plus vite, sans multiplier les prestataires.', 'diradev' ); ?></p>
			</div>
			<div class="dr-mission-panels dr-reveal">
				<div class="dr-mission-panel a">
					<span class="dr-mission-tag"><?php esc_html_e( 'Développement Web & App', 'diradev' ); ?></span>
				</div>
				<div class="dr-mission-panel b">
					<?php echo diradev_icon( 'robot' ); // phpcs:ignore ?>
					<span class="dr-mission-tag"><?php esc_html_e( 'Automatisation IA', 'diradev' ); ?></span>
				</div>
			</div>
		</div>
	</section>

	<!-- Services -->
	<section class="dr-section" id="services">
		<div class="dr-container">
			<div class="dr-services-head dr-reveal">
				<div>
					<span class="dr-eyebrow"><?php esc_html_e( 'Nos services', 'diradev' ); ?></span>
					<h2><?php esc_html_e( 'Quatre expertises pour faire grandir votre entreprise', 'diradev' ); ?></h2>
				</div>
				<p><?php esc_html_e( 'Des solutions pensées ensemble, du premier prototype à l\'automatisation de vos opérations.', 'diradev' ); ?></p>
			</div>

			<div class="dr-services-layout">
				<div class="dr-services-list dr-reveal">
					<?php foreach ( $services as $i => $service ) : ?>
						<a class="dr-service-row" href="<?php echo esc_url( diradev_booking_url() ); ?>">
							<span class="dr-service-left">
								<span class="dr-service-num"><?php echo esc_html( str_pad( $i + 1, 2, '0', STR_PAD_LEFT ) ); ?></span>
								<span class="dr-service-name"><?php echo esc_html( $service['name'] ); ?></span>
							</span>
							<span class="dr-service-arrow"><?php echo diradev_icon( 'arrow' ); // phpcs:ignore ?></span>
						</a>
					<?php endforeach; ?>
				</div>

				<div class="dr-services-cards dr-reveal">
					<a class="dr-service-callout light" href="<?php echo esc_url( diradev_booking_url() ); ?>">
						<div>
							<h4><?php esc_html_e( 'Une idée de projet ?', 'diradev' ); ?></h4>
							<p><?php esc_html_e( 'Parlons de vos objectifs et voyons comment DiraDev peut vous aider à les atteindre.', 'diradev' ); ?></p>
						</div>
						<span class="dr-service-callout-icon"><?php echo diradev_icon( 'arrow-diag' ); // phpcs:ignore ?></span>
					</a>
					<a class="dr-service-callout accent" href="<?php echo esc_url( diradev_booking_url() ); ?>">
						<div>
							<h4><?php esc_html_e( 'Besoin d\'un agent IA ?', 'diradev' ); ?></h4>
							<p><?php esc_html_e( 'Automatisez vos tâches répétitives et gagnez un temps précieux au quotidien.', 'diradev' ); ?></p>
						</div>
						<span class="dr-service-callout-icon"><?php echo diradev_icon( 'arrow-diag' ); // phpcs:ignore ?></span>
					</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Slogan -->
	<div class="dr-tagline">
		<span class="word dr-reveal"><?php esc_html_e( 'Innover', 'diradev' ); ?></span>
		<?php echo diradev_icon( 'plus' ); // phpcs:ignore ?>
		<span class="word dr-reveal"><?php esc_html_e( 'Automatiser', 'diradev' ); ?></span>
		<?php echo diradev_icon( 'plus' ); // phpcs:ignore ?>
		<span class="word dr-reveal"><?php esc_html_e( 'Croître', 'diradev' ); ?></span>
	</div>

	<!-- Process -->
	<section class="dr-section" id="process">
		<div class="dr-container">
			<div class="dr-process-head dr-reveal">
				<span class="dr-eyebrow"><?php esc_html_e( 'Notre process', 'diradev' ); ?></span>
				<h2><?php esc_html_e( 'Comment nous travaillons ensemble', 'diradev' ); ?></h2>
			</div>
			<div class="dr-process-grid">
				<div class="dr-process-card dr-reveal">
					<div class="dr-process-num">01</div>
					<h4><?php esc_html_e( 'Découverte', 'diradev' ); ?></h4>
					<p><?php esc_html_e( 'On échange sur vos objectifs, vos contraintes et vos priorités.', 'diradev' ); ?></p>
				</div>
				<div class="dr-process-card dr-reveal">
					<div class="dr-process-num">02</div>
					<h4><?php esc_html_e( 'Stratégie', 'diradev' ); ?></h4>
					<p><?php esc_html_e( 'Nous définissons la solution et la feuille de route adaptées.', 'diradev' ); ?></p>
				</div>
				<div class="dr-process-card dr-reveal">
					<div class="dr-process-num">03</div>
					<h4><?php esc_html_e( 'Réalisation', 'diradev' ); ?></h4>
					<p><?php esc_html_e( 'Développement, création ou automatisation, avec des points réguliers.', 'diradev' ); ?></p>
				</div>
				<div class="dr-process-card dr-reveal">
					<div class="dr-process-num">04</div>
					<h4><?php esc_html_e( 'Suivi', 'diradev' ); ?></h4>
					<p><?php esc_html_e( 'On reste à vos côtés pour ajuster et faire évoluer votre solution.', 'diradev' ); ?></p>
				</div>
			</div>
		</div>
	</section>

	<!-- Appel à l'action -->
	<div class="dr-container" id="contact">
		<div class="dr-cta dr-reveal">
			<div class="dr-cta-blob"></div>
			<div class="dr-cta-content">
				<h2><?php esc_html_e( 'Prêt à passer au niveau supérieur ?', 'diradev' ); ?></h2>
				<p><?php esc_html_e( 'Discutons de votre site, de votre application, de votre stratégie marketing ou de votre agent IA sur mesure.', 'diradev' ); ?></p>
				<a class="dr-btn dr-btn-dark" href="<?php echo esc_url( diradev_booking_url() ); ?>">
					<?php esc_html_e( 'Démarrer un projet', 'diradev' ); ?>
					<?php echo diradev_icon( 'arrow' ); // phpcs:ignore ?>
				</a>
			</div>
		</div>
	</div>

</main>

<?php get_footer(); ?>
