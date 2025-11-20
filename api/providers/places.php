<?php
// GET /api/providers/:id/places - Alle Orte eines Anbieters

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../../config/database.php';

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

    $providerId = isset($_GET['id']) ? intval($_GET['id']) : null;

    if (!$providerId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INVALID_ID',
                'message' => 'Ungültige Provider-ID.'
            ]
        ]);
        exit;
    }

    // Prüfen ob Provider existiert
    $providerStmt = $conn->prepare("
        SELECT user_id
        FROM users
        WHERE user_id = :provider_id AND is_provider = 1
    ");
    $providerStmt->execute(['provider_id' => $providerId]);

    if (!$providerStmt->fetch()) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'PROVIDER_NOT_FOUND',
                'message' => 'Anbieter nicht gefunden.'
            ]
        ]);
        exit;
    }

    // Orte des Providers laden
    $stmt = $conn->prepare("
        SELECT
            p.place_id as id,
            p.name,
            p.location,
            p.capacity,
            p.price_per_day as pricePerDay,
            p.active
        FROM places p
        WHERE p.provider_id = :provider_id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute(['provider_id' => $providerId]);
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Für jeden Ort: Erstes Bild laden
    foreach ($places as &$place) {
        $imgStmt = $conn->prepare("
            SELECT url
            FROM place_images
            WHERE place_id = :place_id
            ORDER BY sort_order ASC
            LIMIT 1
        ");
        $imgStmt->execute(['place_id' => $place['id']]);
        $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);
        $place['images'] = $images;

        // Datentypen konvertieren
        $place['id'] = (int)$place['id'];
        $place['capacity'] = (int)$place['capacity'];
        $place['pricePerDay'] = (float)$place['pricePerDay'];
        $place['active'] = (bool)$place['active'];
    }

    echo json_encode([
        'success' => true,
        'data' => $places,
        'total' => count($places)
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

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
