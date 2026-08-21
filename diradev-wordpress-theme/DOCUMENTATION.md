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

La page d'accueil n'est plus codée en dur dans le thème (il n'y a plus de `front-page.php`). À l'activation, le thème :

1. Crée (ou met à jour, une seule fois) une vraie page WordPress intitulée **"Accueil"**.
2. La définit comme page d'accueil du site (Réglages → Lecture → "Une page statique").
3. Lui attribue le gabarit **"Elementor Full Width"** (conserve l'en-tête/pied de page du thème).
4. Génère de vraies données Elementor (7 sections → 1 colonne pleine largeur → 1 widget HTML chacune, reprenant exactement la maquette validée) stockées dans la métadonnée `_elementor_data` de la page.

Résultat : ouvrez "Accueil" avec **Modifier avec Elementor** et vous retrouvez les 7 sections (Héros, Pôles, Mission, Services, Slogan, Process, Appel à l'action) comme des blocs Elementor déplaçables, duplicables, supprimables — vous pouvez aussi ajouter n'importe quel widget Elementor à côté.

**Vérification effectuée** : un test isolé (`inc/homepage-content.php` + `inc/homepage-setup.php` exécutés hors WordPress) confirme que le JSON généré est valide, contient bien 7 sections avec identifiants uniques correctement formés, et que chaque widget HTML contient un contenu non vide. Je n'ai en revanche pas pu ouvrir cette page dans un vrai éditeur Elementor depuis cet environnement (aucune instance WordPress+Elementor disponible ici) : après import, ouvrez la page une première fois dans Elementor et cliquez sur **Mettre à jour** — si un réglage mineur (espacement, largeur) doit être ajusté, ce sera visible et corrigible immédiatement dans l'éditeur.

**Sécurité de repli** : si Elementor est désactivé, désinstallé, ou si vous éditez "Accueil" sans Elementor, la page affiche automatiquement le même contenu via `page.php` (blocs HTML natifs WordPress) — jamais de page blanche.

Les autres pages (Rendez-vous, Œuvres, Contact, et toute page que vous créez) restent éditables dans Elementor sans aucune restriction particulière, comme sur n'importe quel thème standard.

## Historique des versions

- **1.1.0** — Correction des bugs d'affichage mobile/tablette (visuel héros en pourcentages au lieu de pixels fixes), correction du script qui restait inactif (menu mobile, animations), numéro de version incrémenté pour forcer le rechargement du CSS/JS en cache par le navigateur.
- **1.0.0** — Version initiale : thème fonctionnel, page d'accueil générée avec données Elementor natives.
