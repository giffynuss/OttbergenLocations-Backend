<?php
// GET /api/bookings/confirm-token.php - Buchung per Token bestätigen

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
        $error_title = 'Link ungültig oder abgelaufen';
        $error_message = 'Dieser Bestätigungslink ist ungültig, wurde bereits verwendet oder die Buchung wurde bereits bearbeitet.';
        $error_details = '';
        $retry_url = '';
        require __DIR__ . '/../../templates/error_page.php';
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
    $booking_reference = $booking['booking_reference'];
    require __DIR__ . '/../../templates/booking_confirmation_success.php';

} catch (Exception $e) {
    error_log("Fehler bei Buchungsbestätigung: " . $e->getMessage());
    $error_title = 'Fehler';
    $error_message = 'Ein Fehler ist aufgetreten.';
    $error_details = 'Bitte kontaktieren Sie den Support.';
    $retry_url = '';
    require __DIR__ . '/../../templates/error_page.php';
}