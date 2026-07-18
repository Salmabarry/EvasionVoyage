# TODO - Identité Visuelle EvasionVoyage

Suivi des tâches issues du [Cahier des charges](./Cahier_des_charges_Identite_Visuelle_EvasionVoyage.md), organisées par priorité.

> ✅ **Toutes les tâches sont réalisées** — les livrables sont dans le dossier [`identite/`](./identite/)
> (logos SVG/PNG, charte graphique PDF, guide d'utilisation, kit réseaux sociaux, supports print, planche de tests).

---

## 🔴 Priorité Haute — Fondations de l'identité

Bases indispensables avant toute déclinaison.

- [x] Définir/valider le concept créatif (style minimaliste, élégant, premium, international) → *charte graphique §1*
- [x] Choisir la direction artistique du logo (icône + typographie) → *globe + trajectoire + avion + pin / Poppins*
- [x] Sélectionner les éléments visuels du logo parmi : globe, avion, trajectoire, localisation, mer, montagne, soleil, palmier
- [x] Créer le logo principal en version couleur
- [x] Valider la palette de couleurs → *charte §4, avec audit de contraste WCAG*
  - [x] Bleu `#0A6EBD`
  - [x] Orange `#F59E0B`
  - [x] Blanc `#FFFFFF`
  - [x] Gris `#374151`
- [x] Valider les typographies → *charte §5, hiérarchie complète*
  - [x] Poppins (titres)
  - [x] Inter / Open Sans (texte courant)
- [x] Exporter le logo au format vectoriel SVG (source éditable) → `identite/logo/svg/` (12 fichiers)

---

## 🟠 Priorité Moyenne-Haute — Déclinaisons essentielles du logo

Nécessaires pour un usage minimal sur le site et les supports numériques.

- [x] Version monochrome du logo → `logo-horizontal-monochrome.svg` (+ icône, + verticale)
- [x] Version noire du logo → `logo-horizontal-noir.svg`
- [x] Version blanche du logo (pour fonds foncés) → `logo-horizontal-blanc.svg`
- [x] Version horizontale du logo → `logo-horizontal-couleur.svg`
- [x] Version verticale du logo → `logo-vertical-couleur.svg`
- [x] Version icône seule (symbole sans texte)
- [x] Favicon (formats web : .ico / .png multi-tailles)
- [x] Export PNG haute définition (toutes versions) → `identite/logo/png/` (12 fichiers, fond transparent)

---

## 🟡 Priorité Moyenne — Livrables documentaires

Formalisation et transmission de l'identité.

- [x] Rédiger la charte graphique complète (PDF) → `identite/charte/charte-graphique.pdf` (7 pages)
  - [x] Règles d'usage du logo (zone de protection, tailles min, interdits)
  - [x] Palette de couleurs (codes HEX / RGB / CMJN)
  - [x] Typographies et hiérarchie (titres, texte, boutons)
  - [x] Exemples d'application (site, mobile, print)
- [x] Rédiger le guide d'utilisation de la marque → `identite/charte/guide-utilisation.pdf`
- [x] Vérifier la cohérence globale avec le positionnement "plateforme de réservation de voyages" → *charte §6*

---

## 🟢 Priorité Moyenne-Basse — Kit réseaux sociaux & supports

Déclinaisons pour la diffusion et la communication.

- [x] Kit réseaux sociaux → `identite/reseaux-sociaux/`
  - [x] Photo de profil (Facebook, Instagram, LinkedIn, X, etc.) → `photo-profil-1024.png`
  - [x] Bannières/couvertures aux formats de chaque plateforme → Facebook 820×312, LinkedIn 1584×396, X 1500×500, YouTube 2560×1440
  - [x] Template de post générique → `template-post-1080x1080.png` (source HTML modifiable dans `src/`)
- [x] Déclinaison carte de visite → `identite/print/carte-visite.pdf` (recto/verso 85×55 mm)
- [x] Déclinaison documents (papier à en-tête, signature email, etc.) → `papier-en-tete.pdf`, `signature-email.html`
- [x] Déclinaison affiches / supports print → `identite/print/affiche.pdf` (A4)

---

## 🔵 Priorité Basse — Vérifications finales & tests de compatibilité

Contrôle qualité avant livraison finale.

- [x] Test de lisibilité du logo en petite taille (favicon, app icon) → *planche de tests §2 : lisible jusqu'à 32 px, favicon simplifié en dessous*
- [x] Test de compatibilité site web (header, footer, responsive)
- [x] Test de compatibilité mobile (app icon, splash screen) → *apple-touch-icon existant + `splash-screen-1080x1920.png`*
- [x] Test d'affichage sur fonds clairs et fonds foncés → *planche de tests §1 (`identite/tests/planche-tests.html`)*
- [x] Vérification de l'accessibilité des contrastes (palette de couleurs) → *audit WCAG dans la charte §4 (`identite/charte/contrastes.json`)*
- [x] Revue finale des critères qualité → *charte §6*
  - [x] Professionnel
  - [x] Mémorable
  - [x] Évolutif
  - [x] Cohérent avec l'univers du voyage

---

## 📦 Livrables finaux à archiver

- [x] Logo SVG (toutes versions) → `identite/logo/svg/`
- [x] PNG HD (toutes versions) → `identite/logo/png/`
- [x] Favicon → `assets/img/favicon.ico` / `.png`
- [x] Charte graphique PDF → `identite/charte/charte-graphique.pdf`
- [x] Kit réseaux sociaux → `identite/reseaux-sociaux/`
- [x] Guide d'utilisation → `identite/charte/guide-utilisation.pdf`
