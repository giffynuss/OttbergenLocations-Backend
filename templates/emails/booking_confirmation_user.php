<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buchung bestätigt - OttbergenLocations</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap');

        body {
            margin: 0;
            padding: 0;
            font-family: 'Lato', 'Helvetica Neue', sans-serif;
            background-color: #e8dfd5;
            color: #3d2817;
        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #f5f0e8;
            box-shadow: 0 4px 6px rgba(61, 40, 23, 0.15);
        }

        .header {
            background-color: #3d2817;
            color: #f5f0e8;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 28px;
            margin: 0;
            letter-spacing: 0.05em;
        }

        .content {
            padding: 40px 30px;
        }

        h2 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #3d2817;
            font-size: 24px;
            margin-bottom: 20px;
            letter-spacing: 0.05em;
        }

        p {
            font-size: 16px;
            line-height: 1.6;
            color: #5c442f;
            margin-bottom: 15px;
        }

        .success-message {
            background-color: #d4f4dd;
            border-left: 4px solid #4CAF50;
            padding: 20px;
            margin: 25px 0;
        }

        .success-message p {
            color: #2e7d32;
            font-weight: 700;
            margin: 0;
            font-size: 18px;
        }

        .details-box {
            background-color: #e8dfd5;
            padding: 25px;
            margin: 25px 0;
            border-left: 4px solid #b8946f;
        }

        .details-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-box td {
            padding: 8px 0;
            font-size: 15px;
        }

        .details-box td:first-child {
            font-weight: 700;
            color: #3d2817;
            width: 40%;
        }

        .details-box td:last-child {
            color: #5c442f;
        }

        .payment-box {
            background-color: #fff3cd;
            border-left: 4px solid #b8946f;
            padding: 25px;
            margin: 25px 0;
        }

        .payment-box h3 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #3d2817;
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 15px;
        }

        .payment-box p {
            margin: 8px 0;
            color: #5c442f;
        }

        .payment-box strong {
            color: #3d2817;
        }

        .bank-details {
            background-color: #ffffff;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #d4c4b0;
        }

        .footer {
            background-color: #d4c4b0;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #5c442f;
        }

        .footer p {
            margin: 5px 0;
            font-size: 14px;
        }

        .footer a {
            color: #5c442f;
            text-decoration: none;
        }

        .divider {
            height: 1px;
            background-color: #d4c4b0;
            margin: 30px 0;
        }

        .highlight {
            color: #b8946f;
            font-weight: 700;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                box-shadow: none;
            }

            .content {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>OttbergenLocations</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>Buchung bestätigt!</h2>

            <p><?= htmlspecialchars($salutation) ?>,</p>

            <div class="success-message">
                <p>&#10004; Großartige Neuigkeiten! Ihre Buchung wurde vom Anbieter bestätigt.</p>
            </div>

            <p>Wir freuen uns, Ihnen mitteilen zu können, dass Ihre Buchungsanfrage angenommen wurde. Ihre Veranstaltung kann wie geplant stattfinden!</p>

            <!-- Buchungsdetails -->
            <div class="details-box">
                <table>
                    <tr>
                        <td>Buchungsreferenz:</td>
                        <td><strong><?= htmlspecialchars($booking_reference) ?></strong></td>
                    </tr>
                    <tr>
                        <td>Location:</td>
                        <td><?= htmlspecialchars($place_name) ?></td>
                    </tr>
                    <tr>
                        <td>Standort:</td>
                        <td><?= htmlspecialchars($place_location) ?></td>
                    </tr>
                    <tr>
                        <td>Check-in:</td>
                        <td><?= $check_in ?></td>
                    </tr>
                    <tr>
                        <td>Check-out:</td>
                        <td><?= $check_out ?></td>
                    </tr>
                    <tr>
                        <td>Gäste:</td>
                        <td><?= $guests ?> Personen</td>
                    </tr>
                    <tr>
                        <td>Gesamtpreis:</td>
                        <td><strong style="font-size: 18px; color: #b8946f;"><?= $total_price ?> &euro;</strong></td>
                    </tr>
                    <tr>
                        <td>Zahlungsmethode:</td>
                        <td><?= htmlspecialchars($payment_method) ?></td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><span class="highlight">Bestätigt</span></td>
                    </tr>
                </table>
            </div>

            <?php if (strpos($payment_method, 'Überweisung') !== false): ?>
            <!-- Zahlungshinweis bei Überweisung -->
            <div class="payment-box">
                <h3>Zahlungsinformationen</h3>
                <p>Bitte überweisen Sie den Betrag von <strong><?= $total_price ?> &euro;</strong> an folgendes Konto:</p>

                <div class="bank-details">
                    <?= $bank_details ?>
                    <p style="margin-top: 15px;">
                        <strong>Verwendungszweck:</strong><br>
                        <?= htmlspecialchars($booking_reference) ?>
                    </p>
                </div>

                <p style="font-size: 14px; color: #856404; margin-top: 15px;">
                    <strong>Wichtig:</strong> Bitte überweisen Sie den Betrag bis spätestens 7 Tage vor Check-in und verwenden Sie unbedingt die Buchungsreferenz als Verwendungszweck.
                </p>
            </div>
            <?php elseif (strpos($payment_method, 'Barzahlung') !== false): ?>
            <!-- Hinweis bei Barzahlung -->
            <div class="payment-box">
                <h3>Zahlungsinformationen</h3>
                <p>Die Zahlung erfolgt in bar vor Ort beim Anbieter.</p>
                <p style="font-size: 14px; color: #856404;">Bitte halten Sie den Betrag von <strong><?= $total_price ?> &euro;</strong> passend bereit.</p>
            </div>
            <?php endif; ?>

            <!-- Kontakt zum Anbieter -->
            <div class="details-box">
                <table>
                    <tr>
                        <td colspan="2" style="padding-bottom: 15px;">
                            <strong style="font-size: 16px; color: #3d2817;">Kontakt zum Anbieter</strong>
                        </td>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td><?= htmlspecialchars($provider_name) ?></td>
                    </tr>
                    <tr>
                        <td>E-Mail:</td>
                        <td><a href="mailto:<?= htmlspecialchars($provider_email) ?>" style="color: #5c442f;"><?= htmlspecialchars($provider_email) ?></a></td>
                    </tr>
                    <tr>
                        <td>Telefon:</td>
                        <td><a href="tel:<?= htmlspecialchars($provider_phone) ?>" style="color: #5c442f;"><?= htmlspecialchars($provider_phone) ?></a></td>
                    </tr>
                </table>
            </div>

            <div class="divider"></div>

            <p style="text-align: center; font-size: 18px; color: #3d2817; font-family: 'Playfair Display', Georgia, serif;">
                Wir wünschen Ihnen eine wunderbare Veranstaltung!
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>OttbergenLocations</strong></p>
            <p>Ihre Plattform für exklusive Veranstaltungsorte</p>
            <p style="margin-top: 15px; font-size: 13px;">
                Bei Fragen erreichen Sie uns unter:<br>
                <a href="mailto:info@ottbergenlocations.de">info@ottbergenlocations.de</a>
            </p>
        </div>
    </div>
</body>
</html>
