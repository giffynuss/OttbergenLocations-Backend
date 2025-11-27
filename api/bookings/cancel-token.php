<?php
// GET /api/bookings/cancel-token.php?token={token} - Buchung per Token stornieren (für Gäste)

// Prüfen ob JSON-Response gewünscht ist (für API-Calls)
$acceptJson = isset($_GET['format']) && $_GET['format'] === 'json';

if ($acceptJson) {
    header("Content-Type: application/json; charset=utf-8");
}

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Preflight-Request behandeln
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/EmailService.php';

// Frontend-URL aus Umgebungsvariablen oder Standard
$frontend_url = getenv('FRONTEND_URL') ?: 'http://localhost:5173';

if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Nur GET/POST-Requests erlaubt.'
        ]
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Token aus Query-Parameter
    $token = $_GET['token'] ?? null;

    if (!$token) {
        http_response_code(400);
        if ($acceptJson) {
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'MISSING_TOKEN',
                    'message' => 'Token fehlt.'
                ]
            ]);
        } else {
            $error_title = 'Token fehlt';
            $error_message = 'Der Stornierungslink ist ungültig oder unvollständig.';
            $error_details = '';
            $retry_url = '';
            require __DIR__ . '/../../templates/error_page.php';
        }
        exit;
    }

    // Buchung anhand des Tokens laden
    $stmt = $conn->prepare("
        SELECT
            b.*,
            p.place_id,
            p.name as place_name,
            p.location as place_location,
            p.user_id as provider_id
        FROM bookings b
        JOIN places p ON b.place_id = p.place_id
        WHERE b.cancellation_token = :token
    ");
    $stmt->execute(['token' => $token]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        http_response_code(404);
        if ($acceptJson) {
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_TOKEN',
                    'message' => 'Ungültiger oder abgelaufener Token.'
                ]
            ]);
        } else {
            $error_title = 'Ungültiger Link';
            $error_message = 'Der Stornierungslink ist ungültig oder bereits verwendet worden.';
            $error_details = 'Bitte überprüfen Sie, ob die Buchung bereits storniert wurde.';
            $retry_url = '';
            require __DIR__ . '/../../templates/error_page.php';
        }
        exit;
    }

    // Status-Prüfung: Nur pending, confirmed oder upcoming können storniert werden
    $allowedStatuses = ['pending', 'confirmed', 'upcoming'];
    if (!in_array($booking['status'], $allowedStatuses)) {
        http_response_code(400);
        if ($acceptJson) {
            echo json_encode([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_STATUS',
                    'message' => "Diese Buchung kann nicht mehr storniert werden (Status: {$booking['status']})."
                ]
            ]);
        } else {
            $error_title = 'Stornierung nicht möglich';
            $error_message = 'Diese Buchung kann nicht mehr storniert werden.';
            $error_details = "Status der Buchung: " . htmlspecialchars($booking['status']);
            $retry_url = '';
            require __DIR__ . '/../../templates/error_page.php';
        }
        exit;
    }

    // Optionale Stornierungsfrist prüfen (z.B. mindestens 7 Tage vor Check-in)
    // Aktivieren Sie diese Prüfung bei Bedarf:
    /*
    $checkInDate = new DateTime($booking['check_in']);
    $now = new DateTime();
    $daysDifference = $now->diff($checkInDate)->days;

    if ($daysDifference < 7) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'CANCELLATION_DEADLINE_PASSED',
                'message' => 'Stornierungen sind nur bis 7 Tage vor Check-in möglich.'
            ]
        ]);
        exit;
    }
    */

    // Optional: Stornierungsgrund aus Request-Body (bei POST)
    $cancellationReason = 'Stornierung durch Gast via E-Mail-Link';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!empty($input['reason'])) {
            $cancellationReason = $input['reason'];
        }
    }

    // Buchung stornieren
    $updateStmt = $conn->prepare("
        UPDATE bookings
        SET status = 'cancelled',
            cancelled_at = NOW(),
            cancellation_reason = :reason
        WHERE booking_id = :booking_id
    ");
    $updateStmt->execute([
        'booking_id' => $booking['booking_id'],
        'reason' => $cancellationReason
    ]);

    // Gast-Informationen laden
    $guestStmt = $conn->prepare("
        SELECT * FROM booking_guest_info WHERE booking_id = :booking_id
    ");
    $guestStmt->execute(['booking_id' => $booking['booking_id']]);
    $guestInfo = $guestStmt->fetch(PDO::FETCH_ASSOC);

    // Provider-Informationen laden
    $providerStmt = $conn->prepare("
        SELECT user_id, first_name, last_name, email, phone, gender
        FROM users WHERE user_id = :user_id
    ");
    $providerStmt->execute(['user_id' => $booking['provider_id']]);
    $provider = $providerStmt->fetch(PDO::FETCH_ASSOC);

    // E-Mails versenden
    $emailService = new EmailService();

    // 1. Stornierungsbestätigung an Gast
    if ($guestInfo) {
        $placeData = [
            'name' => $booking['place_name'],
            'location' => $booking['place_location']
        ];

        // Gast-Daten für EmailService formatieren (camelCase)
        $guestDataForEmail = [
            'email' => $guestInfo['email'],
            'firstName' => $guestInfo['first_name'],
            'lastName' => $guestInfo['last_name'],
            'gender' => $guestInfo['gender']
        ];

        $emailService->sendBookingCancellationToUser(
            $booking,
            $placeData,
            $guestDataForEmail,
            $cancellationReason,
            '' // Rückerstattungsinfo kann hier hinzugefügt werden
        );
    }

    // 2. Benachrichtigung an Provider (optional)
    if ($provider) {
        // TODO: Erstellen Sie ein Template für Provider-Stornierungsbenachrichtigung
        // $emailService->sendCancellationNotificationToProvider($booking, $placeData, $provider, $guestInfo);
    }

    // Wenn JSON gewünscht ist, JSON-Response zurückgeben
    if ($acceptJson) {
        $resultStmt = $conn->prepare("
            SELECT
                booking_id as id,
                status,
                cancelled_at as cancelledAt,
                cancellation_reason as cancellationReason
            FROM bookings
            WHERE booking_id = :booking_id
        ");
        $resultStmt->execute(['booking_id' => $booking['booking_id']]);
        $result = $resultStmt->fetch(PDO::FETCH_ASSOC);

        $result['id'] = (int)$result['id'];

        echo json_encode([
            'success' => true,
            'data' => $result,
            'message' => 'Buchung erfolgreich storniert. Sie erhalten eine Bestätigungs-E-Mail.'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // HTML-Bestätigungsseite anzeigen
        $resultStmt = $conn->prepare("
            SELECT
                booking_id,
                booking_reference,
                status,
                cancelled_at,
                cancellation_reason,
                check_in,
                check_out,
                guests,
                total_price
            FROM bookings
            WHERE booking_id = :booking_id
        ");
        $resultStmt->execute(['booking_id' => $booking['booking_id']]);
        $result = $resultStmt->fetch(PDO::FETCH_ASSOC);

        // Variablen für das Template vorbereiten
        $booking_reference = $result['booking_reference'];
        $place_name = $booking['place_name'];
        $place_location = $booking['place_location'];
        $check_in = date('d.m.Y', strtotime($result['check_in']));
        $check_out = date('d.m.Y', strtotime($result['check_out']));
        $guests = $result['guests'];
        $total_price = number_format($result['total_price'], 2, ',', '.');
        $cancelled_at = date('d.m.Y H:i', strtotime($result['cancelled_at']));
        $guest_email = $guestInfo['email'] ?? '';
        $refund_info = ''; // Kann später hinzugefügt werden

        // Template laden und anzeigen
        require __DIR__ . '/../../templates/cancellation_success.php';
    }

} catch (Exception $e) {
    error_log("Stornierungsfehler: " . $e->getMessage());
    http_response_code(500);
    if ($acceptJson) {
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'SERVER_ERROR',
                'message' => 'Serverfehler bei der Stornierung: ' . $e->getMessage()
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $error_title = 'Serverfehler';
        $error_message = 'Es ist ein unerwarteter Fehler aufgetreten.';
        $error_details = 'Bitte versuchen Sie es später erneut oder kontaktieren Sie uns.';
        $retry_url = $_SERVER['REQUEST_URI'] ?? '';
        require __DIR__ . '/../../templates/error_page.php';
    }
}
