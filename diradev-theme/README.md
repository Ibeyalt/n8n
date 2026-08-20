# Maquette de thème DIRADEV

Maquette de page d'accueil pour la marque **DIRADEV** (© DIRAHOLDINGS LLC), pensée pour un site WordPress modifiable avec **Elementor**.

**Voir la maquette (desktop + mobile) :** https://claude.ai/code/artifact/644fd0cf-78b6-430a-9062-7b8bc7edcdd1

## Contenu

- `Main.dc.html` / `Mobile.dc.html` / `canvas.json` — source de la maquette visuelle (design canvas), à ignorer pour l'intégration WordPress ; ils servent uniquement à régénérer/mettre à jour l'aperçu ci-dessus.
- Sections de la page, dans l'ordre : Header, Hero, bandeau des 4 pôles d'expertise, Mission, Services (4 offres), bannière "Innover + Automatiser + Croître", Process (4 étapes), CTA final, Footer.

## Identité visuelle

- **Fond** : noir profond `#0e120f`, cartes `#151a16`
- **Accent** : vert néon `#baff29`
- **Blanc cassé** (cartes claires / texte) : `#f5f7ef`
- **Titres** : Space Grotesk (Google Fonts) — **Texte courant** : Manrope (Google Fonts)
- Formes : cercles pointillés, badges arrondis, coins très arrondis (20–32px), pas de dégradés agressifs

## Services représentés

1. Sites Web & Applications
2. Marketing Digital
3. Agents IA & Automatisation
4. Sourcing Produit International

## Recréer la page dans Elementor

Cette maquette est un guide visuel (wireframe haute-fidélité), pas un fichier d'import direct : Elementor n'importe pas de HTML brut comme mise en page. Pour la reproduire :

1. **Thème de base** : installez un thème léger compatible Elementor, par ex. **Hello Elementor** (gratuit, officiel Elementor).
2. **Polices** : dans Elementor > Réglages du site > Police, ajoutez *Space Grotesk* (titres) et *Manrope* (texte) via Google Fonts.
3. **Couleurs globales** : dans Elementor > Réglages du site > Couleurs générales, créez les couleurs ci-dessus (fond, accent, cartes, texte).
4. **Reconstruire section par section** avec les conteneurs Flexbox d'Elementor (Container) :
   - Header : Container en `justify-content: space-between`, logo + menu (widget Nav Menu) + bouton.
   - Hero : Container 2 colonnes, texte à gauche, visuel à droite (le "cercle" peut être recréé avec des formes SVG en widget Image/HTML personnalisé, ou simplifié en une image).
   - Bandeau 4 pôles / Services / Process : widgets **Icon Box** en grille (Container avec 4 colonnes).
   - CTA final : Container avec fond vert `#baff29`, coins arrondis 24–32px.
   - Footer : Container multi-colonnes + widget texte pour la mention `© DIRAHOLDINGS LLC`.
5. Les icônes de la maquette sont des SVG simples (traits, sans remplissage) : vous pouvez les recréer avec le widget **Icon** d'Elementor (bibliothèque Font Awesome, style "outline") ou coller les SVG fournis dans un widget **HTML personnalisé**.
6. Remplacez les champs entre crochets (`[votre email de contact]`, etc.) par vos vraies coordonnées avant mise en ligne.

## Prochaines étapes possibles

- Déclinaison des pages secondaires (Services, Réalisations, À propos, Contact) dans le même style.
- Export d'un kit de templates Elementor (.json) une fois la structure validée dans l'éditeur.
