<?php
// PATCH /api/bookings/:id/confirm - Buchung bestätigen (nur für Provider)

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Preflight-Request behandeln
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PATCH' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Nur PATCH-Requests erlaubt.'
        ]
    ]);
    exit;
}

try {
    // Authentifizierung erforderlich
    $userId = requireAuth();

    $db = new Database();
    $conn = $db->getConnection();

    $bookingId = isset($_GET['id']) ? intval($_GET['id']) : null;

    if (!$bookingId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INVALID_ID',
                'message' => 'Ungültige Buchungs-ID.'
            ]
        ]);
        exit;
    }

    // Buchung laden
    $stmt = $conn->prepare("
        SELECT b.*, p.user_id as place_user_id
        FROM bookings b
        JOIN places p ON b.place_id = p.place_id
        WHERE b.booking_id = :booking_id
    ");
    $stmt->execute(['booking_id' => $bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'BOOKING_NOT_FOUND',
                'message' => 'Buchung nicht gefunden.'
            ]
        ]);
        exit;
    }

    // Autorisierung: Nur der Provider des Ortes darf bestätigen
    if ($booking['place_user_id'] != $userId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'Nur der Anbieter kann diese Buchung bestätigen.'
            ]
        ]);
        exit;
    }

    // Status-Prüfung: Nur pending-Buchungen können bestätigt werden
    if ($booking['status'] !== 'pending') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INVALID_STATUS',
                'message' => "Nur Buchungen mit Status 'pending' können bestätigt werden. Aktueller Status: '{$booking['status']}'."
            ]
        ]);
        exit;
    }

    // Prüfen ob Check-in in weniger als 7 Tagen ist
    $checkInDate = new DateTime($booking['check_in']);
    $now = new DateTime();
    $interval = $now->diff($checkInDate);
    $daysUntilCheckIn = $interval->days;

    $newStatus = ($daysUntilCheckIn <= 7) ? 'upcoming' : 'confirmed';

    // Buchung bestätigen
    $updateStmt = $conn->prepare("
        UPDATE bookings
        SET status = :status
        WHERE booking_id = :booking_id
    ");
    $updateStmt->execute([
        'booking_id' => $bookingId,
        'status' => $newStatus
    ]);

    // Aktualisierte Buchung laden
    $resultStmt = $conn->prepare("
        SELECT
            booking_id as id,
            status
        FROM bookings
        WHERE booking_id = :booking_id
    ");
    $resultStmt->execute(['booking_id' => $bookingId]);
    $result = $resultStmt->fetch(PDO::FETCH_ASSOC);

    $result['id'] = (int)$result['id'];

    echo json_encode([
        'success' => true,
        'data' => $result,
        'message' => 'Buchung erfolgreich bestätigt.'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Serverfehler: ' . $e->getMessage()
        ]
    ], JSON_UNESCAPED_UNICODE);
}
