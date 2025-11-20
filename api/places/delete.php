<?php
// DELETE /api/places/delete.php?id={id} - Ort löschen (nur eigener Ort)

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

// Authentifizierung erforderlich
$userId = requireAuth();

// Nur DELETE-Requests erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Nur DELETE-Requests erlaubt.'
        ]
    ]);
    exit;
}

// Ort-ID aus Query-Parameter
$placeId = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$placeId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'MISSING_PLACE_ID',
            'message' => 'Ort-ID fehlt'
        ]
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Prüfen ob Ort existiert und dem User gehört
    $checkStmt = $conn->prepare("SELECT user_id FROM places WHERE place_id = :place_id");
    $checkStmt->execute(['place_id' => $placeId]);
    $place = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$place) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'PLACE_NOT_FOUND',
                'message' => 'Ort nicht gefunden'
            ]
        ]);
        exit;
    }

    if ($place['user_id'] != $userId) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'Keine Berechtigung zum Löschen dieses Ortes'
            ]
        ]);
        exit;
    }

    // Prüfen ob es aktive Buchungen gibt
    $bookingStmt = $conn->prepare("
        SELECT COUNT(*) as count
        FROM bookings
        WHERE place_id = :place_id
        AND status IN ('pending', 'confirmed', 'upcoming')
    ");
    $bookingStmt->execute(['place_id' => $placeId]);
    $bookingCount = $bookingStmt->fetch(PDO::FETCH_ASSOC);

    if ($bookingCount['count'] > 0) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'HAS_ACTIVE_BOOKINGS',
                'message' => 'Ort kann nicht gelöscht werden, da noch aktive Buchungen vorhanden sind'
            ]
        ]);
        exit;
    }

    // Ort löschen (CASCADE löscht automatisch Bilder und Features)
    $deleteStmt = $conn->prepare("DELETE FROM places WHERE place_id = :place_id");
    $deleteStmt->execute(['place_id' => $placeId]);

    echo json_encode([
        'success' => true,
        'message' => 'Ort erfolgreich gelöscht'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Serverfehler beim Löschen des Ortes'
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?>
