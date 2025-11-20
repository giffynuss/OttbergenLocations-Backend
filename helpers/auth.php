<?php
// Auth Helper - Authentifizierung und Autorisierung

/**
 * Prüft ob ein User eingeloggt ist
 * @return int|null User-ID oder null wenn nicht eingeloggt
 */
function requireAuth() {
    session_start();

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'UNAUTHORIZED',
                'message' => 'Nicht authentifiziert. Bitte melden Sie sich an.'
            ]
        ]);
        exit;
    }

    return $_SESSION['user_id'];
}

/**
 * Gibt die User-ID des eingeloggten Users zurück (ohne zu beenden)
 * @return int|null
 */
function getCurrentUserId() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return $_SESSION['user_id'] ?? null;
}

/**
 * Prüft ob der User ein Provider ist
 * @return bool
 */
function isProvider() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['is_provider']) && $_SESSION['is_provider'] == 1;
}

/**
 * Prüft ob der User Zugriff auf einen bestimmten Ort hat (ist der Anbieter)
 * @param PDO $conn Datenbankverbindung
 * @param int $placeId Ort-ID
 * @param int $userId User-ID
 * @return bool
 */
function canAccessPlace($conn, $placeId, $userId) {
    $stmt = $conn->prepare("SELECT user_id FROM places WHERE place_id = :place_id");
    $stmt->execute(['place_id' => $placeId]);
    $place = $stmt->fetch(PDO::FETCH_ASSOC);

    return $place && $place['user_id'] == $userId;
}

/**
 * Prüft ob der User Zugriff auf eine Buchung hat
 * @param PDO $conn Datenbankverbindung
 * @param int $bookingId Buchungs-ID
 * @param int $userId User-ID
 * @return bool
 */
function canAccessBooking($conn, $bookingId, $userId) {
    $stmt = $conn->prepare("
        SELECT b.user_id, p.user_id as place_user_id
        FROM bookings b
        JOIN places p ON b.place_id = p.place_id
        WHERE b.booking_id = :booking_id
    ");
    $stmt->execute(['booking_id' => $bookingId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) return false;

    // User ist entweder der Buchende oder der Anbieter des Ortes
    return $result['user_id'] == $userId || $result['place_user_id'] == $userId;
}
