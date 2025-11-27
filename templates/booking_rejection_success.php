<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buchung abgelehnt - OttbergenLocations</title>
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

        .info-icon {
            text-align: center;
            margin-bottom: 30px;
        }

        .info-icon svg {
            width: 80px;
            height: 80px;
        }

        h2 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #5c442f;
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

        .status-box {
            background-color: #fff3cd;
            border-left: 5px solid #ff9800;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
        }

        .status-box p {
            color: #856404;
            font-weight: 600;
            margin: 0;
            font-size: 16px;
        }

        .booking-reference {
            background-color: #f5f0e8;
            border: 2px solid #e8dfd5;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
            text-align: center;
        }

        .booking-reference .label {
            font-size: 14px;
            color: #5c442f;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .booking-reference .number {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: 700;
            color: #3d2817;
            letter-spacing: 2px;
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

            .booking-reference .number {
                font-size: 18px;
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
            <!-- Info Icon -->
            <div class="info-icon">
                <svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="26" cy="26" r="25" stroke="#ff9800" stroke-width="2"/>
                    <path d="M26 16v12M26 32v2" stroke="#ff9800" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>

            <h2>Buchung abgelehnt</h2>

            <div class="status-box">
                <p>Die Buchung wurde abgelehnt</p>
            </div>

            <p class="message">
                Der Gast wurde per E-Mail über die Ablehnung benachrichtigt und hat Ihren angegebenen Grund erhalten.
            </p>

            <?php if (!empty($booking_reference)): ?>
            <!-- Buchungsreferenz -->
            <div class="booking-reference">
                <div class="label">Buchungsnummer</div>
                <div class="number"><?= htmlspecialchars($booking_reference) ?></div>
            </div>
            <?php endif; ?>

            <!-- Informationen -->
            <div class="info-box">
                <p><strong>Was passiert jetzt?</strong><br>
                Der Gast kann bei Bedarf eine neue Buchungsanfrage für andere Termine stellen. Die abgelehnte Buchung wird in Ihrem Dashboard als "Abgelehnt" markiert.</p>
            </div>

            <!-- Call-to-Action Button -->
            <div class="button-container">
                <a href="<?= $frontend_url ?>/dashboard" class="button">Zum Dashboard</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Vielen Dank für die Nutzung von OttbergenLocations!</strong></p>
            <p style="margin-top: 15px;">Bei Fragen erreichen Sie uns unter:</p>
            <p><a href="mailto:info@ottbergenlocations.de">info@ottbergenlocations.de</a></p>
        </div>
    </div>
</body>
</html>
