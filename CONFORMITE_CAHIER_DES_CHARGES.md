# Conformité au cahier des charges — EvasionVoyage (PHP)

Audit du site contre le cahier des charges « Site Web de Voyage v1.0 ». ✅ conforme · 🟡 partiel/adapté · 📋 prévu au déploiement.

## 2.1 Espace Visiteur
| Exigence | État |
|---|---|
| Consulter les destinations | ✅ `destinations.php` |
| Rechercher des voyages par critères | ✅ `offres.php` — pays/destination, dates, voyageurs, budget, type |
| Consulter les détails des offres | ✅ `reserver.php?offer=ID` (dates fixes, places, prix) |
| Voir les photos des destinations | 🟡 une photo par destination (modèle du site ; pas de galerie multiple) |
| Lire les avis des clients | ✅ avis modérés, affichés sur destinations + accueil |
| Contacter l'agence via un formulaire | ✅ `contact.php` (+ WhatsApp +221 77 145 49 28, Dakar — Sénégal) |
| S'inscrire ou se connecter | ✅ avec validation email, mot de passe ≥ 8, œil afficher/masquer |

## 2.2 Espace Utilisateur
| Exigence | État |
|---|---|
| Modifier son profil | ✅ `profil.php` (nom, téléphone, mot de passe) |
| Consulter ses informations personnelles | ✅ |
| Effectuer une réservation | ✅ offre à dates fixes OU destination à dates libres |
| Effectuer un paiement en ligne | ✅ Carte (Luhn côté serveur), Wave & Orange Money avec QR codes — simulé, passerelle réelle à brancher en production |
| Recevoir une confirmation de réservation | ✅ écran de confirmation avec référence de paiement |
| Consulter l'historique de ses voyages | ✅ `historique.php` (actualisation auto 15 s) |
| Suivre l'état de ses réservations | ✅ statuts en attente / confirmée / refusée / annulée |
| Télécharger ou imprimer ses confirmations | ✅ bouton Imprimer (PDF via le navigateur) |
| Envoi d'un e-mail de confirmation (§5.8) | 📋 nécessite un serveur SMTP — à brancher au déploiement (PHPMailer) |

## 2.3 Espace Administrateur
| Exigence | État |
|---|---|
| Utilisateurs : ajouter / modifier / supprimer / désactiver | ✅ complet (`admin/utilisateurs.php`) |
| Destinations : ajouter / modifier / supprimer / photos | ✅ (`admin/destinations.php`) — photo = image principale |
| Offres : ajouter / modifier (activer-désactiver) / supprimer / tarifs | ✅ (`admin/offres.php`) |
| Réservations : consulter / valider ou refuser / annulations | ✅ + remboursement et restitution des places automatiques |
| Statistiques : visiteurs / réservations / CA / top destinations | ✅ tableau de bord (compteur de visites réel, CA = paiements encaissés) |
| Bonus : modération des avis clients | ✅ (`admin/avis.php`) |

## 4-5. Recherche & Processus de réservation
Recherche : pays ✅ · ville 🟡 (le modèle regroupe pays/destination) · dates ✅ · budget ✅ · voyageurs ✅ · type ✅ (Vacances/Affaires/Aventure/Famille/Luxe).
Processus : sélection ✅ → détails ✅ → dates ✅ → voyageurs ✅ → validation ✅ → paiement sécurisé ✅ → confirmation ✅ → e-mail 📋.

## 6-7. Technique & Sécurité
PHP 8.3 + MySQL ✅ · front Tailwind CSS 🟡 (choix design du projet, à la place de Bootstrap/React proposés) · responsive ✅.
Mots de passe hachés (bcrypt) ✅ · requêtes préparées anti-injection ✅ · jetons CSRF sur tous les formulaires ✅ · échappement XSS ✅ · comptes désactivables ✅ · SSL 📋 déploiement · sauvegarde BDD ✅ (`database/sauvegarde-evasionvoyage.sql`).

## 9. Paiement
Carte bancaire ✅ · paiement mobile (Wave, Orange Money) ✅ · confirmation automatique ✅ · historique des transactions ✅ (table `payments`, visible admin).

---
**Mises à niveau de la base** : une installation existante doit exécuter `database/migration-v2.sql` → `v3` → `v4` (ou repartir du `schema.sql` complet). Une installation neuve utilise directement `schema.sql`.
