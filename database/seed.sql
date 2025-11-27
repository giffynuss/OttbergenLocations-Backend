-- Seed-Daten für OttbergenLocations
-- Entwicklungs- und Test-Daten

USE ottbergen_booking;

-- Test-User erstellen
-- WICHTIG: Passwörter sind gehashed mit SHA256 + Salt
-- Alle Test-Accounts haben das Passwort: "Test123!"

-- User 1: Max Mustermann (Provider)
INSERT INTO users (user_id, first_name, last_name, gender, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(1, 'Max', 'Mustermann', 'herr', 'max.mustermann@example.com', '+49 5272 123456', 'Hauptstraße', '45', '37691', 'Ottbergen',
 SHA2(CONCAT('Test123!', 'salt1'), 256), 'salt1', 1)
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- User 2: Maria Schmidt (Provider)
INSERT INTO users (user_id, first_name, last_name, gender, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(2, 'Maria', 'Schmidt', 'frau', 'maria.schmidt@example.com', '+49 5272 234567', 'Kirchweg', '12', '37691', 'Ottbergen',
 SHA2(CONCAT('Test123!', 'salt2'), 256), 'salt2', 1)
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- User 3: Hans Müller (Provider)
INSERT INTO users (user_id, first_name, last_name, gender, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(3, 'Hans', 'Müller', 'herr', 'hans.mueller@example.com', '+49 5272 345678', 'Waldweg', '23', '37691', 'Ottbergen',
 SHA2(CONCAT('Test123!', 'salt3'), 256), 'salt3', 1)
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- User 4: Anna Wagner (Normaler User/Kunde)
INSERT INTO users (user_id, first_name, last_name, gender, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(4, 'Anna', 'Wagner', 'frau', 'anna.wagner@example.com', '+49 5272 456789', 'Bergstraße', '7', '37691', 'Ottbergen',
 SHA2(CONCAT('Test123!', 'salt4'), 256), 'salt4', 0)
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- User 5: Thomas Klein (Normaler User/Kunde)
INSERT INTO users (user_id, first_name, last_name, gender, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(5, 'Thomas', 'Klein', 'herr', 'thomas.klein@example.com', '+49 5272 567890', 'Dorfstraße', '15', '37691', 'Ottbergen',
 SHA2(CONCAT('Test123!', 'salt5'), 256), 'salt5', 0)
ON DUPLICATE KEY UPDATE email=VALUES(email);

INSERT INTO users (user_id, first_name, last_name, gender, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
VALUES
(6, 'Patryk', 'Bulla', 'herr', 'patrykbulla980@gmail.com', '+49 5272 567890', 'Dorfstraße', '15', '37691', 'Ottbergen',
 SHA2(CONCAT('Passwort123?', 'salt6'), 256), 'salt6', 0)
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
    'Katzenscheune',
    'Gemütliche Scheune mit rustikalem Charme. Ideal für private Feiern im kleinen Kreis. Voll ausgestattete Küche vorhanden.',
    'Geheimort',
    40,
    100.00,
    40.7298,
    9.3423,
    'Dorfstraße 8',
    '37691',
    6,
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

-- 1. Kulturraum Ottbergen (modern, hell, Kultur & Kurse)
(1, 'https://images.unsplash.com/photo-1533090161767-e6ffed986c88?w=900'), -- moderner Veranstaltungsraum
(1, 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?w=900'), -- Workshop-/Seminarraum
(1, 'https://images.unsplash.com/photo-1529107386315-e1a2ed48a620?w=900'), -- Kulturveranstaltung / Meetingraum

-- 2. Gemeindesaal St. Marien (kirchennah, hell, Saal für Feiern)
(2, 'https://images.unsplash.com/photo-1520880867055-1e30d1cb001c?w=900'), -- Gemeindesaal stilvoll gedeckt
(2, 'https://images.unsplash.com/photo-1520483691742-8aee3564a1b0?w=900'), -- ruhiger, heller Saal
(2, 'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=900'), -- offener Raum für Veranstaltungen

-- 3. Dorfgemeinschaftshaus (bodenständig, rustikal aber modern)
(3, 'https://images.unsplash.com/photo-1531058020387-3be344556be6?w=900'), -- rustikaler Mehrzweckraum
(3, 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?w=900'), -- Vereins-/Dorfsaal
(3, 'https://images.unsplash.com/photo-1560448075-bb4b1d1ea24b?w=900'), -- einfache Feier-/Sitzmöglichkeit

-- 4. Scheune am Waldrand (Scheune, Waldnähe, rustikal, warm)
(4, 'https://images.unsplash.com/photo-1506784983877-45594efa4cbe?w=900'), -- Scheune innen, rustikal
(4, 'https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=900'), -- Scheunenfeier / Landhausstil
(4, 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?w=900'); -- Waldnähe / Naturstimmung

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
INSERT INTO bookings (place_id, user_id, check_in, check_out, guests, total_price, payment_method, booking_reference, status) VALUES
(
    1, -- Kulturraum Ottbergen
    4,
    '2025-12-15',
    '2025-12-20',
    80,
    1250.00,  -- 250 * 5 Tage
    'transfer',
    'BK20251120-0001',
    'confirmed'
),
(
    2, -- Gemeindesaal St. Marien
    5,
    '2025-12-28',
    '2025-12-30',
    40,
    300.00,   -- 150 * 2 Tage
    'cash',
    'BK20251120-0002',
    'upcoming'
),
(
    3, -- Dorfgemeinschaftshaus
    NULL, -- Gast-Buchung
    '2026-01-10',
    '2026-01-12',
    30,
    240.00,   -- 120 * 2 Tage
    'cash',
    'BK20251120-0003',
    'pending'
);

-- Gast-Informationen für Buchung 3 (Gast-Buchung ohne User-Account)
INSERT INTO booking_guest_info (booking_id, gender, first_name, last_name, email, phone) VALUES
(3, 'herr', 'Gast', 'User', 'gast@example.com', '+49 555 123456');
