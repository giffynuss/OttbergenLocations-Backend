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
    // Pflichtfelder immer erforderlich
    $requiredFields = ['gender', 'firstName', 'lastName', 'email', 'phone'];

    foreach ($requiredFields as $field) {
        if (empty($userInfo[$field])) {
            return [
                'valid' => false,
                'error' => [
                    'code' => 'MISSING_USER_INFO',
                    'message' => "Pflichtfeld fehlt: {$field}"
                ]
            ];
        }
    }

    // Bei Überweisung sind Adressdaten erforderlich
    if ($paymentMethod === 'transfer') {
        $addressFields = ['street', 'postalCode', 'city'];
        foreach ($addressFields as $field) {
            if (empty($userInfo[$field])) {
                return [
                    'valid' => false,
                    'error' => [
                        'code' => 'MISSING_ADDRESS_INFO',
                        'message' => "Bei Überweisung erforderlich: {$field}"
                    ]
                ];
            }
        }
    }

    // E-Mail-Format validieren
    if (!filter_var($userInfo['email'], FILTER_VALIDATE_EMAIL)) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'INVALID_EMAIL',
                'message' => 'Ungültiges E-Mail-Format.'
            ]
        ];
    }

    // Gender validieren (nur 'herr' oder 'frau')
    $validGenders = ['herr', 'frau'];
    if (!in_array(strtolower($userInfo['gender']), $validGenders)) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'INVALID_GENDER',
                'message' => 'Gender muss "herr" oder "frau" sein.'
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
