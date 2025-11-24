<?php
// GET /api/places/get.php?id={id} - Einzelner Ort (Frontend-Format)

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require_once __DIR__ . '/../../config/database.php';

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

    // ID aus Query-Parameter
    $placeId = isset($_GET['id']) ? intval($_GET['id']) : null;

    if (!$placeId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
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
            u.user_id as providerId,
            u.email as providerEmail,
            CONCAT(u.first_name, ' ', u.last_name) as providerName,
            u.phone as providerPhone
        FROM places p
        LEFT JOIN users u ON p.user_id = u.user_id
        WHERE p.place_id = :place_id
    ");
    $stmt->execute(['place_id' => $placeId]);
    $place = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$place) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => [
                'message' => 'Ort nicht gefunden.'
            ]
        ]);
        exit;
    }

    // Bilder laden
    $imgStmt = $conn->prepare("
        SELECT url
        FROM place_images
        WHERE place_id = :place_id
        ORDER BY image_id ASC
    ");
    $imgStmt->execute(['place_id' => $placeId]);
    $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
    $place['images'] = array_column($images, 'url');

    // Features laden
    $featStmt = $conn->prepare("
        SELECT
            feature_id as id,
            name,
            icon as category
        FROM place_features
        WHERE place_id = :place_id AND available = 1
        ORDER BY feature_id ASC
    ");
    $featStmt->execute(['place_id' => $placeId]);
    $features = $featStmt->fetchAll(PDO::FETCH_ASSOC);

    // Features formatieren (Frontend erwartet id, name, category)
    foreach ($features as &$feature) {
        $feature['id'] = (int)$feature['id'];
        // category ist bereits gesetzt (icon wird als category verwendet)
    }
    $place['features'] = $features;

    // Provider-Objekt formatieren
    $place['provider'] = [
        'id' => (int)$place['providerId'],
        'name' => $place['providerName'],
        'email' => $place['providerEmail'],
        'phone' => $place['providerPhone']
    ];

    // Verfügbare Zeiträume berechnen
    // Wir geben die nächsten 12 Monate als verfügbar zurück,
    // minus die gebuchten Zeiträume
    $availableDates = calculateAvailableDates($conn, $placeId);
    $place['availableDates'] = $availableDates;

    // Unbenötigte Felder entfernen
    unset(
        $place['providerId'],
        $place['providerName'],
        $place['providerEmail'],
        $place['providerPhone']
    );

    // Datentypen konvertieren
    $place['id'] = (int)$place['id'];
    $place['capacity'] = (int)$place['capacity'];
    $place['pricePerDay'] = (float)$place['pricePerDay'];
    $place['latitude'] = $place['latitude'] ? (float)$place['latitude'] : null;
    $place['longitude'] = $place['longitude'] ? (float)$place['longitude'] : null;
    $place['active'] = (bool)$place['active'];

    // Frontend-Format: "place" statt "data"
    echo json_encode([
        'success' => true,
        'place' => $place
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

/**
 * Berechnet verfügbare Zeiträume für einen Ort
 * Gibt die nächsten 12 Monate zurück, aufgeteilt in verfügbare Zeiträume
 */
function calculateAvailableDates($conn, $placeId) {
    // Gebuchte Zeiträume laden
    $stmt = $conn->prepare("
        SELECT check_in, check_out
        FROM bookings
        WHERE place_id = :place_id
        AND status IN ('confirmed', 'upcoming', 'pending')
        AND check_out >= CURDATE()
        ORDER BY check_in ASC
    ");
    $stmt->execute(['place_id' => $placeId]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Startdatum: heute
    $today = new DateTime();
    $today->setTime(0, 0, 0);

    // Enddatum: 12 Monate in der Zukunft
    $maxDate = clone $today;
    $maxDate->modify('+12 months');

    $availableDates = [];
    $currentStart = clone $today;

    foreach ($bookings as $booking) {
        $bookingStart = new DateTime($booking['check_in']);
        $bookingEnd = new DateTime($booking['check_out']);

        // Wenn es eine Lücke vor dieser Buchung gibt
        if ($currentStart < $bookingStart) {
            $availableDates[] = [
                'start' => $currentStart->format('Y-m-d'),
                'end' => $bookingStart->format('Y-m-d')
            ];
        }

        // Nächster verfügbarer Start ist nach dieser Buchung
        $currentStart = $bookingEnd;
    }

    // Wenn noch Zeit bis zum Enddatum übrig ist
    if ($currentStart < $maxDate) {
        $availableDates[] = [
            'start' => $currentStart->format('Y-m-d'),
            'end' => $maxDate->format('Y-m-d')
        ];
    }

    return $availableDates;
}
