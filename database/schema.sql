-- OttbergenLocations Database Schema
-- Erstellt: 2025-11-20
-- Aktualisiert: 2025-11-24 - Checkout-Funktionalität hinzugefügt

-- Datenbank erstellen (falls noch nicht vorhanden)
CREATE DATABASE IF NOT EXISTS ottbergen_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ottbergen_booking;

-- Users Tabelle
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    gender ENUM('herr', 'frau') NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    street VARCHAR(255),
    house_number VARCHAR(10),
    zip_code VARCHAR(10),
    city VARCHAR(100),
    password_hash VARCHAR(255) NOT NULL,
    salt VARCHAR(255) NOT NULL,
    is_provider BOOLEAN DEFAULT 0,
    INDEX idx_email (email),
    INDEX idx_is_provider (is_provider)
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

    -- User-Beziehung (Provider)
    user_id INT NOT NULL,

    -- Status
    active BOOLEAN DEFAULT 1,

    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
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
    FOREIGN KEY (place_id) REFERENCES places(place_id) ON DELETE CASCADE,
    INDEX idx_place_id (place_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Place Features Tabelle (Ausstattung/Features)
CREATE TABLE IF NOT EXISTS place_features (
    feature_id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    icon VARCHAR(100),
    available BOOLEAN DEFAULT 1,
    FOREIGN KEY (place_id) REFERENCES places(place_id) ON DELETE CASCADE,
    INDEX idx_place_id (place_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings Tabelle (Buchungen)
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT NOT NULL,
    user_id INT NULL,  -- NULL erlaubt für Gast-Buchungen

    -- Buchungszeitraum
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,

    -- Gäste
    guests INT NOT NULL,

    -- Preis & Zahlung
    total_price DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'paypal', 'transfer', 'wero') DEFAULT 'cash',
    booking_reference VARCHAR(50) UNIQUE,

    -- Status (inkl. 'rejected' für E-Mail-basierte Ablehnung)
    status ENUM('pending', 'confirmed', 'rejected', 'upcoming', 'completed', 'cancelled') DEFAULT 'pending',

    -- E-Mail-Bestätigung
    confirmation_token VARCHAR(64) DEFAULT NULL COMMENT 'Token für Bestätigung/Ablehnung per E-Mail',

    -- Stornierung
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT,

    FOREIGN KEY (place_id) REFERENCES places(place_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_place_id (place_id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_check_in (check_in),
    INDEX idx_check_out (check_out),
    INDEX idx_dates (check_in, check_out),
    INDEX idx_booking_reference (booking_reference),
    INDEX idx_confirmation_token (confirmation_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Booking Guest Info Tabelle (Gast-Informationen für Buchungen)
CREATE TABLE IF NOT EXISTS booking_guest_info (
    guest_info_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,

    -- Kontaktdaten (Pflichtfelder)
    gender ENUM('herr', 'frau') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,

    -- Adresse (Optional, nur bei Zahlungsart "transfer")
    street VARCHAR(255),
    postal_code VARCHAR(10),
    city VARCHAR(100),

    -- Zeitstempel
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
