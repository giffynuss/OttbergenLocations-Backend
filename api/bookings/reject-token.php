<?php
// GET/POST /api/bookings/reject-token.php - Buchung per Token ablehnen

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/EmailService.php';
require_once __DIR__ . '/../../helpers/booking.php';

$token = $_GET['token'] ?? null;

if (!$token) {
    showErrorPage('Ungültiger Link', 'Es wurde kein Token übergeben.');
    exit;
}

// GET: Formular anzeigen
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    showRejectionForm($token);
    exit;
}

// POST: Ablehnung verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');

    if (empty($reason)) {
        showRejectionForm($token, 'Bitte geben Sie einen Grund für die Ablehnung an.');
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->getConnection();

        // Buchung finden
        $stmt = $conn->prepare("
            SELECT
                b.booking_id,
                b.place_id,
                b.check_in,
                b.check_out,
                b.booking_reference,
                b.status,
                p.name as place_name
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
                'Dieser Ablehnungslink ist ungültig, wurde bereits verwendet oder die Buchung wurde bereits bearbeitet.'
            );
            exit;
        }

        // Status auf rejected setzen mit Grund
        $stmt = $conn->prepare("
            UPDATE bookings
            SET status = 'rejected',
                cancellation_reason = :reason,
                confirmation_token = NULL,
                cancelled_at = NOW()
            WHERE booking_id = :booking_id
        ");
        $stmt->execute([
            'reason' => $reason,
            'booking_id' => $booking['booking_id']
        ]);

        // Gast-Daten laden
        $guestInfo = getGuestInfo($conn, $booking['booking_id']);

        // Place-Daten laden
        $stmt = $conn->prepare("SELECT * FROM places WHERE place_id = :place_id");
        $stmt->execute(['place_id' => $booking['place_id']]);
        $place = $stmt->fetch(PDO::FETCH_ASSOC);

        // E-Mail an Gast senden
        $emailService = new EmailService();
        $emailService->sendBookingRejectionToUser($booking, $place, $guestInfo, $reason);

        // Erfolgsseite
        showSuccessPage(
            'Buchung abgelehnt',
            'Die Buchung wurde abgelehnt. Der Gast wurde per E-Mail benachrichtigt.',
            $booking['booking_reference']
        );

    } catch (Exception $e) {
        error_log("Fehler bei Buchungsablehnung: " . $e->getMessage());
        showErrorPage('Fehler', 'Ein Fehler ist aufgetreten. Bitte kontaktieren Sie den Support.');
    }
    exit;
}

// Hilfsfunktionen für HTML-Ausgabe

function showRejectionForm($token, $error = null) {
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Buchung ablehnen</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 50px 20px;
                margin: 0;
            }
            .form-box {
                background-color: white;
                max-width: 500px;
                margin: 0 auto;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            }
            h1 {
                color: #f44336;
                margin: 0 0 10px 0;
                text-align: center;
            }
            .subtitle {
                color: #666;
                text-align: center;
                margin-bottom: 30px;
            }
            label {
                display: block;
                margin-bottom: 8px;
                font-weight: bold;
                color: #333;
            }
            textarea {
                width: 100%;
                padding: 12px;
                margin-bottom: 20px;
                border: 2px solid #ddd;
                border-radius: 5px;
                font-family: Arial, sans-serif;
                font-size: 14px;
                box-sizing: border-box;
                resize: vertical;
            }
            textarea:focus {
                outline: none;
                border-color: #667eea;
            }
            .btn {
                background-color: #f44336;
                color: white;
                padding: 14px 30px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                font-weight: bold;
                width: 100%;
                transition: background-color 0.3s;
            }
            .btn:hover {
                background-color: #d32f2f;
            }
            .error {
                background-color: #ffebee;
                border-left: 4px solid #f44336;
                padding: 12px;
                margin-bottom: 20px;
                border-radius: 3px;
                color: #c62828;
            }
            .info {
                background-color: #e3f2fd;
                border-left: 4px solid #2196F3;
                padding: 12px;
                margin-bottom: 20px;
                border-radius: 3px;
                font-size: 13px;
                color: #1565C0;
            }
        </style>
    </head>
    <body>
        <div class="form-box">
            <h1>❌ Buchung ablehnen</h1>
            <p class="subtitle">Bitte geben Sie einen Grund für die Ablehnung an.</p>

            <?php if ($error): ?>
                <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="info">
                💡 <strong>Hinweis:</strong> Der Gast wird per E-Mail über die Ablehnung informiert und erhält Ihren angegebenen Grund.
            </div>

            <form method="POST">
                <label for="reason">Ablehnungsgrund *</label>
                <textarea
                    id="reason"
                    name="reason"
                    rows="6"
                    placeholder="z.B. Der Zeitraum ist bereits anderweitig vergeben..."
                    required
                ><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>

                <button type="submit" class="btn">Buchung ablehnen</button>
            </form>
        </div>
    </body>
    </html>
    <?php
}

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
            h1 { color: #666; margin: 0 0 20px 0; }
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
