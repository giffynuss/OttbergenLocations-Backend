<?php
// GET /api/places - Liste aller Orte mit optionalen Filtern

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/validation.php';

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

    // Query-Parameter
    $search = $_GET['search'] ?? null;
    $checkIn = $_GET['checkIn'] ?? null;
    $checkOut = $_GET['checkOut'] ?? null;
    $minCapacity = isset($_GET['minCapacity']) ? intval($_GET['minCapacity']) : null;
    $maxPrice = isset($_GET['maxPrice']) ? floatval($_GET['maxPrice']) : null;
    $active = isset($_GET['active']) ? filter_var($_GET['active'], FILTER_VALIDATE_BOOLEAN) : true;

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
        WHERE 1=1
    ";

    $params = [];

    // Filter: Active
    if ($active) {
        $sql .= " AND p.active = 1";
    }

    // Filter: Suche
    if ($search) {
        $sql .= " AND (p.name LIKE :search OR p.description LIKE :search OR p.location LIKE :search)";
        $params['search'] = "%{$search}%";
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

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
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

        // Provider-Objekt formatieren
        $place['provider'] = [
            'id' => $place['providerId'],
            'name' => $place['providerName']
        ];

        // Unbenötigte Felder entfernen
        unset($place['providerId'], $place['providerName']);

        // Boolean-Werte konvertieren
        $place['active'] = (bool)$place['active'];

        // Numerische Werte konvertieren
        $place['id'] = (int)$place['id'];
        $place['capacity'] = (int)$place['capacity'];
        $place['pricePerDay'] = (float)$place['pricePerDay'];
        $place['latitude'] = $place['latitude'] ? (float)$place['latitude'] : null;
        $place['longitude'] = $place['longitude'] ? (float)$place['longitude'] : null;
    }

    // Filter: Verfügbarkeit (wenn checkIn und checkOut angegeben)
    if ($checkIn && $checkOut) {
        $validation = validateDateRange($checkIn, $checkOut);
        if (!$validation['valid']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $validation['error']
            ]);
            exit;
        }

        // Nur verfügbare Orte zurückgeben
        $availablePlaces = [];
        foreach ($places as $place) {
            $availability = checkAvailability($conn, $place['id'], $checkIn, $checkOut);
            if ($availability['available']) {
                $availablePlaces[] = $place;
            }
        }
        $places = $availablePlaces;
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
