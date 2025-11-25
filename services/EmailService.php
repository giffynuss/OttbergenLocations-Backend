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
                'provider_name' => $provider['first_name'] . ' ' . $provider['last_name'],
                'place_name' => $place['name'],
                'check_in' => $this->formatDate($booking['check_in']),
                'check_out' => $this->formatDate($booking['check_out']),
                'guests' => $booking['guests'],
                'total_price' => number_format($booking['total_price'], 2, ',', '.'),
                'guest_name' => $guestInfo['firstName'] . ' ' . $guestInfo['lastName'],
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
     * @param array $booking Buchungsdaten
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
            $this->mailer->Subject = "Ihre Buchung wurde bestätigt! 🎉";

            $html = $this->loadTemplate('booking_confirmation_user', [
                'guest_first_name' => $guestInfo['firstName'],
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
                'provider_email' => $provider['email']
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
                'provider_name' => $provider['first_name'] . ' ' . $provider['last_name'],
                'place_name' => $place['name'],
                'check_in' => $this->formatDate($booking['check_in']),
                'check_out' => $this->formatDate($booking['check_out']),
                'guests' => $booking['guests'],
                'total_price' => number_format($booking['total_price'], 2, ',', '.'),
                'guest_name' => $guestInfo['firstName'] . ' ' . $guestInfo['lastName'],
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
    public function sendBookingRejectionToUser($booking, $place, $guestInfo, $reason)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($guestInfo['email'], $guestInfo['firstName'] . ' ' . $guestInfo['lastName']);

            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Buchungsanfrage wurde abgelehnt - {$place['name']}";

            $html = $this->loadTemplate('booking_rejection_user', [
                'guest_first_name' => $guestInfo['firstName'],
                'place_name' => $place['name'],
                'check_in' => $this->formatDate($booking['check_in']),
                'check_out' => $this->formatDate($booking['check_out']),
                'rejection_reason' => $reason ?: 'Keine Angabe',
                'booking_reference' => $booking['booking_reference']
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
}
