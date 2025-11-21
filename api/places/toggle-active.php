<?php
// PATCH /api/places/toggle-active.php?id={id} - Ort aktivieren/deaktivieren (nur eigener Ort)

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: PATCH, OPTIONS");
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

// Nur PATCH-Requests erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'PATCH') {
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
    $checkStmt = $conn->prepare("SELECT user_id, active FROM places WHERE place_id = :place_id");
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
                'message' => 'Keine Berechtigung zum Bearbeiten dieses Ortes'
            ]
        ]);
        exit;
    }

    // Status umschalten
    $newStatus = !$place['active'];

    $updateStmt = $conn->prepare("
        UPDATE places
        SET active = :active, updated_at = NOW()
        WHERE place_id = :place_id
    ");
    $updateStmt->execute([
        'active' => $newStatus,
        'place_id' => $placeId
    ]);

    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $placeId,
            'active' => (bool)$newStatus
        ],
        'message' => $newStatus ? 'Ort wurde aktiviert' : 'Ort wurde deaktiviert'
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Serverfehler beim Aktualisieren des Status'
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?>
