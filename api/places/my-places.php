<?php
// GET /api/places/my-places - Eigene Orte des eingeloggten Providers

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, OPTIONS");
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

// Nur GET-Requests erlauben
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

    // Alle Orte des eingeloggten Users laden
    $sql = "
        SELECT
            p.place_id as id,
            p.name,
            p.description,
            p.location,
            p.capacity,
            p.price_per_day as pricePerDay,
            p.latitude,
            p.longitude,
            p.address,
            p.postal_code as postalCode,
            p.active
        FROM places p
        WHERE p.user_id = :user_id
        ORDER BY p.place_id DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $userId]);
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Für jeden Ort: Bilder laden
    foreach ($places as &$place) {
        // Bilder laden
        $imgStmt = $conn->prepare("
            SELECT url
            FROM place_images
            WHERE place_id = :place_id
        ");
        $imgStmt->execute(['place_id' => $place['id']]);
        $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
        $place['images'] = array_column($images, 'url');

        // Features laden
        $featStmt = $conn->prepare("
            SELECT feature_id as id, name, icon, available
            FROM place_features
            WHERE place_id = :place_id
        ");
        $featStmt->execute(['place_id' => $place['id']]);
        $features = $featStmt->fetchAll(PDO::FETCH_ASSOC);

        // Boolean-Werte in features konvertieren
        foreach ($features as &$feature) {
            $feature['available'] = (bool)$feature['available'];
            $feature['id'] = (int)$feature['id'];
        }
        $place['features'] = $features;

        // Boolean-Werte konvertieren
        $place['active'] = (bool)$place['active'];

        // Numerische Werte konvertieren
        $place['id'] = (int)$place['id'];
        $place['capacity'] = (int)$place['capacity'];
        $place['pricePerDay'] = (float)$place['pricePerDay'];
        $place['latitude'] = $place['latitude'] ? (float)$place['latitude'] : null;
        $place['longitude'] = $place['longitude'] ? (float)$place['longitude'] : null;
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
            'message' => 'Serverfehler beim Laden der Orte'
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?>
