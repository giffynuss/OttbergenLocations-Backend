<?php
// GET /api/places/:id/availability - Verfügbarkeitsprüfung

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
    $checkIn = $_GET['checkIn'] ?? null;
    $checkOut = $_GET['checkOut'] ?? null;

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

    if (!$checkIn || !$checkOut) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'MISSING_DATES',
                'message' => 'Check-in und Check-out Datum erforderlich.'
            ]
        ]);
        exit;
    }

    // Datumsbereich validieren
    $validation = validateDateRange($checkIn, $checkOut);
    if (!$validation['valid']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $validation['error']
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

    // Verfügbarkeit prüfen
    $availability = checkAvailability($conn, $placeId, $checkIn, $checkOut);

    if ($availability['available']) {
        echo json_encode([
            'success' => true,
            'available' => true,
            'blockedDates' => [],
            'message' => 'Der Ort ist im gewählten Zeitraum verfügbar.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => true,
            'available' => false,
            'blockedDates' => $availability['blockedDates'],
            'message' => 'Der Ort ist in diesem Zeitraum bereits gebucht.'
        ], JSON_UNESCAPED_UNICODE);
    }

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
