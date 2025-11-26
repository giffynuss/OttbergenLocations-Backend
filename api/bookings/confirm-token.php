<?php
// GET /api/bookings/confirm-token.php - Buchung per Token bestätigen

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/EmailService.php';
require_once __DIR__ . '/../../helpers/booking.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    showErrorPage('Ungültiger Link', 'Es wurde kein Token übergeben.');
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Buchung mit Token finden
    $stmt = $conn->prepare("
        SELECT
            b.booking_id,
            b.place_id,
            b.user_id,
            b.check_in,
            b.check_out,
            b.guests,
            b.total_price,
            b.payment_method,
            b.booking_reference,
            b.status,
            b.cancellation_token,
            p.name as place_name,
            p.location as place_location,
            p.user_id as provider_id
        FROM bookings b
        JOIN places p ON b.place_id = p.place_id
        WHERE b.confirmation_token = :token
        AND b.status = 'pending'
    ");
    $stmt->execute(['token' => $token]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        showErrorPage(
            'Link ungültig oder abgelaufen',
            'Dieser Bestätigungslink ist ungültig, wurde bereits verwendet oder die Buchung wurde bereits bearbeitet.'
        );
        exit;
    }

    // Buchung auf confirmed setzen
    $stmt = $conn->prepare("
        UPDATE bookings
        SET status = 'confirmed', confirmation_token = NULL
        WHERE booking_id = :booking_id
    ");
    $stmt->execute(['booking_id' => $booking['booking_id']]);

    // Provider-Daten laden
    $stmt = $conn->prepare("
        SELECT user_id, first_name, last_name, email, phone
        FROM users
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $booking['provider_id']]);
    $provider = $stmt->fetch(PDO::FETCH_ASSOC);

    // Gast-Informationen laden
    $guestInfo = getGuestInfo($conn, $booking['booking_id']);

    // Place-Daten laden
    $stmt = $conn->prepare("SELECT * FROM places WHERE place_id = :place_id");
    $stmt->execute(['place_id' => $booking['place_id']]);
    $place = $stmt->fetch(PDO::FETCH_ASSOC);

    // E-Mails versenden
    $emailService = new EmailService();

    $emailService->sendBookingConfirmationToUser($booking, $place, $provider, $guestInfo);
    $emailService->sendBookingConfirmationToProvider($booking, $place, $provider, $guestInfo);

    // Erfolgsseite anzeigen
    showSuccessPage(
        'Buchung erfolgreich bestätigt! ✓',
        'Die Buchung wurde bestätigt. Der Gast und Sie selbst haben eine Bestätigungs-E-Mail mit allen Details erhalten.',
        $booking['booking_reference']
    );

} catch (Exception $e) {
    error_log("Fehler bei Buchungsbestätigung: " . $e->getMessage());
    showErrorPage('Fehler', 'Ein Fehler ist aufgetreten. Bitte kontaktieren Sie den Support.');
}

// Hilfsfunktionen für HTML-Ausgabe

function showSuccessPage($title, $message, $bookingReference = null) {
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 50px 20px;
                margin: 0;
            }
            .box {
                background-color: white;
                max-width: 500px;
                margin: 0 auto;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                text-align: center;
            }
            .icon { font-size: 80px; margin-bottom: 20px; }
            h1 { color: #4CAF50; margin: 0 0 20px 0; }
            p { color: #555; line-height: 1.6; }
            .booking-ref {
                background-color: #f0f0f0;
                padding: 10px 20px;
                border-radius: 5px;
                display: inline-block;
                margin-top: 20px;
                font-family: monospace;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <div class="box">
            <div class="icon">✓</div>
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><?= htmlspecialchars($message) ?></p>
            <?php if ($bookingReference): ?>
                <div class="booking-ref">
                    <strong>Buchungsnummer:</strong><br>
                    <?= htmlspecialchars($bookingReference) ?>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

function showErrorPage($title, $message) {
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($title) ?></title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                padding: 50px 20px;
                margin: 0;
            }
            .box {
                background-color: white;
                max-width: 500px;
                margin: 0 auto;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                text-align: center;
            }
            .icon { font-size: 80px; margin-bottom: 20px; }
            h1 { color: #f44336; margin: 0 0 20px 0; }
            p { color: #555; line-height: 1.6; }
        </style>
    </head>
    <body>
        <div class="box">
            <div class="icon">⚠️</div>
            <h1><?= htmlspecialchars($title) ?></h1>
            <p><?= htmlspecialchars($message) ?></p>
        </div>
    </body>
    </html>
    <?php
}
