<?php
// POST /api/bookings - Neue Buchung erstellen

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Preflight-Request behandeln
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/validation.php';
require_once __DIR__ . '/../../helpers/pricing.php';

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

try {
    // Authentifizierung erforderlich
    $userId = requireAuth();

    $db = new Database();
    $conn = $db->getConnection();

    // Request Body parsen
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INVALID_JSON',
                'message' => 'Ungültige JSON-Daten.'
            ]
        ]);
        exit;
    }

    $placeId = $input['placeId'] ?? null;
    $checkIn = $input['checkIn'] ?? null;
    $checkOut = $input['checkOut'] ?? null;
    $guests = $input['guests'] ?? null;

    // Validierung: Pflichtfelder
    if (!$placeId || !$checkIn || !$checkOut || !$guests) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'MISSING_FIELDS',
                'message' => 'Alle Felder (placeId, checkIn, checkOut, guests) sind erforderlich.'
            ]
        ]);
        exit;
    }

    // Validierung: Place existiert und ist aktiv
    $placeValidation = validatePlace($conn, $placeId);
    if (!$placeValidation['valid']) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => $placeValidation['error']
        ]);
        exit;
    }
    $place = $placeValidation['place'];

    // Validierung: Datumsbereich
    $dateValidation = validateDateRange($checkIn, $checkOut);
    if (!$dateValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $dateValidation['error']
        ]);
        exit;
    }

    // Validierung: Gästeanzahl
    $guestValidation = validateGuestCount($conn, $placeId, $guests);
    if (!$guestValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $guestValidation['error']
        ]);
        exit;
    }

    // Validierung: Verfügbarkeit
    $availability = checkAvailability($conn, $placeId, $checkIn, $checkOut);
    if (!$availability['available']) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'PLACE_NOT_AVAILABLE',
                'message' => 'Der Ort ist im gewählten Zeitraum nicht verfügbar.',
                'details' => [
                    'blockedDates' => $availability['blockedDates']
                ]
            ]
        ]);
        exit;
    }

    // Preisberechnung
    $pricing = calculateBookingPrice($conn, $place['price_per_day'], $checkIn, $checkOut);

    // Buchung erstellen
    $stmt = $conn->prepare("
        INSERT INTO bookings
        (place_id, user_id, check_in, check_out, guests, subtotal, service_fee, tax, total_price, status)
        VALUES
        (:place_id, :user_id, :check_in, :check_out, :guests, :subtotal, :service_fee, :tax, :total_price, 'pending')
    ");

    $stmt->execute([
        'place_id' => $placeId,
        'user_id' => $userId,
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'guests' => $guests,
        'subtotal' => $pricing['subtotal'],
        'service_fee' => $pricing['serviceFee'],
        'tax' => $pricing['tax'],
        'total_price' => $pricing['totalPrice']
    ]);

    $bookingId = $conn->lastInsertId();

    // Buchung mit Details laden
    $bookingStmt = $conn->prepare("
        SELECT
            b.booking_id as id,
            b.place_id as placeId,
            p.name as placeName,
            p.location as placeLocation,
            b.user_id as userId,
            b.check_in as checkIn,
            b.check_out as checkOut,
            b.guests,
            b.subtotal,
            b.service_fee as serviceFee,
            b.tax,
            b.total_price as totalPrice,
            b.status,
            b.created_at as createdAt
        FROM bookings b
        JOIN places p ON b.place_id = p.place_id
        WHERE b.booking_id = :booking_id
    ");
    $bookingStmt->execute(['booking_id' => $bookingId]);
    $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);

    // Datentypen konvertieren
    $booking['id'] = (int)$booking['id'];
    $booking['placeId'] = (int)$booking['placeId'];
    $booking['userId'] = (int)$booking['userId'];
    $booking['guests'] = (int)$booking['guests'];
    $booking['subtotal'] = (float)$booking['subtotal'];
    $booking['serviceFee'] = (float)$booking['serviceFee'];
    $booking['tax'] = (float)$booking['tax'];
    $booking['totalPrice'] = (float)$booking['totalPrice'];

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'data' => $booking,
        'message' => 'Buchung erfolgreich erstellt. Bitte warten Sie auf die Bestätigung des Anbieters.'
    ], JSON_UNESCAPED_UNICODE);

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
