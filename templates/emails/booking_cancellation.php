<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stornierung bestätigt - OttbergenLocations</title>
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

        .cancellation-message {
            background-color: #fff3cd;
            border-left: 4px solid #ff9800;
            padding: 20px;
            margin: 25px 0;
        }

        .cancellation-message p {
            color: #856404;
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

        .reason-box {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 20px;
            margin: 25px 0;
        }

        .reason-box h3 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #2e7d32;
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .reason-box p {
            color: #5c442f;
            margin: 0;
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

        .button {
            display: inline-block;
            padding: 15px 35px;
            background-color: #b8946f;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            text-align: center;
            margin: 10px 0;
            box-shadow: 0 4px 6px rgba(61, 40, 23, 0.15);
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

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                box-shadow: none;
            }

            .content {
                padding: 30px 20px;
            }

            .button {
                display: block;
                width: 100%;
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
            <h2>Stornierung bestätigt</h2>

            <p><?= htmlspecialchars($salutation) ?>,</p>

            <div class="cancellation-message">
                <p>&#10004; Ihre Buchung wurde erfolgreich storniert.</p>
            </div>

            <p>Wir bestätigen Ihnen hiermit die Stornierung Ihrer Buchung.</p>

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
                        <td>Status:</td>
                        <td><span style="color: #ff9800; font-weight: 700;">Storniert</span></td>
                    </tr>
                </table>
            </div>

            <?php if (!empty($cancellation_reason)): ?>
            <!-- Stornierungsgrund -->
            <div class="reason-box">
                <h3>Stornierungsgrund</h3>
                <p><?= nl2br(htmlspecialchars($cancellation_reason)) ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($refund_info)): ?>
            <!-- Rückerstattungsinformationen -->
            <div class="info-box">
                <p><strong>Rückerstattung:</strong> <?= nl2br(htmlspecialchars($refund_info)) ?></p>
            </div>
            <?php endif; ?>

            <p>Wir hoffen, Sie bald wieder bei OttbergenLocations begrüßen zu dürfen!</p>

            <p style="text-align: center; margin-top: 30px;">
                <a href="<?= $frontend_url ?>/search" class="button">Weitere Locations entdecken</a>
            </p>

            <div class="divider"></div>

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
