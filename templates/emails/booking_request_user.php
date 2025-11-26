<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buchungsanfrage erhalten - OttbergenLocations</title>
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

        .pending-message {
            background-color: #fff3cd;
            border-left: 4px solid #b8946f;
            padding: 20px;
            margin: 25px 0;
        }

        .pending-message p {
            color: #856404;
            font-weight: 700;
            margin: 0;
            font-size: 16px;
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
            background-color: #e3f2fd;
            border-left: 4px solid #1976d2;
            padding: 25px;
            margin: 25px 0;
        }

        .payment-box h3 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #1976d2;
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

        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #1976d2;
            padding: 20px;
            margin: 25px 0;
        }

        .info-box p {
            margin: 0;
            color: #5c442f;
            font-size: 14px;
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
            <h2>Ihre Buchungsanfrage</h2>

            <p><?= htmlspecialchars($salutation) ?>,</p>

            <p>vielen Dank für Ihre Buchungsanfrage über OttbergenLocations!</p>

            <div class="pending-message">
                <p>&#9200; Ihre Buchung befindet sich im Status "Ausstehend" und wartet auf die Bestätigung des Anbieters.</p>
            </div>

            <p>Wir haben Ihre Anfrage erhalten und an den Anbieter weitergeleitet. Sie erhalten eine weitere E-Mail, sobald der Anbieter Ihre Buchung bestätigt oder ablehnt.</p>

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
                        <td><span class="highlight">Ausstehend</span></td>
                    </tr>
                </table>
            </div>

            <?php if (strpos($payment_method, 'Überweisung') !== false): ?>
            <!-- Zahlungsinformationen bei Überweisung (für später) -->
            <div class="payment-box">
                <h3>Zahlungsinformationen</h3>
                <p>Nach Bestätigung durch den Anbieter überweisen Sie bitte den Betrag von <strong><?= $total_price ?> &euro;</strong> an folgendes Konto:</p>

                <div class="bank-details">
                    <?= $bank_details ?>
                    <p style="margin-top: 15px;">
                        <strong>Verwendungszweck:</strong><br>
                        <?= htmlspecialchars($booking_reference) ?>
                    </p>
                </div>

                <p style="font-size: 14px; color: #856404; margin-top: 15px;">
                    <strong>Wichtig:</strong> Bitte überweisen Sie den Betrag erst nach Bestätigung der Buchung und bis spätestens 7 Tage vor Check-in.
                </p>
            </div>
            <?php endif; ?>

            <div class="info-box">
                <p><strong>Hinweis:</strong> Sie erhalten eine weitere E-Mail, sobald der Anbieter Ihre Buchung bestätigt oder ablehnt. Dies erfolgt in der Regel innerhalb von 24-48 Stunden.</p>
            </div>

            <div class="divider"></div>

            <!-- Stornierungsinformationen -->
            <div style="background-color: #fff3cd; border-left: 4px solid #ff9800; padding: 20px; margin: 25px 0;">
                <h3 style="font-family: 'Playfair Display', Georgia, serif; color: #856404; font-size: 18px; margin-top: 0; margin-bottom: 10px;">Stornierung</h3>
                <p style="margin: 0 0 10px 0; color: #5c442f; font-size: 14px;">
                    Falls Sie Ihre Buchungsanfrage stornieren möchten, können Sie dies jederzeit über den untenstehenden Link tun.
                </p>
                <?php if (!empty($cancellation_link)): ?>
                <p style="text-align: center; margin: 15px 0;">
                    <a href="<?= $cancellation_link ?>" style="display: inline-block; padding: 12px 30px; background-color: #8b6f47; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; box-shadow: 0 4px 6px rgba(61, 40, 23, 0.15);">Buchung stornieren</a>
                </p>
                <?php endif; ?>
                <p style="margin: 10px 0 0 0; color: #856404; font-size: 12px;">
                    <strong>Hinweis:</strong> Die Stornierung ist in der Regel kostenlos. Nach der Stornierung erhalten Sie eine Bestätigungs-E-Mail.
                </p>
            </div>

            <p style="text-align: center;">Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
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
