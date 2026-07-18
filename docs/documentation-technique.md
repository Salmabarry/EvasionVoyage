# Documentation technique — EvasionVoyage

## 1. Architecture

Application web PHP classique en 3 couches, sans framework (choix pédagogique) :

1. **Présentation** : pages PHP qui génèrent le HTML (Tailwind CSS via CDN, icônes Lucide). Les éléments communs sont factorisés dans `includes/` (header, footer, en-têtes de page) et `admin/includes/` (layout de l'administration).
2. **Logique métier** : traitée en tête de chaque page (pattern *post-redirect-get*), avec les fonctions partagées d'`includes/auth.php` (session, connexion, rôles, CSRF) et `includes/db.php` (connexion PDO).
3. **Données** : MySQL/MariaDB via **PDO en requêtes préparées** exclusivement.

La connexion base (`includes/db.php`) détecte l'environnement : XAMPP Windows (127.0.0.1:3306, root sans mot de passe) ou MAMP macOS (socket, root/root). À adapter dans ce fichier pour tout autre hébergement.

## 2. Base de données (8 tables)

| Table | Rôle |
|---|---|
| `users` | Comptes (bcrypt, rôle `is_admin`, désactivation `active`) |
| `destinations` | Destinations (slug, prix indicatif/pers., catégorie, note) |
| `offers` | **Offres à dates fixes** : tarif, dates départ/retour, places (`seats`), activation |
| `bookings` | Réservations — 2 modes : liée à une offre (`offer_id`) ou sur mesure (dates libres) ; statuts `en_attente / confirmee / refusee / annulee` |
| `payments` | Transactions : montant, méthode (`carte/wave/orange_money`), référence, statut `paye/rembourse` |
| `reviews` | Avis clients **modérés** : `en_attente` → `approuve` par l'admin |
| `contact_messages` | Messages du formulaire de contact (statut nouveau/lu/répondu) |
| `visits` | Compteur de visites par jour (une par session) |

**Scripts fournis** (`database/`) : `schema.sql` (installation neuve), `migration-v2/v3/v4.sql` (mise à niveau d'une base antérieure), `sauvegarde-evasionvoyage.sql` (base complète avec données de démonstration — export/sauvegarde exigé par le cahier des charges).

## 3. Parcours de réservation et paiement

1. Le client choisit une **offre** (dates imposées, contrôle des places) ou une **destination** (dates libres, contrôles serveur : départ futur, retour > départ).
2. Étape paiement : montant recalculé **côté serveur** (jamais confiance au client), 3 méthodes — carte (numéro validé par l'**algorithme de Luhn en PHP**, expiration, CVC), Wave et Orange Money (QR code + numéro).
3. En transaction SQL : création `bookings` (confirmée) + `payments` (référence unique `PAY-…`) + décompte des places de l'offre.
4. Annulation/refus : remboursement (`payments.status = rembourse`) et restitution automatique des places.

> Le paiement est **simulé** (aucune passerelle réelle). En production : brancher CinetPay/Stripe dans `reserver.php`, et un envoi d'e-mail (PHPMailer + SMTP) pour la confirmation.

## 4. Sécurité

- **Mots de passe** : `password_hash()` / `password_verify()` (bcrypt), jamais en clair ;
- **Injection SQL** : 100 % requêtes préparées PDO ;
- **CSRF** : jeton de session vérifié (`csrf_check`) sur **tous** les formulaires POST ;
- **XSS** : tout affichage de donnée passe par `htmlspecialchars()` ;
- **Contrôle d'accès** : `require_login()` / `require_admin()` ; comptes désactivables (connexion refusée) ; un admin ne peut pas réserver (intégrité des statistiques) ;
- **SSL** : à activer au déploiement (certificat + redirection HTTPS).

## 5. Déploiement en production

1. Hébergement avec PHP ≥ 8 et MySQL (VPS + Apache/Nginx, ou mutualisé compatible) ;
2. Importer `database/schema.sql`, créer un utilisateur MySQL dédié avec mot de passe fort, reporter les identifiants dans `includes/db.php` ;
3. Supprimer/renommer les comptes de démonstration ; activer HTTPS (Let's Encrypt) ;
4. Brancher la passerelle de paiement et l'envoi d'e-mails ;
5. Sauvegarde quotidienne : `mysqldump evasionvoyage` planifié (cron).
