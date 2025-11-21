<?php
// POST /api/places/create - Neuen Ort erstellen (nur Provider)

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
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

// Nur POST-Requests erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Nur POST-Requests erlaubt.'
        ]
    ]);
    exit;
}

// Input validieren
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INVALID_JSON',
            'message' => 'Ungültige JSON-Daten'
        ]
    ]);
    exit;
}

// Pflichtfelder prüfen
$required = ['name', 'description', 'location', 'capacity', 'pricePerDay'];
$missing = [];

foreach ($required as $field) {
    if (!isset($input[$field]) || trim($input[$field]) === '') {
        $missing[] = $field;
    }
}

if (!empty($missing)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'MISSING_FIELDS',
            'message' => 'Pflichtfelder fehlen: ' . implode(', ', $missing)
        ]
    ]);
    exit;
}

// Validierung
if ($input['capacity'] < 1) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INVALID_CAPACITY',
            'message' => 'Kapazität muss mindestens 1 sein'
        ]
    ]);
    exit;
}

if ($input['pricePerDay'] < 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'INVALID_PRICE',
            'message' => 'Preis muss positiv sein'
        ]
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Prüfen ob User Provider ist
    $userStmt = $conn->prepare("SELECT is_provider FROM users WHERE user_id = :user_id");
    $userStmt->execute(['user_id' => $userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !$user['is_provider']) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'NOT_A_PROVIDER',
                'message' => 'Nur Provider können Orte erstellen'
            ]
        ]);
        exit;
    }

    // Ort erstellen
    $stmt = $conn->prepare("
        INSERT INTO places
        (name, description, location, capacity, price_per_day, latitude, longitude, address, postal_code, user_id, active)
        VALUES
        (:name, :description, :location, :capacity, :price_per_day, :latitude, :longitude, :address, :postal_code, :user_id, :active)
    ");

    $active = isset($input['active']) ? (bool)$input['active'] : true;

    $stmt->execute([
        'name' => $input['name'],
        'description' => $input['description'],
        'location' => $input['location'],
        'capacity' => (int)$input['capacity'],
        'price_per_day' => (float)$input['pricePerDay'],
        'latitude' => $input['latitude'] ?? null,
        'longitude' => $input['longitude'] ?? null,
        'address' => $input['address'] ?? null,
        'postal_code' => $input['postalCode'] ?? null,
        'user_id' => $userId,
        'active' => $active
    ]);

    $placeId = $conn->lastInsertId();

    // Bilder einfügen (falls vorhanden)
    if (isset($input['images']) && is_array($input['images'])) {
        $imgStmt = $conn->prepare("INSERT INTO place_images (place_id, url) VALUES (:place_id, :url)");
        foreach ($input['images'] as $imageUrl) {
            $imgStmt->execute([
                'place_id' => $placeId,
                'url' => $imageUrl
            ]);
        }
    }

    // Features einfügen (falls vorhanden)
    if (isset($input['features']) && is_array($input['features'])) {
        $featStmt = $conn->prepare("
            INSERT INTO place_features (place_id, name, icon, available)
            VALUES (:place_id, :name, :icon, :available)
        ");
        foreach ($input['features'] as $feature) {
            $featStmt->execute([
                'place_id' => $placeId,
                'name' => $feature['name'],
                'icon' => $feature['icon'] ?? null,
                'available' => isset($feature['available']) ? (bool)$feature['available'] : true
            ]);
        }
    }

    // Erstellten Ort zurückgeben
    $placeStmt = $conn->prepare("
        SELECT
            place_id as id,
            name,
            description,
            location,
            capacity,
            price_per_day as pricePerDay,
            latitude,
            longitude,
            address,
            postal_code as postalCode,
            active
        FROM places
        WHERE place_id = :place_id
    ");
    $placeStmt->execute(['place_id' => $placeId]);
    $place = $placeStmt->fetch(PDO::FETCH_ASSOC);

    // Bilder laden
    $imgStmt = $conn->prepare("SELECT url FROM place_images WHERE place_id = :place_id");
    $imgStmt->execute(['place_id' => $placeId]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    $place['images'] = array_column($images, 'url');

    // Features laden
    $featStmt = $conn->prepare("
        SELECT feature_id as id, name, icon, available
        FROM place_features
        WHERE place_id = :place_id
    ");
    $featStmt->execute(['place_id' => $placeId]);
    $place['features'] = $featStmt->fetchAll(PDO::FETCH_ASSOC);

    // Typen konvertieren
    $place['id'] = (int)$place['id'];
    $place['capacity'] = (int)$place['capacity'];
    $place['pricePerDay'] = (float)$place['pricePerDay'];
    $place['active'] = (bool)$place['active'];
    $place['latitude'] = $place['latitude'] ? (float)$place['latitude'] : null;
    $place['longitude'] = $place['longitude'] ? (float)$place['longitude'] : null;

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'data' => $place,
        'message' => 'Ort erfolgreich erstellt'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Serverfehler beim Erstellen des Ortes'
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?>
