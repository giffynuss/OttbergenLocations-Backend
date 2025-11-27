<?php
// Validation Helper - Validierungsfunktionen

/**
 * Validiert einen Datumsbereich
 * @param string $checkIn Check-in Datum
 * @param string $checkOut Check-out Datum
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateDateRange($checkIn, $checkOut) {
    $checkInDate = strtotime($checkIn);
    $checkOutDate = strtotime($checkOut);
    $today = strtotime(date('Y-m-d'));

    if (!$checkInDate || !$checkOutDate) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'INVALID_DATE_FORMAT',
                'message' => 'Ungültiges Datumsformat. Verwenden Sie YYYY-MM-DD.'
            ]
        ];
    }

    if ($checkInDate < $today) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'INVALID_DATE_RANGE',
                'message' => 'Check-in Datum darf nicht in der Vergangenheit liegen.'
            ]
        ];
    }

    if ($checkOutDate <= $checkInDate) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'INVALID_DATE_RANGE',
                'message' => 'Check-out Datum muss nach dem Check-in Datum liegen.'
            ]
        ];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Prüft ob ein Ort im angegebenen Zeitraum verfügbar ist
 * @param PDO $conn Datenbankverbindung
 * @param int $placeId Ort-ID
 * @param string $checkIn Check-in Datum
 * @param string $checkOut Check-out Datum
 * @param int|null $excludeBookingId Buchungs-ID die ignoriert werden soll (bei Updates)
 * @return array ['available' => bool, 'blockedDates' => array]
 */
function checkAvailability($conn, $placeId, $checkIn, $checkOut, $excludeBookingId = null) {
    $sql = "
        SELECT check_in, check_out
        FROM bookings
        WHERE place_id = :place_id
        AND status IN ('confirmed', 'upcoming', 'pending')
        AND (
            (check_in < :check_out AND check_out > :check_in)
        )
    ";

    $params = [
        'place_id' => $placeId,
        'check_in' => $checkIn,
        'check_out' => $checkOut
    ];

    if ($excludeBookingId) {
        $sql .= " AND booking_id != :exclude_booking_id";
        $params['exclude_booking_id'] = $excludeBookingId;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($conflicts)) {
        return ['available' => true, 'blockedDates' => []];
    }

    // Blockierte Daten sammeln
    $blockedDates = [];
    foreach ($conflicts as $conflict) {
        $start = new DateTime($conflict['check_in']);
        $end = new DateTime($conflict['check_out']);

        while ($start < $end) {
            $blockedDates[] = $start->format('Y-m-d');
            $start->modify('+1 day');
        }
    }

    return [
        'available' => false,
        'blockedDates' => array_unique($blockedDates)
    ];
}

/**
 * Validiert die Gästeanzahl für einen Ort
 * @param PDO $conn Datenbankverbindung
 * @param int $placeId Ort-ID
 * @param int $guests Anzahl Gäste
 * @return array ['valid' => bool, 'error' => array|null]
 */
function validateGuestCount($conn, $placeId, $guests) {
    $stmt = $conn->prepare("SELECT capacity FROM places WHERE place_id = :place_id");
    $stmt->execute(['place_id' => $placeId]);
    $place = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$place) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'PLACE_NOT_FOUND',
                'message' => 'Ort nicht gefunden.'
            ]
        ];
    }

    if ($guests > $place['capacity']) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'CAPACITY_EXCEEDED',
                'message' => "Die maximale Kapazität von {$place['capacity']} Personen wurde überschritten."
            ]
        ];
    }

    if ($guests < 1) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'INVALID_GUEST_COUNT',
                'message' => 'Es muss mindestens 1 Gast angegeben werden.'
            ]
        ];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Prüft ob ein Ort existiert und aktiv ist
 * @param PDO $conn Datenbankverbindung
 * @param int $placeId Ort-ID
 * @return array ['valid' => bool, 'error' => array|null, 'place' => array|null]
 */
function validatePlace($conn, $placeId) {
    $stmt = $conn->prepare("SELECT * FROM places WHERE place_id = :place_id");
    $stmt->execute(['place_id' => $placeId]);
    $place = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$place) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'PLACE_NOT_FOUND',
                'message' => 'Ort nicht gefunden.'
            ],
            'place' => null
        ];
    }

    if (!$place['active']) {
        return [
            'valid' => false,
            'error' => [
                'code' => 'PLACE_NOT_ACTIVE',
                'message' => 'Dieser Ort ist derzeit nicht buchbar.'
            ],
            'place' => null
        ];
    }

    return ['valid' => true, 'error' => null, 'place' => $place];
}

