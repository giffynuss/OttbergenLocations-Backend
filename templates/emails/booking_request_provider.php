<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neue Buchungsanfrage - OttbergenLocations</title>
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

        .details-box h3 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #3d2817;
            font-size: 18px;
            margin-top: 0;
            margin-bottom: 15px;
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

        .button-container {
            text-align: center;
            margin: 30px 0;
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
            margin: 10px 10px 10px 0;
            box-shadow: 0 4px 6px rgba(61, 40, 23, 0.15);
        }

        .button-secondary {
            background-color: #8b6f47;
        }

        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #b8946f;
            padding: 20px;
            margin: 25px 0;
        }

        .info-box p {
            margin: 0;
            color: #856404;
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
                margin: 10px 0;
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
            <h2>Neue Buchungsanfrage</h2>

            <p><?= htmlspecialchars($salutation) ?>,</p>

            <p>Sie haben eine neue Buchungsanfrage für <strong><?= htmlspecialchars($place_name) ?></strong> erhalten!</p>

            <!-- Buchungsdetails -->
            <div class="details-box">
                <h3>Buchungsdetails</h3>
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
                </table>
            </div>

            <!-- Gast-Informationen -->
            <div class="details-box">
                <h3>Gast-Informationen</h3>
                <table>
                    <tr>
                        <td>Name:</td>
                        <td><?= htmlspecialchars($guest_name) ?></td>
                    </tr>
                    <tr>
                        <td>E-Mail:</td>
                        <td><a href="mailto:<?= htmlspecialchars($guest_email) ?>" style="color: #5c442f;"><?= htmlspecialchars($guest_email) ?></a></td>
                    </tr>
                    <tr>
                        <td>Telefon:</td>
                        <td><a href="tel:<?= htmlspecialchars($guest_phone) ?>" style="color: #5c442f;"><?= htmlspecialchars($guest_phone) ?></a></td>
                    </tr>
                    <?php if (!empty($guest_address) && $guest_address !== 'Keine Adresse angegeben'): ?>
                    <tr>
                        <td>Adresse:</td>
                        <td><?= htmlspecialchars($guest_address) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- Call-to-Action Buttons -->
            <div class="button-container">
                <a href="<?= $confirm_link ?>" class="button">&#10004; Buchung bestätigen</a>
                <a href="<?= $reject_link ?>" class="button button-secondary">&#10008; Buchung ablehnen</a>
            </div>

            <div class="info-box">
                <p><strong>Hinweis:</strong> Sie können die Buchung auch in Ihrem Dashboard verwalten. Bitte reagieren Sie zeitnah auf diese Anfrage, der Gast wartet auf Ihre Rückmeldung.</p>
            </div>

            <div class="divider"></div>

            <p>Bei Fragen können Sie sich gerne an uns wenden.</p>
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
