-- Seed-Daten für OttbergenLocations
-- Entwicklungs- und Test-Daten

USE ottbergen_booking;

-- Test-User erstellen
-- WICHTIG: Passwörter sind gehashed mit SHA256 + Salt
-- Alle Test-Accounts haben das Passwort: "Test123!"

-- User 1: Max Mustermann (Provider)
INSERT INTO users (user_id, first_name, last_name, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(1, 'Max', 'Mustermann', 'max.mustermann@example.com', '+49 5272 123456', 'Hauptstraße', '45', '37691', 'Ottbergen',
 SHA2(CONCAT('Test123!', 'salt1'), 256), 'salt1', 1)
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- User 2: Maria Schmidt (Provider)
INSERT INTO users (user_id, first_name, last_name, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(2, 'Maria', 'Schmidt', 'maria.schmidt@example.com', '+49 5272 234567', 'Kirchweg', '12', '37691', 'Ottbergen',
 SHA2(CONCAT('Test123!', 'salt2'), 256), 'salt2', 1)
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- User 3: Hans Müller (Provider)
INSERT INTO users (user_id, first_name, last_name, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(3, 'Hans', 'Müller', 'hans.mueller@example.com', '+49 5272 345678', 'Waldweg', '23', '37691', 'Ottbergen',
 SHA2(CONCAT('Test123!', 'salt3'), 256), 'salt3', 1)
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- User 4: Anna Wagner (Normaler User/Kunde)
INSERT INTO users (user_id, first_name, last_name, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(4, 'Anna', 'Wagner', 'anna.wagner@example.com', '+49 5272 456789', 'Bergstraße', '7', '37691', 'Ottbergen',
 SHA2(CONCAT('Test123!', 'salt4'), 256), 'salt4', 0)
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- User 5: Thomas Klein (Normaler User/Kunde)
INSERT INTO users (user_id, first_name, last_name, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(5, 'Thomas', 'Klein', 'thomas.klein@example.com', '+49 5272 567890', 'Dorfstraße', '15', '37691', 'Ottbergen',
 SHA2(CONCAT('Test123!', 'salt5'), 256), 'salt5', 0)
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- Places (Orte) erstellen
INSERT INTO places (name, description, location, capacity, price_per_day, latitude, longitude, address, postal_code, user_id, active) VALUES
(
    'Kulturraum Ottbergen',
    'Ein wunderschöner Veranstaltungsraum im Herzen von Ottbergen. Ideal für Hochzeiten, Geburtstagsfeiern und Firmenevents. Der Raum bietet eine moderne Ausstattung und viel Platz für Ihre Gäste.',
    'Ottbergen',
    100,
    250.00,
    51.7234,
    9.3456,
    'Hauptstraße 45',
    '37691',
    1,
    1
),
(
    'Gemeindesaal St. Marien',
    'Traditioneller Gemeindesaal in ruhiger Lage. Perfekt für Familienfeierlichkeiten und kleinere Veranstaltungen. Mit Küche und direktem Zugang zum Garten.',
    'Ottbergen Süd',
    60,
    150.00,
    51.7189,
    9.3512,
    'Kirchweg 12',
    '37691',
    1,
    1
),
(
    'Dorfgemeinschaftshaus',
    'Gemütliches Dorfgemeinschaftshaus mit rustikalem Charme. Ideal für private Feiern im kleinen Kreis. Voll ausgestattete Küche vorhanden.',
    'Ottbergen Nord',
    40,
    120.00,
    51.7298,
    9.3423,
    'Dorfstraße 8',
    '37691',
    2,
    1
),
(
    'Scheune am Waldrand',
    'Umgebaute historische Scheune mit besonderem Ambiente. Rustikaler Charme trifft auf moderne Technik. Große Außenfläche verfügbar. Perfekt für außergewöhnliche Events.',
    'Ottbergen West',
    80,
    300.00,
    51.7245,
    9.3289,
    'Waldweg 23',
    '37691',
    3,
    1
);

-- Bilder für Places
INSERT INTO place_images (place_id, url) VALUES
-- Kulturraum Ottbergen
(1, 'https://images.unsplash.com/photo-1519167758481-83f29da8ee8a?w=800'),
(1, 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=800'),
(1, 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800'),

-- Gemeindesaal St. Marien
(2, 'https://images.unsplash.com/photo-1478146896981-b80fe463b330?w=800'),
(2, 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=800'),

-- Dorfgemeinschaftshaus
(3, 'https://images.unsplash.com/photo-1504253163759-c23fccaebb55?w=800'),
(3, 'https://images.unsplash.com/photo-1478146896981-b80fe463b330?w=800'),

-- Scheune am Waldrand
(4, 'https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=800'),
(4, 'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=800'),
(4, 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800');

-- Features für Places
INSERT INTO place_features (place_id, name, icon, available) VALUES
-- Kulturraum Ottbergen
(1, 'WLAN verfügbar', 'wifi', 1),
(1, 'Parkmöglichkeiten', 'parking', 1),
(1, 'Küche vorhanden', 'kitchen', 1),
(1, 'Barrierearm', 'accessible', 1),
(1, 'Bühne vorhanden', 'stage', 1),
(1, 'Ton- und Lichttechnik', 'audio', 1),

-- Gemeindesaal St. Marien
(2, 'WLAN verfügbar', 'wifi', 1),
(2, 'Parkmöglichkeiten', 'parking', 1),
(2, 'Küche vorhanden', 'kitchen', 1),
(2, 'Außenbereich', 'outdoor', 1),
(2, 'Sanitäranlagen', 'bathroom', 1),

-- Dorfgemeinschaftshaus
(3, 'Parkmöglichkeiten', 'parking', 1),
(3, 'Küche vorhanden', 'kitchen', 1),
(3, 'Gemütliche Atmosphäre', 'cozy', 1),
(3, 'Sanitäranlagen', 'bathroom', 1),

-- Scheune am Waldrand
(4, 'WLAN verfügbar', 'wifi', 1),
(4, 'Parkmöglichkeiten', 'parking', 1),
(4, 'Außenbereich', 'outdoor', 1),
(4, 'Rustikaler Charme', 'rustic', 1),
(4, 'Ton- und Lichttechnik', 'audio', 1),
(4, 'Bar vorhanden', 'bar', 1);

-- Beispiel-Buchungen
INSERT INTO bookings (place_id, user_id, check_in, check_out, guests, total_price, status) VALUES
(
    1, -- Kulturraum Ottbergen
    4,
    '2025-12-15',
    '2025-12-20',
    80,
    1250.00,  -- 250 * 5 Tage
    'confirmed'
),
(
    2, -- Gemeindesaal St. Marien
    5,
    '2025-12-28',
    '2025-12-30',
    40,
    300.00,   -- 150 * 2 Tage
    'upcoming'
),
(
    3, -- Dorfgemeinschaftshaus
    4,
    '2026-01-10',
    '2026-01-12',
    30,
    240.00,   -- 120 * 2 Tage
    'pending'
);