/**
 * Validiert einen Namen (Vorname oder Nachname)
 * @param string $name Name
 * @param string $fieldName Feldname für Fehlermeldung
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateName($name, $fieldName = 'Name') {
    if (empty($name) || strlen(trim($name)) < 2) {
        return [
            'valid' => false,
            'error' => "{$fieldName} muss mindestens 2 Zeichen lang sein"
        ];
    }
    return ['valid' => true, 'error' => null];
}

/**
 * Validiert eine E-Mail-Adresse
 * @param string $email E-Mail
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateEmail($email) {
    // Frontend regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'valid' => false,
            'error' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein'
        ];
    }
    return ['valid' => true, 'error' => null];
}

/**
 * Validiert eine Telefonnummer
 * Frontend regex: /^[\d\s+()-]+$/ mit min. 6 Zeichen
 * @param string $phone Telefonnummer
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validatePhone($phone) {
    if (empty($phone)) {
        return [
            'valid' => false,
            'error' => 'Bitte geben Sie eine gültige Telefonnummer ein'
        ];
    }

    // Mindestlänge 6 Zeichen
    if (strlen(trim($phone)) < 6) {
        return [
            'valid' => false,
            'error' => 'Bitte geben Sie eine gültige Telefonnummer ein'
        ];
    }

    // Format: nur Zahlen, Leerzeichen, +, -, (, )
    if (!preg_match('/^[\d\s+()-]+$/', $phone)) {
        return [
            'valid' => false,
            'error' => 'Bitte geben Sie eine gültige Telefonnummer ein'
        ];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Validiert eine PLZ (muss exakt 5 Ziffern sein)
 * Frontend regex: /^\d{5}$/
 * @param string $zipCode PLZ
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateZipCode($zipCode) {
    if (empty($zipCode) || !preg_match('/^\d{5}$/', $zipCode)) {
        return [
            'valid' => false,
            'error' => 'PLZ muss 5 Ziffern haben'
        ];
    }
    return ['valid' => true, 'error' => null];
}

/**
 * Validiert ein Passwort (Registrierung)
 * Frontend-Regeln:
 * - Mindestens 10 Zeichen
 * - Mindestens 1 Kleinbuchstabe
 * - Mindestens 1 Großbuchstabe
 * - Mindestens 1 Zahl
 * - Mindestens 1 Sonderzeichen: !@#$%^&*+(),.?":{}|<>_-
 * @param string $password Passwort
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validatePassword($password) {
    $errors = [];

    if (strlen($password) < 10) {
        $errors[] = 'Passwort muss mindestens 10 Zeichen lang sein';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Passwort muss mindestens einen Kleinbuchstaben enthalten';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Passwort muss mindestens einen Großbuchstaben enthalten';
    }

    if (!preg_match('/\d/', $password)) {
        $errors[] = 'Passwort muss mindestens eine Zahl enthalten';
    }

    if (!preg_match('/[!@#$%^&*+(),.?":{}|<>_\-]/', $password)) {
        $errors[] = 'Passwort muss mindestens ein Sonderzeichen enthalten (!@#$%^&*+(),.?":{}|<>_-)';
    }

    if (!empty($errors)) {
        return [
            'valid' => false,
            'error' => implode('. ', $errors)
        ];
    }

    return ['valid' => true, 'error' => null];
}

/**
 * Validiert die Anrede (Gender)
 * @param string $gender Anrede
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateGender($gender) {
    $validGenders = ['herr', 'frau'];
    if (empty($gender) || !in_array(strtolower($gender), $validGenders)) {
        return [
            'valid' => false,
            'error' => 'Anrede muss "herr" oder "frau" sein'
        ];
    }
    return ['valid' => true, 'error' => null];
}

/**
 * Validiert Registrierungsdaten komplett
 * @param array $data Registrierungsdaten
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateRegistrationData($data) {
    $errors = [];

    // Vorname
    $firstNameValidation = validateName($data['firstName'] ?? '', 'Vorname');
    if (!$firstNameValidation['valid']) {
        $errors['firstName'] = $firstNameValidation['error'];
    }

    // Nachname
    $lastNameValidation = validateName($data['lastName'] ?? '', 'Nachname');
    if (!$lastNameValidation['valid']) {
        $errors['lastName'] = $lastNameValidation['error'];
    }

    // Anrede
    $genderValidation = validateGender($data['gender'] ?? '');
    if (!$genderValidation['valid']) {
        $errors['gender'] = $genderValidation['error'];
    }

    // E-Mail
    $emailValidation = validateEmail($data['email'] ?? '');
    if (!$emailValidation['valid']) {
        $errors['email'] = $emailValidation['error'];
    }

    // Telefon
    $phoneValidation = validatePhone($data['phone'] ?? '');
    if (!$phoneValidation['valid']) {
        $errors['phone'] = $phoneValidation['error'];
    }

    // Straße
    if (empty($data['street'])) {
        $errors['street'] = 'Dieses Feld ist erforderlich';
    }

    // Hausnummer
    if (empty($data['houseNumber'])) {
        $errors['houseNumber'] = 'Hausnummer ist erforderlich';
    }

    // PLZ
    $zipCodeValidation = validateZipCode($data['zipCode'] ?? '');
    if (!$zipCodeValidation['valid']) {
        $errors['zipCode'] = $zipCodeValidation['error'];
    }

    // Stadt
    if (empty($data['city'])) {
        $errors['city'] = 'Dieses Feld ist erforderlich';
    }

    // Passwort
    $passwordValidation = validatePassword($data['password'] ?? '');
    if (!$passwordValidation['valid']) {
        $errors['password'] = $passwordValidation['error'];
    }

    // Passwort-Bestätigung
    if (isset($data['confirmPassword']) && $data['password'] !== $data['confirmPassword']) {
        $errors['confirmPassword'] = 'Passwörter stimmen nicht überein';
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
