<?php
// GET /api/places/list.php - Liste aller Orte (Frontend-Format)

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/validation.php';

// Nur GET-Requests erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => 'Nur GET-Requests erlaubt.'
        ]
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Query-Parameter
    $search = $_GET['search'] ?? null;
    $location = $_GET['location'] ?? null;
    $checkIn = $_GET['checkIn'] ?? null;
    $checkOut = $_GET['checkOut'] ?? null;
    $minCapacity = isset($_GET['minCapacity']) ? intval($_GET['minCapacity']) : null;
    $maxPrice = isset($_GET['maxPrice']) ? floatval($_GET['maxPrice']) : null;

    // Validierung der Datumsparameter (wenn beide angegeben)
    if ($checkIn && $checkOut) {
        $validation = validateDateRange($checkIn, $checkOut);
        if (!$validation['valid']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => [
                    'message' => $validation['error']['message']
                ]
            ]);
            exit;
        }
    }

    // Base Query
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
            p.active,
            u.user_id as providerId,
            CONCAT(u.first_name, ' ', u.last_name) as providerName
        FROM places p
        LEFT JOIN users u ON p.user_id = u.user_id
        WHERE p.active = 1
    ";

    $params = [];

    // Filter: Verfügbarkeit (wenn checkIn und checkOut angegeben)
    if ($checkIn && $checkOut) {
        $sql .= " AND p.place_id NOT IN (
            SELECT b.place_id
            FROM bookings b
            WHERE b.status IN ('pending', 'confirmed', 'upcoming')
            AND b.check_in < :availability_check_out
            AND b.check_out > :availability_check_in
        )";
        $params['availability_check_in'] = $checkIn;
        $params['availability_check_out'] = $checkOut;
    }

    // Filter: Suche (Name, Beschreibung, Location)
    if ($search) {
        $sql .= " AND (p.name LIKE :search OR p.description LIKE :search OR p.location LIKE :search)";
        $params['search'] = "%{$search}%";
    }

    // Filter: Location
    if ($location) {
        $sql .= " AND p.location LIKE :location";
        $params['location'] = "%{$location}%";
    }

    // Filter: Kapazität
    if ($minCapacity !== null) {
        $sql .= " AND p.capacity >= :min_capacity";
        $params['min_capacity'] = $minCapacity;
    }

    // Filter: Max Preis
    if ($maxPrice !== null) {
        $sql .= " AND p.price_per_day <= :max_price";
        $params['max_price'] = $maxPrice;
    }

    $sql .= " ORDER BY p.place_id DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Für jeden Ort: Bilder, Features laden und Daten formatieren
    foreach ($places as &$place) {
        // Bilder laden
        $imgStmt = $conn->prepare("
            SELECT url
            FROM place_images
            WHERE place_id = :place_id
            ORDER BY image_id ASC
        ");
        $imgStmt->execute(['place_id' => $place['id']]);
        $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
        $place['images'] = array_column($images, 'url');

        // Features laden
        $featuresStmt = $conn->prepare("
            SELECT name, icon, available
            FROM place_features
            WHERE place_id = :place_id
            ORDER BY feature_id ASC
        ");
        $featuresStmt->execute(['place_id' => $place['id']]);
        $features = $featuresStmt->fetchAll(PDO::FETCH_ASSOC);

        // Features formatieren (available als boolean)
        foreach ($features as &$feature) {
            $feature['available'] = (bool)$feature['available'];
        }
        $place['features'] = $features;

        // Provider-Objekt formatieren
        $place['provider'] = [
            'id' => (int)$place['providerId'],
            'name' => $place['providerName']
        ];

        // Unbenötigte Felder entfernen
        unset($place['providerId'], $place['providerName']);

        // Datentypen konvertieren
        $place['id'] = (int)$place['id'];
        $place['capacity'] = (int)$place['capacity'];
        $place['pricePerDay'] = (float)$place['pricePerDay'];
        $place['latitude'] = $place['latitude'] ? (float)$place['latitude'] : null;
        $place['longitude'] = $place['longitude'] ? (float)$place['longitude'] : null;
        $place['active'] = (bool)$place['active'];
    }

    // Frontend-Format: "places" statt "data"
    echo json_encode([
        'success' => true,
        'places' => $places
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => 'Serverfehler: ' . $e->getMessage()
        ]
    ], JSON_UNESCAPED_UNICODE);
}
