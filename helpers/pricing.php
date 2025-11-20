<?php
// Pricing Helper - Preisberechnungen

/**
 * Berechnet den Gesamtpreis einer Buchung
 * @param PDO $conn Datenbankverbindung
 * @param float $pricePerDay Preis pro Tag
 * @param string $checkIn Check-in Datum
 * @param string $checkOut Check-out Datum
 * @return array ['subtotal', 'serviceFee', 'tax', 'totalPrice', 'days']
 */
function calculateBookingPrice($conn, $pricePerDay, $checkIn, $checkOut) {
    // Anzahl der Tage berechnen
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    $interval = $checkInDate->diff($checkOutDate);
    $days = $interval->days;

    // Einstellungen aus Datenbank laden
    $settings = getSettings($conn);
    $serviceFeePercentage = $settings['service_fee_percentage'];
    $taxPercentage = $settings['tax_percentage'];

    // Preisberechnung
    $subtotal = round($pricePerDay * $days, 2);
    $serviceFee = round($subtotal * $serviceFeePercentage, 2);
    $tax = round(($subtotal + $serviceFee) * $taxPercentage, 2);
    $totalPrice = round($subtotal + $serviceFee + $tax, 2);

    return [
        'subtotal' => $subtotal,
        'serviceFee' => $serviceFee,
        'tax' => $tax,
        'totalPrice' => $totalPrice,
        'days' => $days
    ];
}

/**
 * Lädt die Einstellungen aus der Datenbank
 * @param PDO $conn Datenbankverbindung
 * @return array Assoziatives Array mit den Einstellungen
 */
function getSettings($conn) {
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = floatval($row['setting_value']);
    }

    // Default-Werte falls nicht in DB
    return array_merge([
        'service_fee_percentage' => 0.05,
        'tax_percentage' => 0.19,
        'cancellation_deadline_hours' => 48
    ], $settings);
}

/**
 * Prüft ob eine Stornierung noch innerhalb der Frist möglich ist
 * @param PDO $conn Datenbankverbindung
 * @param string $checkIn Check-in Datum
 * @return array ['allowed' => bool, 'message' => string]
 */
function canCancelBooking($conn, $checkIn) {
    $settings = getSettings($conn);
    $deadlineHours = $settings['cancellation_deadline_hours'];

    $checkInDate = new DateTime($checkIn);
    $now = new DateTime();
    $deadline = clone $checkInDate;
    $deadline->modify("-{$deadlineHours} hours");

    if ($now > $deadline) {
        return [
            'allowed' => false,
            'message' => "Die kostenlose Stornierungsfrist von {$deadlineHours} Stunden wurde überschritten."
        ];
    }

    return [
        'allowed' => true,
        'message' => 'Stornierung innerhalb der Frist möglich.'
    ];
}
