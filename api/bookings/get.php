<?php
// GET /api/bookings/get.php?id={bookingId} - Einzelne Buchung laden

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Nur GET-Requests erlaubt.'
        ]
    ]);
    exit;
}

try {
    // Authentifizierung erforderlich
    $userId = requireAuth();

    $db = new Database();
    $conn = $db->getConnection();

    $bookingId = $_GET['id'] ?? null;

    if (!$bookingId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'MISSING_ID',
                'message' => 'Buchungs-ID fehlt.'
            ]
        ]);
        exit;
    }

    // SQL Query mit JOIN
    $sql = "
        SELECT
            b.booking_id as id,
            b.place_id as placeId,
            p.name as placeName,
            p.location as placeLocation,
            b.user_id as userId,
            b.check_in as checkIn,
            b.check_out as checkOut,
            b.guests,
            b.total_price as totalPrice,
            b.payment_method as paymentMethod,
            b.booking_reference as bookingReference,
            b.status,
            b.cancelled_at as cancelledAt,
            b.cancellation_reason as cancellationReason,
            p.user_id as providerId
        FROM bookings b
        LEFT JOIN places p ON b.place_id = p.place_id
        WHERE b.booking_id = :bookingId
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':bookingId', $bookingId, PDO::PARAM_INT);
    $stmt->execute();
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

    // Berechtigungsprüfung: User ist Owner oder Provider
    if ($booking['userId'] != $userId && $booking['providerId'] != $userId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'Sie haben keine Berechtigung, diese Buchung zu sehen.'
            ]
        ]);
        exit;
    }

    // Entferne interne Felder
    unset($booking['providerId']);

    // Datentypen konvertieren
    $booking['id'] = (int)$booking['id'];
    $booking['placeId'] = (int)$booking['placeId'];
    $booking['userId'] = (int)$booking['userId'];
    $booking['guests'] = (int)$booking['guests'];
    $booking['totalPrice'] = (float)$booking['totalPrice'];

    // Response
    echo json_encode([
        'success' => true,
        'booking' => $booking
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
