<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #8B7355; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .booking-details { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #8B7355; }
        .detail-row { margin: 10px 0; }
        .label { font-weight: bold; color: #8B7355; }
        .buttons { text-align: center; margin: 30px 0; }
        .btn { display: inline-block; padding: 12px 30px; margin: 10px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .btn-confirm { background-color: #4CAF50; color: white; }
        .btn-reject { background-color: #f44336; color: white; }
        .btn:hover { opacity: 0.9; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
        .info-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 15px 0; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📬 Neue Buchungsanfrage</h1>
        </div>

        <div class="content">
            <p>Hallo <?= htmlspecialchars($provider_name) ?>,</p>

            <p>Sie haben eine neue Buchungsanfrage für <strong><?= htmlspecialchars($place_name) ?></strong> erhalten!</p>

            <div class="booking-details">
                <h3 style="margin-top: 0; color: #8B7355;">📅 Buchungsdetails</h3>
                <div class="detail-row">
                    <span class="label">📍 Ort:</span> <?= htmlspecialchars($place_name) ?>
                </div>
                <div class="detail-row">
                    <span class="label">📅 Check-in:</span> <?= $check_in ?>
                </div>
                <div class="detail-row">
                    <span class="label">📅 Check-out:</span> <?= $check_out ?>
                </div>
                <div class="detail-row">
                    <span class="label">👥 Anzahl Gäste:</span> <?= $guests ?>
                </div>
                <div class="detail-row">
                    <span class="label">💰 Gesamtpreis:</span> <strong style="color: #4CAF50; font-size: 18px;"><?= $total_price ?> €</strong>
                </div>
                <div class="detail-row">
                    <span class="label">💳 Zahlungsmethode:</span> <?= htmlspecialchars($payment_method) ?>
                </div>
            </div>

            <div class="booking-details">
                <h3 style="margin-top: 0; color: #8B7355;">👤 Gast-Informationen</h3>
                <div class="detail-row">
                    <span class="label">Name:</span> <?= htmlspecialchars($guest_name) ?>
                </div>
                <div class="detail-row">
                    <span class="label">E-Mail:</span> <a href="mailto:<?= htmlspecialchars($guest_email) ?>"><?= htmlspecialchars($guest_email) ?></a>
                </div>
                <div class="detail-row">
                    <span class="label">Telefon:</span> <a href="tel:<?= htmlspecialchars($guest_phone) ?>"><?= htmlspecialchars($guest_phone) ?></a>
                </div>
                <div class="detail-row">
                    <span class="label">Adresse:</span> <?= htmlspecialchars($guest_address) ?>
                </div>
            </div>

            <p><strong>📋 Buchungsnummer:</strong> <code style="background-color: #f0f0f0; padding: 5px 10px; border-radius: 3px;"><?= htmlspecialchars($booking_reference) ?></code></p>

            <div class="buttons">
                <a href="<?= $confirm_link ?>" class="btn btn-confirm">✓ Buchung bestätigen</a>
                <a href="<?= $reject_link ?>" class="btn btn-reject">✗ Buchung ablehnen</a>
            </div>

            <div class="info-box">
                <strong>⏰ Hinweis:</strong> Bitte reagieren Sie zeitnah auf diese Buchungsanfrage. Der Gast wartet auf Ihre Rückmeldung.
            </div>
        </div>

        <div class="footer">
            <p>Mit freundlichen Grüßen,<br><strong>Ihr Ottbergen Locations Team</strong></p>
            <p style="color: #999; font-size: 11px;">&copy; <?= date('Y') ?> Ottbergen Locations | Diese E-Mail wurde automatisch generiert.</p>
        </div>
    </div>
</body>
</html>
