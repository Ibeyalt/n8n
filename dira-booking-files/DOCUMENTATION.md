# Dira Booking & Files — État du projet

Ce document fait le point entre le cahier des charges initial (60 points) et ce qui est réellement codé dans cette première version (1.0.0), pour que vous sachiez exactement quoi tester et quoi prioriser ensuite.

## Installation rapide

1. `dira-booking-files.zip` (à la racine du dépôt) → WordPress → Extensions → Ajouter → Téléverser une extension.
2. Activez. Les tables, le dossier protégé, les horaires par défaut (lun-ven 08:00-17:00) et les pages (Rendez-vous, Œuvres, Mon compte, Mes rendez-vous, Mes téléchargements) sont créés automatiquement.
3. Allez dans **Dira Booking → Paramètres** pour renseigner votre entreprise, votre devise, etc.
4. Créez vos services (**Dira Booking → Services**) et vos fichiers (**Dira Booking → Fichiers**).

## Ce qui est pleinement fonctionnel

- Architecture indépendante du thème (tables `wp_dira_*` dédiées, aucune logique dans le thème, aucune erreur si l'extension est désactivée).
- Services réservables (durée, prix, couleur, délais min/max, capacité, temps de préparation).
- Moteur de disponibilité : horaires hebdomadaires, jours fermés exceptionnels, horaires exceptionnels, créneaux bloqués manuellement — tout est pris en compte dans le calcul des créneaux libres.
- Formulaire de réservation public multi-étapes (`[dira_booking]`) : service → date → créneau → informations → récapitulatif → confirmation, en AJAX (pas de rechargement de page).
- Statuts de rendez-vous complets (en attente, confirmé, terminé, annulé, refusé, absence) + emails automatiques au client et à l'administrateur à chaque changement.
- Calendrier administrateur (vues jour / semaine / mois).
- Clients (création automatique depuis les réservations/achats, liste, recherche, suppression).
- Fichiers numériques : gratuits ou payants, catégories, page publique (`[dira_files]`) avec recherche/filtres/tri, fiche individuelle (`[dira_file]`).
- Téléchargement **sécurisé** : les fichiers sont stockés hors du dossier public (`wp-content/uploads/dira-protected/`, accès direct interdit), diffusés via un jeton à usage limité (nombre de téléchargements + expiration configurables).
- Commandes de fichiers payants + abstraction de paiement (`Dira_BF_Payment_Gateway`) : passerelle manuelle par défaut, passerelle WooCommerce automatique si WooCommerce est actif (créée à la volée, sans dépendance obligatoire).
- Rappels automatiques (24h / 2h avant, via WP-Cron) et nettoyage des liens expirés.
- Espace client sans compte obligatoire : "Mes rendez-vous" et "Mes téléchargements" via vérification par email.
- Réglages complets (général, rendez-vous, notifications, fichiers, paiement, avancé) avec devise configurable (jamais codée en dur).
- Désinstallation : conservation des données par défaut, suppression complète en option explicite.
- Sécurité de base : nonces sur tous les formulaires et endpoints AJAX, `current_user_can('manage_options')` sur toutes les actions d'administration, échappement systématique (`esc_html`, `esc_attr`, `esc_url`), requêtes `$wpdb` préparées, validation des extensions/tailles de fichiers à l'upload.

## Ce qui est architecturé mais volontairement simplifié (v1)

- **Paiement Mobile Money / API personnalisée** : l'interface `Dira_BF_Payment_Gateway` est prête (voir `includes/payment-gateway.php` et `includes/gateways/`) ; il suffit d'ajouter une classe `Dira_BF_Gateway_MobileMoney` et de l'enregistrer. Non implémentée dans cette version faute d'accès à une API réelle.
- **WhatsApp / SMS / Telegram** : le hook `dira_bf_notify` est déclenché à chaque notification (voir `class-notifications.php`) pour brancher un futur canal sans toucher au cœur de l'extension. Seul l'email (`wp_mail`) est actif aujourd'hui.
- **Permaliens jolis pour les œuvres** (`/oeuvres/mon-document/`) : actuellement la fiche s'affiche via `/oeuvres/?dira_file=mon-document` (même contenu, URL moins "propre"). Passer à une vraie règle de réécriture est une évolution simple mais volontairement reportée pour ne pas complexifier l'activation.
- **Calendrier admin** : vues jour/semaine/mois fonctionnelles avec une grille HTML légère (pas de librairie JS de calendrier externe pour rester léger et sans dépendance CDN).
- **Assistant de configuration (onboarding)** pas encore construit comme wizard pas-à-pas dans l'admin ; les réglages par défaut sont posés automatiquement à l'activation et modifiables immédiatement dans Paramètres.
- **Rôle "Gestionnaire" dédié** : l'administration est aujourd'hui réservée à `manage_options` (administrateurs). Ajouter une capacité dédiée (`dira_manage_bookings`) pour un rôle "Gestionnaire" est prévu mais pas encore câblé.
- **RGPD** (export/suppression des données personnelles) : les données sont minimales et la désinstallation propose déjà un choix conserver/supprimer ; les outils natifs "Exporter les données personnelles" / "Effacer les données personnelles" de WordPress (Réglages → Confidentialité) ne sont pas encore branchés sur les tables `wp_dira_*`.
- **REST API publique** (pour appli mobile/React/Flutter/chatbot) : les échanges front-end passent aujourd'hui par `admin-ajax.php`, suffisant pour le site web. Une vraie API REST (`/wp-json/dira/v1/...`) est une extension naturelle du code existant (les classes métier sont déjà séparées de l'affichage, donc réutilisables telles quelles).

## Ce qui n'est pas dans cette version

- Le **thème WordPress** lui-même n'est pas encore un thème installable : `diradev-theme/` contient une **maquette visuelle** (desktop + mobile) à recréer dans Elementor, pas un fichier `dira-theme.zip` fonctionnel. Dites-moi si vous voulez qu'on code un vrai thème (ou un thème enfant Hello Elementor) ensuite.
- Traductions (`.pot`/`.po`) : toutes les chaînes sont déjà encapsulées dans `__()`/`_e()` avec le text domain `dira-booking-files`, prêtes à être traduites, mais le fichier `.pot` n'a pas encore été généré.

## Prochaines étapes suggérées

1. Tester le parcours complet sur un WordPress de test (réservation, annulation, achat/téléchargement gratuit et payant).
2. Me dire quelle passerelle Mobile Money vous utilisez réellement (MTN, Airtel, autre) pour la brancher concrètement.
3. Décider si on code le vrai thème WordPress (au-delà de la maquette Elementor) — c'est un chantier à part entière.
