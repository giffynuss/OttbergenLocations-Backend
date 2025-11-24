<?php
// PATCH /api/places/update.php?id={id} - Ort aktualisieren (nur eigener Ort)

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
                'message' => 'Keine Berechtigung zum Bearbeiten dieses Ortes'
            ]
        ]);
        exit;
    }

    // Update-Query dynamisch aufbauen
    $updateFields = [];
    $params = ['place_id' => $placeId];

    if (isset($input['name'])) {
        $updateFields[] = "name = :name";
        $params['name'] = $input['name'];
    }

    if (isset($input['description'])) {
        $updateFields[] = "description = :description";
        $params['description'] = $input['description'];
    }

    if (isset($input['location'])) {
        $updateFields[] = "location = :location";
        $params['location'] = $input['location'];
    }

    if (isset($input['capacity'])) {
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
        $updateFields[] = "capacity = :capacity";
        $params['capacity'] = (int)$input['capacity'];
    }

    if (isset($input['pricePerDay'])) {
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
        $updateFields[] = "price_per_day = :price_per_day";
        $params['price_per_day'] = (float)$input['pricePerDay'];
    }

    if (isset($input['latitude'])) {
        $updateFields[] = "latitude = :latitude";
        $params['latitude'] = $input['latitude'];
    }

    if (isset($input['longitude'])) {
        $updateFields[] = "longitude = :longitude";
        $params['longitude'] = $input['longitude'];
    }

    if (isset($input['address'])) {
        $updateFields[] = "address = :address";
        $params['address'] = $input['address'];
    }

    if (isset($input['postalCode'])) {
        $updateFields[] = "postal_code = :postal_code";
        $params['postal_code'] = $input['postalCode'];
    }

    if (isset($input['active'])) {
        $updateFields[] = "active = :active";
        $params['active'] = (bool)$input['active'];
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'NO_UPDATE_DATA',
                'message' => 'Keine Daten zum Aktualisieren'
            ]
        ]);
        exit;
    }

    // Update durchführen
    $sql = "UPDATE places SET " . implode(', ', $updateFields) . " WHERE place_id = :place_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // Bilder aktualisieren (falls vorhanden)
    if (isset($input['images']) && is_array($input['images'])) {
        // Alte Bilder löschen
        $conn->prepare("DELETE FROM place_images WHERE place_id = :place_id")->execute(['place_id' => $placeId]);

        // Neue Bilder einfügen
        $imgStmt = $conn->prepare("INSERT INTO place_images (place_id, url) VALUES (:place_id, :url)");
        foreach ($input['images'] as $imageUrl) {
            $imgStmt->execute([
                'place_id' => $placeId,
                'url' => $imageUrl
            ]);
        }
    }

    // Features aktualisieren (falls vorhanden)
    if (isset($input['features']) && is_array($input['features'])) {
        // Alte Features löschen
        $conn->prepare("DELETE FROM place_features WHERE place_id = :place_id")->execute(['place_id' => $placeId]);

        // Neue Features einfügen
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

    // Aktualisierten Ort zurückgeben
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
    $updatedPlace = $placeStmt->fetch(PDO::FETCH_ASSOC);

    // Bilder laden
    $imgStmt = $conn->prepare("SELECT url FROM place_images WHERE place_id = :place_id");
    $imgStmt->execute(['place_id' => $placeId]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    $updatedPlace['images'] = array_column($images, 'url');

    // Features laden
    $featStmt = $conn->prepare("
        SELECT feature_id as id, name, icon, available
        FROM place_features
        WHERE place_id = :place_id
    ");
    $featStmt->execute(['place_id' => $placeId]);
    $updatedPlace['features'] = $featStmt->fetchAll(PDO::FETCH_ASSOC);

    // Typen konvertieren
    $updatedPlace['id'] = (int)$updatedPlace['id'];
    $updatedPlace['capacity'] = (int)$updatedPlace['capacity'];
    $updatedPlace['pricePerDay'] = (float)$updatedPlace['pricePerDay'];
    $updatedPlace['active'] = (bool)$updatedPlace['active'];
    $updatedPlace['latitude'] = $updatedPlace['latitude'] ? (float)$updatedPlace['latitude'] : null;
    $updatedPlace['longitude'] = $updatedPlace['longitude'] ? (float)$updatedPlace['longitude'] : null;

    echo json_encode([
        'success' => true,
        'data' => $updatedPlace,
        'message' => 'Ort erfolgreich aktualisiert'
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Serverfehler beim Aktualisieren des Ortes'
        ]
    ], JSON_UNESCAPED_UNICODE);
}
?>
