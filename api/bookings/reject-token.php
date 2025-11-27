<?php
// GET/POST /api/bookings/reject-token.php - Buchung per Token ablehnen

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/EmailService.php';
require_once __DIR__ . '/../../helpers/booking.php';

// Frontend-URL aus Umgebungsvariablen oder Standard
$frontend_url = getenv('FRONTEND_URL') ?: 'http://localhost:5173';

$token = $_GET['token'] ?? null;

if (!$token) {
    $error_title = 'Ungültiger Link';
    $error_message = 'Es wurde kein Token übergeben.';
    $error_details = '';
    $retry_url = '';
    require __DIR__ . '/../../templates/error_page.php';
    exit;
}

// GET: Formular anzeigen
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $error_message = '';
    $previous_reason = '';
    require __DIR__ . '/../../templates/booking_rejection_form.php';
    exit;
}

// POST: Ablehnung verarbeiten
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = trim($_POST['reason'] ?? '');

    if (empty($reason)) {
        $error_message = 'Bitte geben Sie einen Grund für die Ablehnung an.';
        $previous_reason = $reason;
        require __DIR__ . '/../../templates/booking_rejection_form.php';
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
            $error_title = 'Link ungültig oder abgelaufen';
            $error_message = 'Dieser Ablehnungslink ist ungültig, wurde bereits verwendet oder die Buchung wurde bereits bearbeitet.';
            $error_details = '';
            $retry_url = '';
            require __DIR__ . '/../../templates/error_page.php';
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
        $booking_reference = $booking['booking_reference'];
        require __DIR__ . '/../../templates/booking_rejection_success.php';

    } catch (Exception $e) {
        error_log("Fehler bei Buchungsablehnung: " . $e->getMessage());
        $error_title = 'Fehler';
        $error_message = 'Ein Fehler ist aufgetreten.';
        $error_details = 'Bitte kontaktieren Sie den Support.';
        $retry_url = '';
        require __DIR__ . '/../../templates/error_page.php';
    }
    exit;
}