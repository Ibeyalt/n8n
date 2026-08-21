# Thème DiraDev — checklist de livraison

Ce document suit le schéma demandé : analyse → traduction → activation → vérification.

## 1. Analyse de la maquette

Structure reprise de la maquette validée (`diradev-theme/`, style Creatix : fond noir, accent vert néon `#baff29`, typographies Space Grotesk/Manrope) et de la photo du fondateur :
en-tête sticky, héros avec visuel circulaire (photo + icônes flottantes), bandeau des 4 pôles d'expertise, section mission, liste de services numérotée, bannière slogan, process en 4 étapes, appel à l'action final, pied de page.

## 2. Traduction

Tout le thème est rédigé en français : menus, boutons, titres, contenus, messages d'erreur (404), textes de secours. Les chaînes passent par `__()`/`_e()` (text domain `diradev`) pour rester traduisibles si besoin d'autres langues plus tard.

## 3. Activation — tous les boutons sont réels

| Bouton / lien | Destination réelle |
|---|---|
| Logo (en-tête et pied de page) | Page d'accueil (`home_url()`) |
| Menu principal | Menu WordPress réel (`wp_nav_menu`), créé automatiquement à l'activation avec 4 liens fonctionnels (Accueil, Services, Notre process, Contact) ; modifiable dans **Apparence → Menus** |
| Bouton "Démarrer un projet" (en-tête, héros, services, appel à l'action) | Page "Rendez-vous" créée par l'extension Dira Booking & Files (repli sur `#contact` si l'extension est inactive) |
| "Voir nos services" | Ancre `#services` (défilement fluide) |
| Bandeau des 4 pôles | Ancre `#services` |
| Chaque ligne de service | Page "Rendez-vous" |
| "Une idée de projet ?" / "Besoin d'un agent IA ?" | Page "Rendez-vous" |
| Menu mobile (icône ☰) | Bascule réellement le menu (JavaScript, `assets/js/main.js`) |
| Coordonnées du pied de page | Email/téléphone réels si l'extension est configurée, sinon repli explicite `[votre téléphone]` |
| Bouton 404 | Retour à l'accueil |

Aucun bouton décoratif : chaque élément cliquable a une vraie destination (page, ancre ou action JS).

## 4. Vérification

- `php -l` exécuté sur tous les fichiers PHP du thème : aucune erreur de syntaxe.
- Chaque lien vérifié manuellement dans le code (tableau ci-dessus).
- Le thème ne dépend d'aucun plugin : sans l'extension Dira Booking & Files, tous les boutons se replient sur des ancres/pages qui existent toujours (aucune erreur PHP, aucun lien mort).
- `page.php` utilise `the_content()` (gabarit standard WordPress) : toutes les pages créées par l'extension (Rendez-vous, Œuvres, Mon compte…) s'affichent normalement, et ces pages restent éditables avec **Elementor** comme n'importe quelle page WordPress standard.
- La page d'accueil (`front-page.php`) reprend le design exact de la maquette validée ; elle n'est pas construite avec Elementor (voir note ci-dessous).

## À savoir sur Elementor

Elementor peut éditer **n'importe quelle page WordPress classique** de ce site sans restriction (Rendez-vous, Œuvres, Contact, pages que vous créez vous-même). En revanche, la page d'accueil (`front-page.php`) est codée directement dans le thème pour garantir un rendu fidèle à 100 % à la maquette validée et un fonctionnement immédiat sans dépendre d'Elementor : elle n'est donc pas éditable en glisser-déposer dans Elementor telle quelle.

Si vous voulez pouvoir modifier l'accueil visuellement dans Elementor, deux options :
1. Je peux transformer l'accueil en une vraie page Elementor (sections/widgets natifs) — possible, mais je ne peux pas le tester dans un vrai Elementor depuis cet environnement, donc un premier réglage manuel de votre part serait probablement nécessaire après génération.
2. Recréer l'accueil à la main dans Elementor en suivant le guide déjà fourni dans `diradev-theme/README.md` (structure section par section).

Dites-moi laquelle vous préférez.
