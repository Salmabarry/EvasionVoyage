# ✈️ EvasionVoyage — Plateforme de réservation de voyages

Site web complet de réservation touristique en ligne : découverte de destinations, offres à dates fixes, voyages sur mesure, paiement en ligne et espace d'administration complet.

> **Devise :** *Le monde est à portée de clic* — 📍 Dakar, Sénégal · 📞 +221 77 145 49 28 (WhatsApp)

---

## 🧰 Technologies

| Couche | Technologie |
|---|---|
| Back-end | PHP 8 (procédural + PDO) |
| Base de données | MySQL / MariaDB |
| Front-end | HTML5, Tailwind CSS, JavaScript, icônes Lucide |
| Sécurité | Mots de passe bcrypt, requêtes préparées, jetons CSRF, échappement XSS |

## ⭐ Fonctionnalités

### Espace visiteur
- Découverte des destinations et des **offres à dates fixes** (places limitées)
- **Recherche multicritères** : pays, dates, voyageurs, budget, type de voyage
- Avis clients vérifiés (modérés avant publication)
- Formulaire de contact + bouton WhatsApp
- Inscription / connexion sécurisées (validation e-mail, afficher/masquer le mot de passe)

### Espace voyageur (connecté)
- Réservation d'une **offre** (dates fixes) ou d'une **destination à ses propres dates**
- **Paiement en ligne** : carte bancaire (validation Luhn), **Wave** et **Orange Money** avec QR codes *(démonstration — passerelle réelle à brancher en production)*
- Tableau de bord, **Mes réservations** (annulation, **impression** de la confirmation), **historique** des voyages en temps réel, profil modifiable

### Espace administrateur
- Tableau de bord : **visiteurs**, réservations, **chiffre d'affaires réel**, top destinations, graphiques
- Gestion des **réservations** (valider / refuser / annuler, remboursement automatique)
- Gestion des **utilisateurs** (ajouter, modifier, désactiver, supprimer, rôles)
- Gestion des **destinations** et des **offres** (tarifs, dates, places, activation)
- **Modération des avis** clients et messagerie de contact

## 🚀 Installation locale (Windows + XAMPP)

### Prérequis
- [XAMPP](https://www.apachefriends.org) (Apache + MySQL démarrés)
- [Git](https://git-scm.com)

### Étapes

```bash
# 1. Cloner le projet dans htdocs
cd /c/xampp/htdocs
git clone https://github.com/Salmabarry/EvasionVoyage.git
```

**2. Créer la base de données** — deux options :
- **phpMyAdmin** (simple) : `http://localhost/phpmyadmin` → onglet *Importer* → choisir `database/sauvegarde-evasionvoyage.sql` → *Exécuter* *(base complète avec données de démonstration)*
- Ou en ligne de commande : `mysql -u root < database/schema.sql` *(structure seule)*

**3. Ouvrir le site** : [http://localhost/EvasionVoyage](http://localhost/EvasionVoyage)

> La connexion à la base s'adapte automatiquement (XAMPP Windows : port 3306, root sans mot de passe — modifiable dans `includes/db.php`).

### Comptes de démonstration

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Administrateur | `admin@evasionvoyage.travel` | `AdminEvasion2026!` |
| Client | `client.test@evasion.com` | `TestClient2026!` |

Carte de test pour le paiement : `4242 4242 4242 4242` · expiration `09/28` · CVC `123`

## 📂 Structure du projet

```
EvasionVoyage/
├── index.php, destinations.php, offres.php…   # Pages publiques
├── reserver.php                               # Réservation + paiement (2 modes)
├── tableau-de-bord.php, mes-reservations.php,
│   historique.php, profil.php                 # Espace voyageur
├── admin/                                     # Espace administrateur complet
├── includes/                                  # Connexion BDD, auth/CSRF, header, footer
├── assets/                                    # CSS, JS, images, logos SVG
├── database/                                  # schema.sql, migrations v2→v4, sauvegarde complète
├── identite/                                  # Identité visuelle : logos, charte PDF, kit réseaux sociaux
└── CONFORMITE_CAHIER_DES_CHARGES.md           # Audit de conformité au cahier des charges
```

## 📑 Documents du projet

- [Cahier des charges](./Cahier_des_charges_Identite_Visuelle_EvasionVoyage.md) et [modèle de données](./Modele_Donnees_EvasionVoyage.md)
- [Conformité au cahier des charges](./CONFORMITE_CAHIER_DES_CHARGES.md) — audit point par point
- [Charte graphique (PDF)](./identite/charte/charte-graphique.pdf) et [guide d'utilisation de la marque](./identite/charte/guide-utilisation.pdf)

## 🔄 Mise à jour d'une installation existante

```bash
git pull
```
Si la structure de la base a évolué : réimporter `database/sauvegarde-evasionvoyage.sql`, ou appliquer les migrations `database/migration-v2.sql` → `v3` → `v4`.

## 👩🏽‍💻 Auteure

**Salma Barry** — projet de formation en développement web, 2026.
