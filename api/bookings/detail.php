<?php
// GET /api/bookings/:id - Detailansicht einer Buchung

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

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
    // Authentifizierung erforderlich
    $userId = requireAuth();

    $db = new Database();
    $conn = $db->getConnection();

    $bookingId = isset($_GET['id']) ? intval($_GET['id']) : null;

    if (!$bookingId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INVALID_ID',
                'message' => 'Ungültige Buchungs-ID.'
            ]
        ]);
        exit;
    }

    // Buchung laden
    $stmt = $conn->prepare("
        SELECT
            b.booking_id as id,
            b.place_id as placeId,
            b.check_in as checkIn,
            b.check_out as checkOut,
            b.guests,
            b.total_price as totalPrice,
            b.status,
            b.cancelled_at as cancelledAt,
            b.cancellation_reason as cancellationReason,
            p.name as placeName,
            p.location as placeLocation,
            p.address as placeAddress,
            p.postal_code as placePostalCode,
            u.user_id as providerId,
            CONCAT(u.first_name, ' ', u.last_name) as providerName,
            u.email as providerEmail,
            u.phone as providerPhone
        FROM bookings b
        JOIN places p ON b.place_id = p.place_id
        JOIN users u ON p.user_id = u.user_id
        WHERE b.booking_id = :booking_id
    ");
    $stmt->execute(['booking_id' => $bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'BOOKING_NOT_FOUND',
                'message' => 'Buchung nicht gefunden.'
            ]
        ]);
        exit;
    }

    // Autorisierung: Nur eigene Buchungen oder Anbieter
    if (!canAccessBooking($conn, $bookingId, $userId)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'FORBIDDEN',
                'message' => 'Keine Berechtigung für diese Buchung.'
            ]
        ]);
        exit;
    }

    // Bilder des Places laden
    $imgStmt = $conn->prepare("
        SELECT url
        FROM place_images
        WHERE place_id = :place_id
        ORDER BY sort_order ASC
        LIMIT 1
    ");
    $imgStmt->execute(['place_id' => $booking['placeId']]);
    $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

    // Response formatieren
    $booking['place'] = [
        'name' => $booking['placeName'],
        'location' => $booking['placeLocation'],
        'address' => $booking['placeAddress'],
        'postalCode' => $booking['placePostalCode'],
        'images' => $images
    ];

    $booking['provider'] = [
        'id' => (int)$booking['providerId'],
        'name' => $booking['providerName'],
        'email' => $booking['providerEmail'],
        'phone' => $booking['providerPhone']
    ];

    // Unbenötigte Felder entfernen
    unset(
        $booking['placeName'],
        $booking['placeLocation'],
        $booking['placeAddress'],
        $booking['placePostalCode'],
        $booking['providerId'],
        $booking['providerName'],
        $booking['providerEmail'],
        $booking['providerPhone']
    );

    // Datentypen konvertieren
    $booking['id'] = (int)$booking['id'];
    $booking['placeId'] = (int)$booking['placeId'];
    $booking['guests'] = (int)$booking['guests'];
    $booking['totalPrice'] = (float)$booking['totalPrice'];

    echo json_encode([
        'success' => true,
        'data' => $booking
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
