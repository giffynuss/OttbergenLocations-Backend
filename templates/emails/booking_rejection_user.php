<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buchungsanfrage abgelehnt - OttbergenLocations</title>
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
            background-color: #ffebee;
            border-left: 4px solid #c62828;
            padding: 20px;
            margin: 25px 0;
        }

        .reason-box h3 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #c62828;
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

        .info-box h3 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #1976d2;
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .info-box p, .info-box ul {
            color: #5c442f;
            margin: 5px 0;
        }

        .info-box ul {
            padding-left: 20px;
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
            <h2>Buchungsanfrage abgelehnt</h2>

            <p><?= htmlspecialchars($salutation) ?>,</p>

            <p>leider müssen wir Ihnen mitteilen, dass Ihre Buchungsanfrage vom Anbieter abgelehnt wurde.</p>

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
                        <td>Check-in:</td>
                        <td><?= $check_in ?></td>
                    </tr>
                    <tr>
                        <td>Check-out:</td>
                        <td><?= $check_out ?></td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><span style="color: #c62828; font-weight: 700;">Abgelehnt</span></td>
                    </tr>
                </table>
            </div>

            <?php if (!empty($rejection_reason) && $rejection_reason !== 'Keine Angabe'): ?>
            <!-- Grund der Ablehnung -->
            <div class="reason-box">
                <h3>Grund der Ablehnung</h3>
                <p><?= nl2br(htmlspecialchars($rejection_reason)) ?></p>
            </div>
            <?php endif; ?>

            <!-- Empfehlungen -->
            <div class="info-box">
                <h3>Was nun?</h3>
                <p>Wir empfehlen Ihnen:</p>
                <ul>
                    <li>Suchen Sie nach alternativen Terminen für dieselbe Location</li>
                    <li>Schauen Sie sich andere verfügbare Locations an</li>
                    <li>Kontaktieren Sie uns bei Fragen über unsere Plattform</li>
                </ul>
            </div>

            <p>Es tut uns leid, dass es dieses Mal nicht geklappt hat. Wir hoffen, dass Sie bald eine passende Alternative finden!</p>

            <p style="text-align: center; margin-top: 30px;">
                <a href="<?= $frontend_url ?>/places" class="button">Weitere Locations ansehen</a>
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
