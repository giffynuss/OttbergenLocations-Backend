<?php
// Pricing Helper - Preisberechnungen

/**
 * Berechnet den Gesamtpreis einer Buchung
 * @param float $pricePerDay Preis pro Tag
 * @param string $checkIn Check-in Datum
 * @param string $checkOut Check-out Datum
 * @return array ['totalPrice', 'days']
 */
function calculateBookingPrice($pricePerDay, $checkIn, $checkOut) {
    // Anzahl der Tage berechnen
    $checkInDate = new DateTime($checkIn);
    $checkOutDate = new DateTime($checkOut);
    $interval = $checkInDate->diff($checkOutDate);
    $days = $interval->days;

    // Preisberechnung: Nur Tagespreis * Anzahl Tage
    $totalPrice = round($pricePerDay * $days, 2);

    return [
        'totalPrice' => $totalPrice,
        'days' => $days
    ];
}
