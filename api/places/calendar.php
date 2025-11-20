<?php
// GET /api/places/:id/calendar - Buchungskalender

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/validation.php';

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
    $db = new Database();
    $conn = $db->getConnection();

    $placeId = isset($_GET['id']) ? intval($_GET['id']) : null;
    $months = isset($_GET['months']) ? intval($_GET['months']) : 12;

    if (!$placeId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INVALID_ID',
                'message' => 'Ungültige Ort-ID.'
            ]
        ]);
        exit;
    }

    // Prüfen ob Place existiert
    $placeValidation = validatePlace($conn, $placeId);
    if (!$placeValidation['valid']) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => $placeValidation['error']
        ]);
        exit;
    }

    // Buchungen für die nächsten X Monate laden
    $endDate = date('Y-m-d', strtotime("+{$months} months"));

    $stmt = $conn->prepare("
        SELECT check_in, check_out
        FROM bookings
        WHERE place_id = :place_id
        AND status IN ('confirmed', 'upcoming', 'pending')
        AND check_in <= :end_date
        ORDER BY check_in ASC
    ");
    $stmt->execute([
        'place_id' => $placeId,
        'end_date' => $endDate
    ]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Gebuchte Zeiträume formatieren
    $bookedDates = [];
    foreach ($bookings as $booking) {
        $bookedDates[] = [
            'start' => $booking['check_in'],
            'end' => $booking['check_out']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'placeId' => $placeId,
            'bookedDates' => $bookedDates
        ]
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
