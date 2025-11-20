<?php
// GET /api/places/:id - Detailansicht eines Ortes

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

    // ID aus URL extrahieren (z.B. /api/places/detail.php?id=1)
    $placeId = isset($_GET['id']) ? intval($_GET['id']) : null;

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

    // Place laden
    $stmt = $conn->prepare("
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
            p.active,
            p.created_at as createdAt,
            p.updated_at as updatedAt,
            u.user_id as providerId,
            u.email as providerEmail,
            CONCAT(u.first_name, ' ', u.last_name) as providerName,
            u.phone as providerPhone,
            pr.member_since as providerMemberSince,
            pr.avatar as providerAvatar,
            pr.verified as providerVerified
        FROM places p
        LEFT JOIN users u ON p.provider_id = u.user_id
        LEFT JOIN providers pr ON u.user_id = pr.user_id
        WHERE p.place_id = :place_id
    ");
    $stmt->execute(['place_id' => $placeId]);
    $place = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$place) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'PLACE_NOT_FOUND',
                'message' => 'Ort nicht gefunden.'
            ]
        ]);
        exit;
    }

    // Bilder laden
    $imgStmt = $conn->prepare("
        SELECT url, thumbnail_url as thumbnailUrl
        FROM place_images
        WHERE place_id = :place_id
        ORDER BY sort_order ASC
    ");
    $imgStmt->execute(['place_id' => $placeId]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    $place['images'] = array_column($images, 'url');

    // Features laden
    $featStmt = $conn->prepare("
        SELECT
            feature_id as id,
            name,
            icon,
            available
        FROM place_features
        WHERE place_id = :place_id
        ORDER BY feature_id ASC
    ");
    $featStmt->execute(['place_id' => $placeId]);
    $features = $featStmt->fetchAll(PDO::FETCH_ASSOC);

    // Features formatieren
    foreach ($features as &$feature) {
        $feature['id'] = (int)$feature['id'];
        $feature['available'] = (bool)$feature['available'];
    }
    $place['features'] = $features;

    // Provider-Objekt formatieren
    $place['provider'] = [
        'id' => (int)$place['providerId'],
        'name' => $place['providerName'],
        'email' => $place['providerEmail'],
        'phone' => $place['providerPhone'],
        'memberSince' => $place['providerMemberSince'],
        'avatar' => $place['providerAvatar'],
        'verified' => (bool)$place['providerVerified']
    ];

    // Unbenötigte Felder entfernen
    unset(
        $place['providerId'],
        $place['providerName'],
        $place['providerEmail'],
        $place['providerPhone'],
        $place['providerMemberSince'],
        $place['providerAvatar'],
        $place['providerVerified']
    );

    // Datentypen konvertieren
    $place['id'] = (int)$place['id'];
    $place['capacity'] = (int)$place['capacity'];
    $place['pricePerDay'] = (float)$place['pricePerDay'];
    $place['latitude'] = $place['latitude'] ? (float)$place['latitude'] : null;
    $place['longitude'] = $place['longitude'] ? (float)$place['longitude'] : null;
    $place['active'] = (bool)$place['active'];

    echo json_encode([
        'success' => true,
        'data' => $place
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
