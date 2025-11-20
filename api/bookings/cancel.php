<?php
// PATCH /api/bookings/:id/cancel - Buchung stornieren

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
require_once __DIR__ . '/../../helpers/pricing.php';

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

    // Request Body parsen
    $input = json_decode(file_get_contents("php://input"), true);
    $reason = $input['reason'] ?? 'Keine Angabe';

    // Buchung laden
    $stmt = $conn->prepare("
        SELECT b.*, p.provider_id
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

    // Autorisierung: Nur der User selbst oder der Provider kann stornieren
    if ($booking['user_id'] != $userId && $booking['provider_id'] != $userId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'Keine Berechtigung für diese Aktion.'
            ]
        ]);
        exit;
    }

    // Status-Prüfung: Nur pending, confirmed oder upcoming können storniert werden
    $allowedStatuses = ['pending', 'confirmed', 'upcoming'];
    if (!in_array($booking['status'], $allowedStatuses)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INVALID_STATUS',
                'message' => "Buchungen mit Status '{$booking['status']}' können nicht storniert werden."
            ]
        ]);
        exit;
    }

    // Stornierungsfrist prüfen (Optional, aber empfohlen)
    $cancellationCheck = canCancelBooking($conn, $booking['check_in']);
    if (!$cancellationCheck['allowed']) {
        // Warnung, aber Stornierung trotzdem erlauben
        $warning = $cancellationCheck['message'];
    }

    // Buchung stornieren
    $updateStmt = $conn->prepare("
        UPDATE bookings
        SET status = 'cancelled',
            cancelled_at = NOW(),
            cancellation_reason = :reason,
            updated_at = NOW()
        WHERE booking_id = :booking_id
    ");
    $updateStmt->execute([
        'booking_id' => $bookingId,
        'reason' => $reason
    ]);

    // Aktualisierte Buchung laden
    $resultStmt = $conn->prepare("
        SELECT
            booking_id as id,
            status,
            cancelled_at as cancelledAt,
            cancellation_reason as cancellationReason,
            updated_at as updatedAt
        FROM bookings
        WHERE booking_id = :booking_id
    ");
    $resultStmt->execute(['booking_id' => $bookingId]);
    $result = $resultStmt->fetch(PDO::FETCH_ASSOC);

    $result['id'] = (int)$result['id'];

    $response = [
        'success' => true,
        'data' => $result,
        'message' => 'Buchung erfolgreich storniert.'
    ];

    if (isset($warning)) {
        $response['warning'] = $warning;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);

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
