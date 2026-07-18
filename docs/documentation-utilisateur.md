# Documentation utilisateur — EvasionVoyage

## 1. Visiteur (sans compte)

- **Accueil** : destinations phares, avis de voyageurs (réels, validés), promesses de la marque ;
- **Destinations** : parcourir toutes les destinations et leurs prix « à partir de » ;
- **Offres** : séjours **à dates fixes** avec la recherche : pays/destination, départ après / retour avant, nombre de voyageurs, budget maximum (curseur), type (Vacances, Affaires, Aventure, Famille, Luxe) ;
- **Contact** : formulaire + bouton **WhatsApp** flottant (+221 77 145 49 28) — Dakar, Sénégal ;
- **Inscription** : prénom, nom, e-mail valide, mot de passe (8 caractères minimum, œil 👁 pour l'afficher).

## 2. Voyageur connecté

### Réserver
1. Depuis **Offres** : choisir une offre → les **dates sont fixes**, indiquer le nombre de voyageurs (places limitées) ;
   Depuis **Destinations** : choisir la destination → **choisir librement ses dates** de départ et retour ;
2. Vérifier le total (calculé automatiquement) → **Continuer vers le paiement** ;
3. Payer par **carte** (démo : `4242 4242 4242 4242`, exp. `09/28`, CVC `123`), **Wave** ou **Orange Money** (scanner le QR code ou saisir son numéro) ;
4. La confirmation s'affiche avec la **référence de paiement**.

### Gérer ses voyages (menu sur le prénom, en haut à droite)
- **Tableau de bord** : voyages à venir, totaux, accès rapides ;
- **Mes réservations** : statut de chaque voyage, bouton **Imprimer** la confirmation (→ PDF), bouton **Annuler** (remboursement automatique) ;
- **Historique des voyages** : toutes les réservations et leur état, actualisé en temps réel ;
- **Mon profil** : modifier nom, téléphone, mot de passe ;
- **Donner un avis** sur une destination : il sera publié **après validation** par l'équipe.

## 3. Administrateur

Connexion avec un compte admin → bouton **Admin** (ou `/admin/index.php`).

- **Vue d'ensemble** : visiteurs, réservations, **chiffre d'affaires encaissé**, destinations les plus réservées, graphiques (réservations par mois, statuts, visites 14 jours), derniers messages ;
- **Réservations** : tout consulter ; passer une réservation à *Confirmée / Refusée / Annulée* — refus et annulation **remboursent** le paiement et **rendent les places** de l'offre ;
- **Offres** : créer une offre (destination, titre, type, tarif/pers., dates, places), l'activer/désactiver, la supprimer ;
- **Avis clients** : publier ✔ ou supprimer 🗑 les avis en attente (badge de compteur dans le menu) ;
- **Utilisateurs** : ajouter, **modifier**, promouvoir/rétrograder admin, **désactiver** (connexion bloquée) ou supprimer un compte ;
- **Destinations** et **Messages** : gestion du catalogue et du courrier entrant.

## 4. Comptes de démonstration

| Rôle | E-mail | Mot de passe |
|---|---|---|
| Administrateur | `admin@evasionvoyage.travel` | `AdminEvasion2026!` |
| Client | `client.test@evasion.com` | `TestClient2026!` |

## 5. En cas de problème

| Symptôme | Cause probable | Solution |
|---|---|---|
| « Connexion refusée » dans le navigateur | Apache éteint | XAMPP → Start Apache |
| Erreur de connexion à la base sur les pages | MySQL éteint ou identifiants | XAMPP → Start MySQL ; vérifier `includes/db.php` |
| « URL introuvable » | mauvais dossier/URL | vérifier `htdocs/EvasionVoyage` et l'adresse |
| Pages sans données après mise à jour | base non migrée | réimporter `database/sauvegarde-evasionvoyage.sql` |
