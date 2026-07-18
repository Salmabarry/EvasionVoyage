-- Migration v2 : paiements, avis modérés, dates & montants des réservations
-- (fonctionnalités portées depuis la version Node.js du projet)
USE evasionvoyage;

ALTER TABLE bookings
  ADD COLUMN date_depart DATE NULL AFTER travelers,
  ADD COLUMN date_retour DATE NULL AFTER date_depart,
  ADD COLUMN amount INT UNSIGNED NULL AFTER date_retour,
  MODIFY status ENUM('en_attente','confirmee','refusee','annulee') NOT NULL DEFAULT 'en_attente';

-- Montant des anciennes réservations : prix destination x voyageurs
UPDATE bookings b
JOIN destinations d ON d.id = b.destination_id
SET b.amount = d.price * b.travelers
WHERE b.amount IS NULL;

CREATE TABLE IF NOT EXISTS payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NOT NULL,
  amount INT UNSIGNED NOT NULL,
  method ENUM('carte','wave','orange_money') NOT NULL,
  reference VARCHAR(100) NOT NULL,
  status ENUM('paye','rembourse') NOT NULL DEFAULT 'paye',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Avis clients : créés "en_attente", publiés uniquement après validation admin
CREATE TABLE IF NOT EXISTS reviews (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  destination_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  comment TEXT NULL,
  status ENUM('en_attente','approuve') NOT NULL DEFAULT 'en_attente',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_reviews_destination FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
) ENGINE=InnoDB;
