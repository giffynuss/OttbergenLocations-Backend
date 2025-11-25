<?php
// POST /api/bookings/create.php - Neue Buchung erstellen (inkl. Gast-Buchungen)

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

// Preflight-Request behandeln
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/validation.php';
require_once __DIR__ . '/../../helpers/pricing.php';
require_once __DIR__ . '/../../helpers/booking.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Nur POST-Requests erlaubt.'
    ]);
    exit;
}

try {
    // OPTIONAL: User-ID prüfen (falls eingeloggt)
    session_start();
    $userId = $_SESSION['user_id'] ?? null;

    $db = new Database();
    $conn = $db->getConnection();

    // Request Body parsen
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ungültige JSON-Daten.'
        ]);
        exit;
    }

    // Pflichtfelder aus Request
    $placeId = $input['placeId'] ?? null;
    $checkIn = $input['checkIn'] ?? null;
    $checkOut = $input['checkOut'] ?? null;
    $guests = $input['guests'] ?? 1;
    $paymentMethod = $input['paymentMethod'] ?? 'cash';
    $userInfo = $input['userInfo'] ?? null;

    // Validierung: Basis-Pflichtfelder
    if (!$placeId || !$checkIn || !$checkOut) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Fehlende Pflichtfelder: placeId, checkIn, checkOut sind erforderlich.'
        ]);
        exit;
    }

    // Validierung: userInfo erforderlich
    if (!$userInfo || !is_array($userInfo)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'userInfo mit Kontaktdaten ist erforderlich.'
        ]);
        exit;
    }

    // Validierung: Zahlungsmethode
    $validPaymentMethods = ['cash', 'paypal', 'transfer', 'wero'];
    if (!in_array($paymentMethod, $validPaymentMethods)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Ungültige Zahlungsmethode. Erlaubt: cash, paypal, transfer, wero'
        ]);
        exit;
    }

    // Validierung: UserInfo
    $userInfoValidation = validateUserInfo($userInfo, $paymentMethod);
    if (!$userInfoValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $userInfoValidation['error']['message'],
            'errors' => $userInfoValidation['error']
        ]);
        exit;
    }

    // Validierung: Place existiert und ist aktiv
    $placeValidation = validatePlace($conn, $placeId);
    if (!$placeValidation['valid']) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => $placeValidation['error']['message']
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
            'message' => $dateValidation['error']['message'],
            'errors' => [
                'checkIn' => 'Ungültiger Datumsbereich'
            ]
        ]);
        exit;
    }

    // Validierung: Gästeanzahl
    $guestValidation = validateGuestCount($conn, $placeId, $guests);
    if (!$guestValidation['valid']) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $guestValidation['error']['message']
        ]);
        exit;
    }

    // Validierung: Verfügbarkeit
    $availability = checkAvailability($conn, $placeId, $checkIn, $checkOut);
    if (!$availability['available']) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Der Ort ist für diesen Zeitraum nicht verfügbar',
            'errors' => [
                'availability' => 'Bereits gebucht in diesem Zeitraum',
                'blockedDates' => $availability['blockedDates']
            ]
        ]);
        exit;
    }

    // Preisberechnung (serverseitig!)
    $pricing = calculateBookingPrice($place['price_per_day'], $checkIn, $checkOut);

    // Buchungsreferenz generieren
    $bookingReference = generateBookingReference($conn);

    // Buchung erstellen
    $stmt = $conn->prepare("
        INSERT INTO bookings
        (place_id, user_id, check_in, check_out, guests, total_price, payment_method, booking_reference, status)
        VALUES
        (:place_id, :user_id, :check_in, :check_out, :guests, :total_price, :payment_method, :booking_reference, 'pending')
    ");

    $stmt->execute([
        'place_id' => $placeId,
        'user_id' => $userId, // kann NULL sein bei Gästen
        'check_in' => $checkIn,
        'check_out' => $checkOut,
        'guests' => $guests,
        'total_price' => $pricing['totalPrice'],
        'payment_method' => $paymentMethod,
        'booking_reference' => $bookingReference
    ]);

    $bookingId = $conn->lastInsertId();

    // Token generieren für Bestätigung/Ablehnung per E-Mail
    $token = bin2hex(random_bytes(32));

    // Token in Datenbank speichern
    $tokenStmt = $conn->prepare("UPDATE bookings SET confirmation_token = :token WHERE booking_id = :booking_id");
    $tokenStmt->execute(['token' => $token, 'booking_id' => $bookingId]);

    // Gast-Informationen speichern
    saveGuestInfo($conn, $bookingId, $userInfo);

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
            b.total_price as totalPrice,
            b.payment_method as paymentMethod,
            b.booking_reference as bookingReference,
            b.status
        FROM bookings b
        JOIN places p ON b.place_id = p.place_id
        WHERE b.booking_id = :booking_id
    ");
    $bookingStmt->execute(['booking_id' => $bookingId]);
    $booking = $bookingStmt->fetch(PDO::FETCH_ASSOC);

    // Gast-Info laden
    $guestInfo = getGuestInfo($conn, $bookingId);

    // Datentypen konvertieren
    $booking['id'] = (int)$booking['id'];
    $booking['placeId'] = (int)$booking['placeId'];
    $booking['userId'] = $booking['userId'] ? (int)$booking['userId'] : null;
    $booking['guests'] = (int)$booking['guests'];
    $booking['totalPrice'] = (float)$booking['totalPrice'];
    $booking['guestInfo'] = $guestInfo;

    // Response vorbereiten
    $response = [
        'success' => true,
        'booking' => $booking,
        'message' => 'Buchung erfolgreich erstellt'
    ];

    // Bei Überweisung: Mock-Bankdaten hinzufügen
    if ($paymentMethod === 'transfer') {
        $response['paymentDetails'] = getMockBankDetails();
    }

    // ========== E-MAIL-BENACHRICHTIGUNG AN ANBIETER ==========
    // E-Mail-Versand ist optional - wenn PHPMailer nicht installiert ist, wird die Buchung trotzdem erstellt
    error_log("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    error_log("📧 E-MAIL BENACHRICHTIGUNG - Booking #{$bookingId}");
    error_log("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

    if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
        error_log("✓ PHPMailer gefunden");
        try {
            require_once __DIR__ . '/../../services/EmailService.php';
            error_log("✓ EmailService geladen");

            // Provider-Daten laden
            $providerStmt = $conn->prepare("
                SELECT user_id, first_name, last_name, email, phone
                FROM users
                WHERE user_id = :user_id
            ");
            $providerStmt->execute(['user_id' => $place['user_id']]);
            $provider = $providerStmt->fetch(PDO::FETCH_ASSOC);

            if ($provider) {
                error_log("✓ Provider gefunden: " . $provider['email']);

                $emailService = new EmailService();
                error_log("✓ EmailService initialisiert");

                // Buchungsdaten für E-Mail vorbereiten
                $bookingForEmail = [
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'guests' => $guests,
                    'total_price' => $pricing['totalPrice'],
                    'payment_method' => $paymentMethod,
                    'booking_reference' => $bookingReference
                ];

                // E-Mail an Anbieter senden
                error_log("→ Sende E-Mail an Provider...");
                $emailResult = $emailService->sendBookingRequestToProvider(
                    $bookingForEmail,
                    $place,
                    $provider,
                    $guestInfo,
                    $token
                );

                // Log-Eintrag bei Fehler (blockiert aber nicht die Response)
                if ($emailResult['success']) {
                    error_log("✓✓✓ E-MAIL ERFOLGREICH: " . $emailResult['message']);
                } else {
                    error_log("✗✗✗ E-MAIL FEHLGESCHLAGEN: " . $emailResult['message']);
                }
            } else {
                error_log("✗ Provider nicht gefunden (user_id: " . $place['user_id'] . ")");
            }
        } catch (Exception $emailException) {
            // E-Mail-Fehler loggen, aber Buchung war erfolgreich
            error_log("✗✗✗ E-MAIL EXCEPTION: " . $emailException->getMessage());
            error_log("Stack Trace: " . $emailException->getTraceAsString());
        }
    } else {
        // PHPMailer nicht installiert - E-Mail wird übersprungen
        error_log("✗ PHPMailer (vendor/autoload.php) nicht gefunden");
    }
    error_log("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

    http_response_code(201);
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Serverfehler: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
