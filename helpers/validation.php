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
