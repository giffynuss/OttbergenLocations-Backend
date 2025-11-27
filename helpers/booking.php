<?php
// Booking Helper - Hilfsfunktionen für Buchungen

/**
 * Generiert eine eindeutige Buchungsreferenz im Format BK{YYYYMMDD}-{random4digits}
 * @param PDO $conn Datenbankverbindung
 * @return string Eindeutige Buchungsreferenz
 */
function generateBookingReference($conn) {
    $maxRetries = 10;
    $attempt = 0;

    while ($attempt < $maxRetries) {
        // Format: BK20251124-1234
        $date = date('Ymd');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $reference = "BK{$date}-{$random}";

        // Prüfen ob Referenz bereits existiert
        $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE booking_reference = :ref");
        $stmt->execute(['ref' => $reference]);
        $count = $stmt->fetchColumn();

        if ($count == 0) {
            return $reference;
        }

        $attempt++;
    }

    // Falls nach 10 Versuchen keine eindeutige Referenz gefunden wurde
    throw new Exception('Konnte keine eindeutige Buchungsreferenz generieren.');
}

/**
 * Gibt Mock-Bankdaten für Überweisungen zurück
 * @return array Bankverbindungsdaten
 */
function getMockBankDetails() {
    return [
        'accountHolder' => 'Ottbergen Locations GmbH',
        'iban' => 'DE89 3704 0044 0532 0130 00',
        'bic' => 'COBADEFFXXX',
        'bankName' => 'Commerzbank'
    ];
}

/**
 * Validiert User-Info für Buchung
 * @param array $userInfo User-Informationen aus Request
 * @param string $paymentMethod Zahlungsmethode
 * @return array ['valid' => bool, 'error' => array|null]
 */
function validateUserInfo($userInfo, $paymentMethod) {
    require_once __DIR__ . '/validation.php';

    $errors = [];

    // Gender validieren
    $genderValidation = validateGender($userInfo['gender'] ?? '');
    if (!$genderValidation['valid']) {
        $errors['gender'] = $genderValidation['error'];
    }

    // Vorname validieren
    $firstNameValidation = validateName($userInfo['firstName'] ?? '', 'Vorname');
    if (!$firstNameValidation['valid']) {
        $errors['firstName'] = $firstNameValidation['error'];
    }

    // Nachname validieren
    $lastNameValidation = validateName($userInfo['lastName'] ?? '', 'Nachname');
    if (!$lastNameValidation['valid']) {
        $errors['lastName'] = $lastNameValidation['error'];
    }

    // E-Mail validieren
    $emailValidation = validateEmail($userInfo['email'] ?? '');
    if (!$emailValidation['valid']) {
        $errors['email'] = $emailValidation['error'];
    }

    // Telefon validieren
    $phoneValidation = validatePhone($userInfo['phone'] ?? '');
    if (!$phoneValidation['valid']) {
        $errors['phone'] = $phoneValidation['error'];
    }

    // Bei Überweisung sind Adressdaten erforderlich
    if ($paymentMethod === 'transfer') {
        // Straße
        if (empty($userInfo['street'])) {
            $errors['street'] = 'Dieses Feld ist erforderlich';
        }

        // Hausnummer (optional prüfen ob vorhanden)
        if (isset($userInfo['houseNumber']) && empty($userInfo['houseNumber'])) {
            $errors['houseNumber'] = 'Hausnummer ist erforderlich';
        }

        // PLZ validieren
        $zipCodeValidation = validateZipCode($userInfo['postalCode'] ?? '');
        if (!$zipCodeValidation['valid']) {
            $errors['postalCode'] = $zipCodeValidation['error'];
        }

        // Stadt
        if (empty($userInfo['city'])) {
            $errors['city'] = 'Dieses Feld ist erforderlich';
        }
    }

    if (!empty($errors)) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'VALIDATION_FAILED',
                'message' => 'Validierung fehlgeschlagen',
                'details' => $errors
            ]
        ];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Speichert Gast-Informationen für eine Buchung
 * @param PDO $conn Datenbankverbindung
 * @param int $bookingId Buchungs-ID
 * @param array $userInfo User-Informationen
 * @return bool Erfolg
 */
function saveGuestInfo($conn, $bookingId, $userInfo) {
    $stmt = $conn->prepare("
        INSERT INTO booking_guest_info
        (booking_id, gender, first_name, last_name, email, phone, street, postal_code, city)
        VALUES
        (:booking_id, :gender, :first_name, :last_name, :email, :phone, :street, :postal_code, :city)
    ");

    return $stmt->execute([
        'booking_id' => $bookingId,
        'gender' => strtolower($userInfo['gender']),
        'first_name' => $userInfo['firstName'],
        'last_name' => $userInfo['lastName'],
        'email' => $userInfo['email'],
        'phone' => $userInfo['phone'],
        'street' => $userInfo['street'] ?? null,
        'postal_code' => $userInfo['postalCode'] ?? null,
        'city' => $userInfo['city'] ?? null
    ]);
}

/**
 * Lädt Gast-Informationen für eine Buchung
 * @param PDO $conn Datenbankverbindung
 * @param int $bookingId Buchungs-ID
 * @return array|null Gast-Informationen oder null
 */
function getGuestInfo($conn, $bookingId) {
    $stmt = $conn->prepare("
        SELECT
            gender,
            first_name as firstName,
            last_name as lastName,
            email,
            phone,
            street,
            postal_code as postalCode,
            city
        FROM booking_guest_info
        WHERE booking_id = :booking_id
    ");
    $stmt->execute(['booking_id' => $bookingId]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        return null;
    }

    // Null-Werte für optionale Felder entfernen
    return array_filter($info, function($value) {
        return $value !== null;
    });
}
