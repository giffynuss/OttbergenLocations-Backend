<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .booking-details { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #4CAF50; }
        .detail-row { margin: 10px 0; }
        .label { font-weight: bold; color: #8B7355; }
        .highlight-box { background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 3px; }
        .success-icon { font-size: 48px; text-align: center; margin: 20px 0; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="success-icon">🎉</div>
            <h1>Buchung bestätigt!</h1>
        </div>

        <div class="content">
            <p>Hallo <?= htmlspecialchars($guest_first_name) ?>,</p>

            <p>Großartige Neuigkeiten! Ihre Buchung wurde vom Anbieter <strong>bestätigt</strong>. Wir freuen uns auf Ihren Besuch!</p>

            <div class="booking-details">
                <h3 style="margin-top: 0; color: #4CAF50;">📋 Ihre Buchungsdetails</h3>
                <div class="detail-row">
                    <span class="label">📍 Ort:</span> <?= htmlspecialchars($place_name) ?>
                </div>
                <div class="detail-row">
                    <span class="label">📌 Standort:</span> <?= htmlspecialchars($place_location) ?>
                </div>
                <div class="detail-row">
                    <span class="label">📅 Check-in:</span> <?= $check_in ?>
                </div>
                <div class="detail-row">
                    <span class="label">📅 Check-out:</span> <?= $check_out ?>
                </div>
                <div class="detail-row">
                    <span class="label">👥 Gäste:</span> <?= $guests ?>
                </div>
                <div class="detail-row">
                    <span class="label">💰 Gesamtpreis:</span> <strong style="font-size: 20px; color: #4CAF50;"><?= $total_price ?> €</strong>
                </div>
                <div class="detail-row">
                    <span class="label">📋 Buchungsnummer:</span> <code style="background-color: #f0f0f0; padding: 5px 10px; border-radius: 3px;"><?= htmlspecialchars($booking_reference) ?></code>
                </div>
            </div>

            <?php if ($payment_method === 'Überweisung'): ?>
            <div class="highlight-box">
                <h3 style="margin-top: 0;">💳 Zahlungsinformationen</h3>
                <p>Bitte überweisen Sie den Betrag von <strong><?= $total_price ?> €</strong> an folgendes Konto:</p>
                <?= $bank_details ?>
                <p style="margin-top: 15px;">
                    <strong>Verwendungszweck:</strong> <code style="background-color: #fff; padding: 3px 8px; border-radius: 3px; border: 1px solid #ddd;"><?= htmlspecialchars($booking_reference) ?></code>
                </p>
                <p style="font-size: 12px; color: #856404; margin-top: 15px;">
                    ⚠️ <strong>Wichtig:</strong> Bitte überweisen Sie den Betrag bis spätestens <strong>7 Tage vor Check-in</strong> und verwenden Sie unbedingt die Buchungsnummer als Verwendungszweck.
                </p>
            </div>
            <?php endif; ?>

            <div class="booking-details">
                <h3 style="margin-top: 0; color: #8B7355;">📞 Kontakt zum Anbieter</h3>
                <div class="detail-row">
                    <span class="label">Name:</span> <?= htmlspecialchars($provider_name) ?>
                </div>
                <div class="detail-row">
                    <span class="label">Telefon:</span> <a href="tel:<?= htmlspecialchars($provider_phone) ?>"><?= htmlspecialchars($provider_phone) ?></a>
                </div>
                <div class="detail-row">
                    <span class="label">E-Mail:</span> <a href="mailto:<?= htmlspecialchars($provider_email) ?>"><?= htmlspecialchars($provider_email) ?></a>
                </div>
            </div>

            <p style="margin-top: 25px;">Bei Fragen oder Anliegen wenden Sie sich bitte direkt an den Anbieter.</p>

            <p style="margin-top: 30px; font-size: 18px; text-align: center; color: #4CAF50;">
                <strong>Wir wünschen Ihnen einen wunderbaren Aufenthalt! 🌟</strong>
            </p>
        </div>

        <div class="footer">
            <p>Mit freundlichen Grüßen,<br><strong>Ihr Ottbergen Locations Team</strong></p>
            <p style="color: #999; font-size: 11px;">&copy; <?= date('Y') ?> Ottbergen Locations | Diese E-Mail wurde automatisch generiert.</p>
        </div>
    </div>
</body>
</html>
