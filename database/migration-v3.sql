-- Migration v3 : offres à dates fixes (les destinations restent réservables à dates libres)
USE evasionvoyage;

CREATE TABLE IF NOT EXISTS offers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  destination_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT NULL,
  category ENUM('Vacances','Affaires','Aventure','Famille','Luxe') NOT NULL DEFAULT 'Vacances',
  price INT UNSIGNED NOT NULL,
  date_depart DATE NOT NULL,
  date_retour DATE NOT NULL,
  seats SMALLINT UNSIGNED NOT NULL DEFAULT 20,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_offers_destination FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Une réservation peut venir d'une offre (dates fixes) ou d'une destination (dates libres)
ALTER TABLE bookings
  ADD COLUMN offer_id INT UNSIGNED NULL AFTER destination_id,
  ADD CONSTRAINT fk_bookings_offer FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE SET NULL;

INSERT INTO offers (destination_id, title, description, category, price, date_depart, date_retour, seats) VALUES
((SELECT id FROM destinations WHERE slug='santorini'), 'Santorin romantique — 7 nuits', 'Hôtel troglodyte face à la caldeira, croisière au coucher du soleil et dégustation de vins.', 'Luxe', 845000, '2026-09-12', '2026-09-19', 12),
((SELECT id FROM destinations WHERE slug='maldives'), 'Maldives all inclusive — 8 nuits', 'Bungalow sur pilotis, pension complète, plongée et spa au cœur de l''océan Indien.', 'Luxe', 1605000, '2026-10-05', '2026-10-13', 10),
((SELECT id FROM destinations WHERE slug='kyoto'), 'Kyoto impérial — 10 nuits', 'Temples, cérémonie du thé, forêt de bambous d''Arashiyama et nuit en ryokan.', 'Vacances', 1240000, '2026-11-02', '2026-11-12', 14),
((SELECT id FROM destinations WHERE slug='marrakech'), 'Marrakech en famille — 5 nuits', 'Riad avec piscine, jardin Majorelle, balade à dos de chameau pour petits et grands.', 'Famille', 585000, '2026-08-24', '2026-08-29', 20),
((SELECT id FROM destinations WHERE slug='marrakech'), 'Marrakech affaires — 3 nuits', 'Hôtel 5* avec salles de réunion, transferts privés et conciergerie dédiée.', 'Affaires', 720000, '2026-09-21', '2026-09-24', 8),
((SELECT id FROM destinations WHERE slug='alps'), 'Alpes suisses grandeur nature — 6 nuits', 'Randonnées glaciaires, train panoramique et chalet au bord du lac.', 'Aventure', 1080000, '2026-09-07', '2026-09-13', 16),
((SELECT id FROM destinations WHERE slug='bali'), 'Bali zen — 9 nuits', 'Rizières d''Ubud, temples sacrés, retraite yoga et plages de Canggu.', 'Vacances', 950000, '2026-10-15', '2026-10-24', 18),
((SELECT id FROM destinations WHERE slug='bali'), 'Bali aventure — volcans et fonds marins', 'Ascension du mont Batur au lever du soleil, snorkeling à Amed et cascades secrètes.', 'Aventure', 1050000, '2026-11-08', '2026-11-16', 12),
((SELECT id FROM destinations WHERE slug='iceland'), 'Islande — aurores boréales, 7 nuits', 'Cercle d''or, lagon bleu, chasse aux aurores et grotte de glace avec guide francophone.', 'Aventure', 1435000, '2026-11-20', '2026-11-27', 10);
