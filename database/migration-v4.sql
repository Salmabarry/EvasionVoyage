-- Migration v4 : désactivation de comptes + compteur de visiteurs
USE evasionvoyage;

-- Un compte peut être désactivé par l'admin (connexion refusée)
ALTER TABLE users
  ADD COLUMN active TINYINT(1) NOT NULL DEFAULT 1 AFTER is_admin;

-- Compteur de visites par jour (statistiques admin)
CREATE TABLE IF NOT EXISTS visits (
  day DATE PRIMARY KEY,
  counter INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;
