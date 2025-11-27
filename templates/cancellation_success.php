<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stornierung erfolgreich - OttbergenLocations</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Lato:wght@300;400;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lato', 'Helvetica Neue', sans-serif;
            background: linear-gradient(135deg, #e8dfd5 0%, #d4c4b0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            width: 100%;
            background-color: #ffffff;
            box-shadow: 0 10px 30px rgba(61, 40, 23, 0.2);
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background-color: #3d2817;
            color: #f5f0e8;
            padding: 40px 30px;
            text-align: center;
        }

        .header h1 {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 32px;
            margin-bottom: 10px;
            letter-spacing: 0.05em;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .success-icon {
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon svg {
            width: 80px;
            height: 80px;
        }

        .checkmark {
            stroke: #4caf50;
            stroke-width: 2;
            fill: none;
            animation: draw 0.5s ease-out forwards;
        }

        @keyframes draw {
            to {
                stroke-dashoffset: 0;
            }
        }

        .success-icon svg circle {
            stroke: #4caf50;
            stroke-width: 2;
            fill: none;
        }

        .success-icon svg path {
            stroke-dasharray: 50;
            stroke-dashoffset: 50;
        }

        h2 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #3d2817;
            font-size: 28px;
            margin-bottom: 20px;
            text-align: center;
            letter-spacing: 0.05em;
        }

        .message {
            text-align: center;
            font-size: 18px;
            color: #5c442f;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .success-box {
            background-color: #e8f5e9;
            border-left: 5px solid #4caf50;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }

        .success-box p {
            color: #2e7d32;
            font-weight: 600;
            margin: 0;
            font-size: 16px;
        }

        .details-box {
            background-color: #f5f0e8;
            padding: 30px;
            margin: 30px 0;
            border-radius: 8px;
            border: 1px solid #e8dfd5;
        }

        .details-box h3 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #3d2817;
            font-size: 20px;
            margin-bottom: 20px;
            text-align: center;
        }

        .details-box table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-box td {
            padding: 12px 0;
            font-size: 16px;
            border-bottom: 1px solid #e8dfd5;
        }

        .details-box tr:last-child td {
            border-bottom: none;
        }

        .details-box td:first-child {
            font-weight: 600;
            color: #3d2817;
            width: 45%;
        }

        .details-box td:last-child {
            color: #5c442f;
            text-align: right;
        }

        .status-cancelled {
            color: #ff9800 !important;
            font-weight: 700;
        }

        .info-box {
            background-color: #e3f2fd;
            border-left: 5px solid #1976d2;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }

        .info-box p {
            margin: 0;
            color: #0d47a1;
            font-size: 15px;
            line-height: 1.6;
        }

        .button-container {
            text-align: center;
            margin-top: 40px;
        }

        .button {
            display: inline-block;
            padding: 16px 40px;
            background-color: #b8946f;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(61, 40, 23, 0.2);
        }

        .button:hover {
            background-color: #a37d5a;
            box-shadow: 0 6px 8px rgba(61, 40, 23, 0.3);
            transform: translateY(-2px);
        }

        .footer {
            background-color: #f5f0e8;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e8dfd5;
        }

        .footer p {
            color: #5c442f;
            font-size: 14px;
            margin: 8px 0;
        }

        .footer a {
            color: #b8946f;
            text-decoration: none;
            font-weight: 600;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 26px;
            }

            .content {
                padding: 30px 20px;
            }

            h2 {
                font-size: 24px;
            }

            .message {
                font-size: 16px;
            }

            .details-box {
                padding: 20px;
            }

            .details-box td {
                font-size: 14px;
                padding: 10px 0;
            }

            .button {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>OttbergenLocations</h1>
            <p>Ihre Plattform für exklusive Veranstaltungsorte</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Success Icon -->
            <div class="success-icon">
                <svg viewBox="0 0 52 52">
                    <circle cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>

            <h2>Stornierung erfolgreich</h2>

            <div class="success-box">
                <p>✓ Ihre Buchung wurde erfolgreich storniert</p>
            </div>

            <p class="message">
                Wir haben Ihre Buchung storniert und eine Bestätigungs-E-Mail an Sie verschickt.
            </p>

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
                        <td><strong style="font-size: 18px; color: #b8946f;"><?= $total_price ?> €</strong></td>
                    </tr>
                    <tr>
                        <td>Status:</td>
                        <td><span class="status-cancelled">Storniert</span></td>
                    </tr>
                    <tr>
                        <td>Storniert am:</td>
                        <td><?= $cancelled_at ?></td>
                    </tr>
                </table>
            </div>

            <?php if (!empty($refund_info)): ?>
            <!-- Rückerstattungsinformationen -->
            <div class="info-box">
                <p><strong>Rückerstattung:</strong> <?= nl2br(htmlspecialchars($refund_info)) ?></p>
            </div>
            <?php endif; ?>

            <div class="info-box">
                <p>Sie haben eine Bestätigungs-E-Mail an <strong><?= htmlspecialchars($guest_email) ?></strong> erhalten.</p>
            </div>

            <!-- Call-to-Action Button -->
            <div class="button-container">
                <a href="<?= $frontend_url ?>/places" class="button">Weitere Locations entdecken</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Wir hoffen, Sie bald wieder bei uns begrüßen zu dürfen!</strong></p>
            <p style="margin-top: 15px;">Bei Fragen erreichen Sie uns unter:</p>
            <p><a href="mailto:info@ottbergenlocations.de">info@ottbergenlocations.de</a></p>
        </div>
    </div>
</body>
</html>
