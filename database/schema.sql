-- Schéma EvasionVoyage (phase 1 : PHP procédural + MySQL 8)
CREATE DATABASE IF NOT EXISTS evasionvoyage
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE evasionvoyage;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS destinations (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(150) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  country VARCHAR(150) NOT NULL,
  image VARCHAR(255) NOT NULL,
  tagline VARCHAR(255) NULL,
  price INT UNSIGNED NOT NULL,
  nights SMALLINT UNSIGNED NOT NULL,
  category VARCHAR(50) NOT NULL,
  rating DECIMAL(2,1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  destination_id INT UNSIGNED NOT NULL,
  travelers SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  status ENUM('en_attente', 'confirmee', 'annulee') NOT NULL DEFAULT 'en_attente',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_bookings_destination FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  phone VARCHAR(30) NULL,
  destination VARCHAR(150) NULL,
  budget VARCHAR(50) NULL,
  message TEXT NOT NULL,
  status ENUM('nouveau', 'lu', 'repondu') NOT NULL DEFAULT 'nouveau',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO destinations (slug, name, country, image, tagline, price, nights, category, rating) VALUES
  ('santorini', 'Santorin', 'Grèce', 'assets/img/hero-santorini.jpg', "Coucher de soleil sur l'Égée", 845000, 7, 'Luxe', 4.9),
  ('maldives', 'Maldives', 'Océan Indien', 'assets/img/dest-maldives.jpg', 'Bungalows sur eaux turquoise', 1605000, 8, 'Plage', 4.9),
  ('kyoto', 'Kyoto', 'Japon', 'assets/img/dest-kyoto.jpg', 'Temples et forêts de bambou', 1240000, 10, 'Culture', 4.8),
  ('marrakech', 'Marrakech', 'Maroc', 'assets/img/dest-marrakech.jpg', 'Riads et médinas colorées', 585000, 5, 'Culture', 4.7),
  ('alps', 'Alpes Suisses', 'Suisse', 'assets/img/dest-alps.jpg', 'Sommets et lacs cristallins', 1080000, 6, 'Nature', 4.8),
  ('bali', 'Bali', 'Indonésie', 'assets/img/dest-bali.jpg', 'Rizières et spiritualité', 950000, 9, 'Aventure', 4.8),
  ('iceland', 'Islande', 'Nord Atlantique', 'assets/img/dest-iceland.jpg', 'Aurores boréales et glaciers', 1435000, 7, 'Aventure', 4.9)
ON DUPLICATE KEY UPDATE
  name = VALUES(name), country = VALUES(country), image = VALUES(image),
  tagline = VALUES(tagline), price = VALUES(price), nights = VALUES(nights),
  category = VALUES(category), rating = VALUES(rating);

-- Compte admin de démo (local uniquement) — email: admin@evasionvoyage.travel / mot de passe: AdminEvasion2026!
INSERT INTO users (first_name, last_name, email, password_hash, is_admin) VALUES
  ('Admin', 'EvasionVoyage', 'admin@evasionvoyage.travel', '$2y$12$LP5FMjML3cVCB2OUB5mk1udjP17LF8Mj7AfoaRK7WmD8MQjSs9qVO', 1)
ON DUPLICATE KEY UPDATE is_admin = 1, password_hash = VALUES(password_hash);
