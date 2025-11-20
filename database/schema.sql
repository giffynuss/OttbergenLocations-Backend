-- OttbergenLocations Database Schema
-- Erstellt: 2025-11-20

-- Datenbank erstellen (falls noch nicht vorhanden)
CREATE DATABASE IF NOT EXISTS ottbergen_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ottbergen_booking;

-- Users Tabelle (existiert bereits, aber für Referenz)
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    street VARCHAR(255),
    house_number VARCHAR(10),
    zip_code VARCHAR(10),
    city VARCHAR(100),
    password_hash VARCHAR(255) NOT NULL,
    salt VARCHAR(255) NOT NULL,
    is_provider BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Providers Tabelle (Erweiterte Anbieter-Informationen)
CREATE TABLE IF NOT EXISTS providers (
    provider_id INT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    member_since DATE NOT NULL,
    avatar VARCHAR(500),
    verified BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Places Tabelle (Orte/Locations)
CREATE TABLE IF NOT EXISTS places (
    place_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    price_per_day DECIMAL(10, 2) NOT NULL,

    -- GPS & Adresse
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    address VARCHAR(255),
    postal_code VARCHAR(10),

    -- Anbieter-Beziehung
    provider_id INT NOT NULL,

    -- Status
    active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (provider_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_provider_id (provider_id),
    INDEX idx_active (active),
    INDEX idx_location (location),
    INDEX idx_capacity (capacity),
    INDEX idx_price (price_per_day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Place Images Tabelle (Bilder für Orte)
CREATE TABLE IF NOT EXISTS place_images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT NOT NULL,
    url VARCHAR(500) NOT NULL,
    thumbnail_url VARCHAR(500),
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (place_id) REFERENCES places(place_id) ON DELETE CASCADE,
    INDEX idx_place_id (place_id),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Place Features Tabelle (Ausstattung/Features)
CREATE TABLE IF NOT EXISTS place_features (
    feature_id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    icon VARCHAR(100),
    available BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (place_id) REFERENCES places(place_id) ON DELETE CASCADE,
    INDEX idx_place_id (place_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings Tabelle (Buchungen)
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT NOT NULL,
    user_id INT NOT NULL,

    -- Buchungszeitraum
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,

    -- Gäste
    guests INT NOT NULL,

    -- Preisberechnung
    subtotal DECIMAL(10, 2) NOT NULL,
    service_fee DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,

    -- Status
    status ENUM('pending', 'confirmed', 'upcoming', 'completed', 'cancelled') DEFAULT 'pending',

    -- Stornierung
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (place_id) REFERENCES places(place_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_place_id (place_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_check_in (check_in),
    INDEX idx_check_out (check_out),
    INDEX idx_dates (check_in, check_out)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Tabelle (Konfigurierbare Werte wie Gebühren)
CREATE TABLE IF NOT EXISTS settings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Standard-Einstellungen einfügen
INSERT INTO settings (setting_key, setting_value, description) VALUES
('service_fee_percentage', '0.05', 'Servicegebühr in Prozent (z.B. 0.05 = 5%)'),
('tax_percentage', '0.19', 'MwSt. in Prozent (z.B. 0.19 = 19%)'),
('cancellation_deadline_hours', '48', 'Kostenlose Stornierungsfrist in Stunden vor Check-in')
ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value);
