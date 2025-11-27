<?php
// E-Mail-Service für Buchungsbenachrichtigungen
// Verwendet PHPMailer für SMTP-Versand

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private $config;
    private $mailer;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../config/mail.php';
        $this->mailer = new PHPMailer(true);
        $this->configureSMTP();
    }

    /**
     * Konfiguriert SMTP-Einstellungen
     */
    private function configureSMTP()
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['smtp_host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['smtp_user'];
        $this->mailer->Password = $this->config['smtp_pass'];
        $this->mailer->SMTPSecure = $this->config['smtp_secure'];
        $this->mailer->Port = $this->config['smtp_port'];
        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);

        // Debug-Modus aktivieren
        $this->mailer->SMTPDebug = 2; // 0=off, 1=client, 2=client+server
        $this->mailer->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug [$level]: $str");
        };

        error_log("=== EMAIL CONFIG ===");
        error_log("SMTP Host: " . $this->config['smtp_host']);
        error_log("SMTP Port: " . $this->config['smtp_port']);
        error_log("SMTP User: " . $this->config['smtp_user']);
        error_log("From Email: " . $this->config['from_email']);
        error_log("====================");
    }

    /**
     * Sendet Buchungsanfrage-Bestätigung an User (Status: pending)
     *
     * @param array $booking Buchungsdaten (muss 'cancellation_token' enthalten)
     * @param array $place Ortsdaten
     * @param array $guestInfo Gastdaten
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendBookingRequestToUser($booking, $place, $guestInfo)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($guestInfo['email'], $guestInfo['firstName'] . ' ' . $guestInfo['lastName']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Ihre Buchungsanfrage bei OttbergenLocations - {$place['name']}";

            // Stornierungslink generieren (falls Token vorhanden)
            $cancellationLink = '';
            if (!empty($booking['cancellation_token'])) {
                $cancellationLink = $this->config['base_url'] . "/api/bookings/cancel-token.php?token={$booking['cancellation_token']}";
            }

            $html = $this->loadTemplate('booking_request_user', [
                'salutation' => $this->formatSalutation($guestInfo['gender'] ?? '', $guestInfo['lastName']),
                'place_name' => $place['name'],
                'place_location' => $place['location'],
                'check_in' => $this->formatDate($booking['check_in']),
                'check_out' => $this->formatDate($booking['check_out']),
                'guests' => $booking['guests'],
                'total_price' => number_format($booking['total_price'], 2, ',', '.'),
                'payment_method' => $this->getPaymentMethodLabel($booking['payment_method']),
                'bank_details' => $this->formatBankDetails(),
                'booking_reference' => $booking['booking_reference'],
                'cancellation_link' => $cancellationLink
            ]);

            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);

            $this->mailer->send();
            return ['success' => true, 'message' => 'Buchungsanfrage-E-Mail an User gesendet'];

        } catch (Exception $e) {
            error_log("E-Mail-Fehler: {$this->mailer->ErrorInfo}");
            return ['success' => false, 'message' => "E-Mail konnte nicht gesendet werden: {$this->mailer->ErrorInfo}"];
        }
    }

    /**
     * Sendet Buchungsanfrage an Anbieter mit Bestätigen/Ablehnen-Links
     *
     * @param array $booking Buchungsdaten
     * @param array $place Ortsdaten
     * @param array $provider Anbieterdaten (aus users Tabelle)
     * @param array $guestInfo Gastdaten (aus booking_guest_info)
     * @param string $token Bestätigungs-Token
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendBookingRequestToProvider($booking, $place, $provider, $guestInfo, $token)
    {
        try {
            error_log("=== STARTE E-MAIL VERSAND ===");
            error_log("Empfänger: " . $provider['email'] . " (" . $provider['first_name'] . " " . $provider['last_name'] . ")");
            error_log("Betreff: Neue Buchungsanfrage für {$place['name']}");

            $this->mailer->clearAddresses();
            $this->mailer->addAddress($provider['email'], $provider['first_name'] . ' ' . $provider['last_name']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Neue Buchungsanfrage für {$place['name']}";

            $confirmLink = $this->config['base_url'] . "/api/bookings/confirm-token.php?token={$token}";
            $rejectLink = $this->config['base_url'] . "/api/bookings/reject-token.php?token={$token}";

            $html = $this->loadTemplate('booking_request_provider', [
                'salutation' => $this->formatSalutation($provider['gender'] ?? '', $provider['last_name']),
                'place_name' => $place['name'],
                'check_in' => $this->formatDate($booking['check_in']),
                'check_out' => $this->formatDate($booking['check_out']),
                'guests' => $booking['guests'],
                'total_price' => number_format($booking['total_price'], 2, ',', '.'),
                'guest_name' => $this->formatGuestName($guestInfo),
                'guest_email' => $guestInfo['email'],
                'guest_phone' => $guestInfo['phone'],
                'guest_address' => $this->formatAddress($guestInfo),
                'payment_method' => $this->getPaymentMethodLabel($booking['payment_method']),
                'confirm_link' => $confirmLink,
                'reject_link' => $rejectLink,
                'booking_reference' => $booking['booking_reference']
            ]);

            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);

            error_log("Sende E-Mail...");
            $this->mailer->send();
            error_log("✓ E-MAIL ERFOLGREICH GESENDET!");
            error_log("=============================");
            return ['success' => true, 'message' => 'E-Mail an Anbieter gesendet'];

        } catch (Exception $e) {
            error_log("✗ E-MAIL FEHLER: " . $e->getMessage());
            error_log("=============================");
            error_log("E-Mail-Fehler: {$this->mailer->ErrorInfo}");
            return ['success' => false, 'message' => "E-Mail konnte nicht gesendet werden: {$this->mailer->ErrorInfo}"];
        }
    }

    /**
     * Sendet Bestätigung an User mit Zahlungsdetails
     *
     * @param array $booking Buchungsdaten (muss 'cancellation_token' enthalten)
     * @param array $place Ortsdaten
     * @param array $provider Anbieterdaten
     * @param array $guestInfo Gastdaten
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendBookingConfirmationToUser($booking, $place, $provider, $guestInfo)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($guestInfo['email'], $guestInfo['firstName'] . ' ' . $guestInfo['lastName']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Buchung bestätigt - {$place['name']} - " . $this->formatDate($booking['check_in']);

            // Stornierungslink generieren (falls Token vorhanden)
            $cancellationLink = '';
            if (!empty($booking['cancellation_token'])) {
                $cancellationLink = $this->config['base_url'] . "/api/bookings/cancel-token.php?token={$booking['cancellation_token']}";
            }

            $html = $this->loadTemplate('booking_confirmation_user', [
                'salutation' => $this->formatSalutation($guestInfo['gender'] ?? '', $guestInfo['lastName']),
                'place_name' => $place['name'],
                'place_location' => $place['location'],
                'check_in' => $this->formatDate($booking['check_in']),
                'check_out' => $this->formatDate($booking['check_out']),
                'guests' => $booking['guests'],
                'total_price' => number_format($booking['total_price'], 2, ',', '.'),
                'payment_method' => $this->getPaymentMethodLabel($booking['payment_method']),
                'bank_details' => $this->formatBankDetails(),
                'booking_reference' => $booking['booking_reference'],
                'provider_name' => $provider['first_name'] . ' ' . $provider['last_name'],
                'provider_phone' => $provider['phone'] ?? 'Nicht verfügbar',
                'provider_email' => $provider['email'],
                'cancellation_link' => $cancellationLink
            ]);

            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);

            $this->mailer->send();
            return ['success' => true, 'message' => 'Bestätigungs-E-Mail an User gesendet'];

        } catch (Exception $e) {
            error_log("E-Mail-Fehler: {$this->mailer->ErrorInfo}");
            return ['success' => false, 'message' => "E-Mail konnte nicht gesendet werden: {$this->mailer->ErrorInfo}"];
        }
    }

    /**
     * Sendet Bestätigung an Anbieter (Kopie für eigene Unterlagen)
     *
     * @param array $booking Buchungsdaten
     * @param array $place Ortsdaten
     * @param array $provider Anbieterdaten
     * @param array $guestInfo Gastdaten
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendBookingConfirmationToProvider($booking, $place, $provider, $guestInfo)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($provider['email'], $provider['first_name'] . ' ' . $provider['last_name']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Buchungsbestätigung - {$place['name']}";

            $html = $this->loadTemplate('booking_confirmation_provider', [
                'salutation' => $this->formatSalutation($provider['gender'] ?? '', $provider['last_name']),
                'place_name' => $place['name'],
                'check_in' => $this->formatDate($booking['check_in']),
                'check_out' => $this->formatDate($booking['check_out']),
                'guests' => $booking['guests'],
                'total_price' => number_format($booking['total_price'], 2, ',', '.'),
                'guest_name' => $this->formatGuestName($guestInfo),
                'guest_phone' => $guestInfo['phone'],
                'guest_email' => $guestInfo['email'],
                'booking_reference' => $booking['booking_reference']
            ]);

            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);

            $this->mailer->send();
            return ['success' => true, 'message' => 'Bestätigungs-E-Mail an Anbieter gesendet'];

        } catch (Exception $e) {
            error_log("E-Mail-Fehler: {$this->mailer->ErrorInfo}");
            return ['success' => false, 'message' => "E-Mail konnte nicht gesendet werden: {$this->mailer->ErrorInfo}"];
        }
    }

    /**
     * Sendet Ablehnungs-E-Mail an User
     *
     * @param array $booking Buchungsdaten
     * @param array $place Ortsdaten
     * @param array $guestInfo Gastdaten
     * @param string $reason Ablehnungsgrund
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendBookingRejectionToUser($booking, $place, $guestInfo, $reason = '')
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($guestInfo['email'], $guestInfo['firstName'] . ' ' . $guestInfo['lastName']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Buchungsanfrage abgelehnt - {$place['name']}";

            $html = $this->loadTemplate('booking_rejection_user', [
                'salutation' => $this->formatSalutation($guestInfo['gender'] ?? '', $guestInfo['lastName']),
                'place_name' => $place['name'],
                'check_in' => $this->formatDate($booking['check_in']),
                'check_out' => $this->formatDate($booking['check_out']),
                'rejection_reason' => $reason ?: 'Keine Angabe',
                'booking_reference' => $booking['booking_reference'],
                'frontend_url' => $this->getFrontendUrl()
            ]);

            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);

            $this->mailer->send();
            return ['success' => true, 'message' => 'Ablehnungs-E-Mail an User gesendet'];

        } catch (Exception $e) {
            error_log("E-Mail-Fehler: {$this->mailer->ErrorInfo}");
            return ['success' => false, 'message' => "E-Mail konnte nicht gesendet werden: {$this->mailer->ErrorInfo}"];
        }
    }

    /**
     * Sendet Stornierungsbestätigung an User
     *
     * @param array $booking Buchungsdaten
     * @param array $place Ortsdaten
     * @param array $guestInfo Gastdaten
     * @param string $reason Stornierungsgrund (optional)
     * @param string $refundInfo Rückerstattungsinformationen (optional)
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendBookingCancellationToUser($booking, $place, $guestInfo, $reason = '', $refundInfo = '')
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($guestInfo['email'], $guestInfo['firstName'] . ' ' . $guestInfo['lastName']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Stornierung bestätigt - {$place['name']}";

            $html = $this->loadTemplate('booking_cancellation', [
                'salutation' => $this->formatSalutation($guestInfo['gender'] ?? '', $guestInfo['lastName']),
                'place_name' => $place['name'],
                'place_location' => $place['location'],
                'check_in' => $this->formatDate($booking['check_in']),
                'check_out' => $this->formatDate($booking['check_out']),
                'guests' => $booking['guests'],
                'total_price' => number_format($booking['total_price'], 2, ',', '.'),
                'booking_reference' => $booking['booking_reference'],
                'cancellation_reason' => $reason,
                'refund_info' => $refundInfo,
                'frontend_url' => $this->getFrontendUrl()
            ]);

            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);

            $this->mailer->send();
            return ['success' => true, 'message' => 'Stornierungsbestätigung an User gesendet'];

        } catch (Exception $e) {
            error_log("E-Mail-Fehler: {$this->mailer->ErrorInfo}");
            return ['success' => false, 'message' => "E-Mail konnte nicht gesendet werden: {$this->mailer->ErrorInfo}"];
        }
    }

    // ==================== HILFSFUNKTIONEN ====================

    /**
     * Lädt E-Mail-Template und füllt Platzhalter
     */
    private function loadTemplate($templateName, $data)
    {
        $templatePath = __DIR__ . "/../templates/emails/{$templateName}.php";
        if (!file_exists($templatePath)) {
            throw new Exception("Template nicht gefunden: {$templateName}");
        }

        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Formatiert Datum in deutsches Format
     */
    private function formatDate($dateString)
    {
        $date = new DateTime($dateString);
        return $date->format('d.m.Y');
    }

    /**
     * Formatiert Adresse aus Gastdaten
     */
    private function formatAddress($guestInfo)
    {
        $parts = [];
        if (!empty($guestInfo['street'])) $parts[] = $guestInfo['street'];
        if (!empty($guestInfo['postalCode']) && !empty($guestInfo['city'])) {
            $parts[] = $guestInfo['postalCode'] . ' ' . $guestInfo['city'];
        }
        return !empty($parts) ? implode(', ', $parts) : 'Keine Adresse angegeben';
    }

    /**
     * Formatiert Bankdaten für E-Mail (statisch aus Config)
     */
    private function formatBankDetails()
    {
        $bank = $this->config['bank_details'];

        return "
            <strong>Kontoinhaber:</strong> {$bank['account_holder']}<br>
            <strong>IBAN:</strong> {$bank['iban']}<br>
            <strong>BIC:</strong> {$bank['bic']}<br>
            <strong>Bank:</strong> {$bank['bank_name']}
        ";
    }

    /**
     * Übersetzt Zahlungsmethode in lesbare Form
     */
    private function getPaymentMethodLabel($method)
    {
        $labels = [
            'cash' => 'Barzahlung',
            'paypal' => 'PayPal',
            'transfer' => 'Überweisung',
            'wero' => 'Wero'
        ];
        return $labels[$method] ?? $method;
    }

    /**
     * Formatiert Anrede basierend auf Gender
     *
     * @param string $gender 'herr' oder 'frau' (lowercase aus DB)
     * @param string $lastName Nachname
     * @return string Formatierte Anrede (z.B. "Sehr geehrter Herr Müller")
     */
    private function formatSalutation($gender, $lastName)
    {
        $gender = strtolower(trim($gender));

        if ($gender === 'herr') {
            return "Sehr geehrter Herr " . htmlspecialchars($lastName);
        } elseif ($gender === 'frau') {
            return "Sehr geehrte Frau " . htmlspecialchars($lastName);
        } else {
            // Fallback wenn Gender nicht gesetzt oder unbekannt
            return "Sehr geehrte Damen und Herren";
        }
    }

    /**
     * Formatiert Gast-Name mit Gender-Titel
     *
     * @param array $guestInfo Gastdaten mit firstName, lastName, gender
     * @return string Formatierter Name mit Anrede
     */
    private function formatGuestName($guestInfo)
    {
        $gender = strtolower(trim($guestInfo['gender'] ?? ''));
        $title = '';

        if ($gender === 'herr') {
            $title = 'Herr ';
        } elseif ($gender === 'frau') {
            $title = 'Frau ';
        }

        return $title . $guestInfo['firstName'] . ' ' . $guestInfo['lastName'];
    }

    /**
     * Gibt die Frontend-URL zurück (aus config oder Standard)
     *
     * @return string Frontend-URL
     */
    private function getFrontendUrl()
    {
        // Standardmäßig localhost:5173 für Entwicklung
        // In Produktion sollte dies in der Config gesetzt werden
        return $this->config['frontend_url'] ?? 'http://localhost:5173';
    }
}
