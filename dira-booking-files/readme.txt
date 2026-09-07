=== Dira Booking & Files ===
Contributors: diradev
Tags: booking, appointments, digital downloads, file sales, calendar
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Prise de rendez-vous en ligne et vente/téléchargement de fichiers numériques pour WordPress, indépendante de tout thème.

== Description ==

Dira Booking & Files ajoute à votre site WordPress :

* La prise de rendez-vous en ligne (services, disponibilités, créneaux, confirmation/annulation).
* La gestion des clients et des notifications par email (client + administrateur).
* Un calendrier administrateur (jour / semaine / mois).
* Une page publique "Œuvres" pour publier des fichiers numériques gratuits ou payants, avec téléchargement sécurisé et limité.
* Une abstraction de paiement (paiement manuel par défaut, intégration WooCommerce optionnelle, architecture prête pour Mobile Money).

L'extension fonctionne indépendamment du thème actif : si vous changez de thème, ou si vous désactivez l'extension, aucune erreur n'est générée et vos données restent intactes.

= Shortcodes =

* `[dira_booking]` ou `[dira_booking_form]` — formulaire de réservation.
* `[dira_files]` — grille publique des fichiers.
* `[dira_file id="123"]` — fiche d'un fichier précis.
* `[dira_my_bookings]` — mes rendez-vous (recherche par email).
* `[dira_my_downloads]` — mes téléchargements (recherche par email).

= Architecture =

Toute la logique métier (rendez-vous, clients, fichiers, commandes, notifications) vit dans l'extension, dans des tables dédiées (`wp_dira_*`). Le thème ne gère que l'apparence : typographie, couleurs, header, footer, layout général.

== Installation ==

1. Téléversez le dossier `dira-booking-files` dans `/wp-content/plugins/`, ou installez le zip depuis Extensions → Ajouter → Téléverser une extension.
2. Activez l'extension.
3. À l'activation, l'extension crée automatiquement les tables nécessaires, un dossier protégé pour les fichiers payants, des horaires par défaut (lundi-vendredi 08:00-17:00) et les pages : Rendez-vous, Œuvres, Mon compte, Mes rendez-vous, Mes téléchargements.
4. Configurez l'extension depuis Dira Booking → Paramètres (entreprise, devise, horaires, notifications, fichiers, paiement).
5. Ajoutez vos services (Dira Booking → Services) et vos fichiers (Dira Booking → Fichiers).

== Frequently Asked Questions ==

= Dois-je installer WooCommerce ? =

Non. Le paiement manuel/hors-ligne fonctionne sans aucune dépendance. Si WooCommerce est actif, une passerelle de paiement WooCommerce apparaît automatiquement dans les réglages.

= Que se passe-t-il si je désactive l'extension ? =

Rien n'est cassé côté thème : les pages existent toujours, elles affichent simplement un shortcode inactif tant que l'extension n'est pas réactivée. Aucune donnée n'est supprimée à la désactivation.

= Mes données sont-elles supprimées si je désinstalle l'extension ? =

Par défaut, non : vos données sont conservées. Vous pouvez choisir de les supprimer définitivement depuis Paramètres → Avancé avant de désinstaller.

== Changelog ==

= 1.0.0 =
* Version initiale : rendez-vous, disponibilités, clients, notifications, fichiers numériques, téléchargements sécurisés, commandes, abstraction de paiement, shortcodes, tableau de bord.
