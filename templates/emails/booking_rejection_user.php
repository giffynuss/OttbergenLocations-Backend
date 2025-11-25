<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f44336; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; }
        .booking-details { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #8B7355; }
        .detail-row { margin: 10px 0; }
        .label { font-weight: bold; color: #8B7355; }
        .reason-box { background-color: #ffebee; border-left: 4px solid #f44336; padding: 15px; margin: 20px 0; border-radius: 3px; }
        .info-box { background-color: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 3px; }
        .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>❌ Buchungsanfrage abgelehnt</h1>
        </div>

        <div class="content">
            <p>Hallo <?= htmlspecialchars($guest_first_name) ?>,</p>

            <p>Leider müssen wir Ihnen mitteilen, dass Ihre Buchungsanfrage vom Anbieter <strong>abgelehnt</strong> wurde.</p>

            <div class="booking-details">
                <h3 style="margin-top: 0; color: #8B7355;">📅 Betroffene Buchung</h3>
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
                    <span class="label">📋 Buchungsnummer:</span> <code style="background-color: #f0f0f0; padding: 5px 10px; border-radius: 3px;"><?= htmlspecialchars($booking_reference) ?></code>
                </div>
            </div>

            <div class="reason-box">
                <h3 style="margin-top: 0; color: #f44336;">💬 Grund der Ablehnung</h3>
                <p style="margin: 0;"><?= nl2br(htmlspecialchars($rejection_reason)) ?></p>
            </div>

            <div class="info-box">
                <h3 style="margin-top: 0; color: #2196F3;">💡 Was nun?</h3>
                <p style="margin-bottom: 10px;">Wir empfehlen Ihnen:</p>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li>Suchen Sie nach alternativen Terminen für dieselbe Location</li>
                    <li>Schauen Sie sich andere verfügbare Locations in Ottbergen an</li>
                    <li>Kontaktieren Sie uns bei Fragen über unsere Plattform</li>
                </ul>
            </div>

            <p style="margin-top: 25px;">Es tut uns leid, dass es dieses Mal nicht geklappt hat. Wir hoffen, dass Sie bald eine passende Alternative finden!</p>

            <p style="text-align: center; margin-top: 30px;">
                <a href="http://localhost:5173/places" style="display: inline-block; background-color: #8B7355; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">Weitere Locations ansehen</a>
            </p>
        </div>

        <div class="footer">
            <p>Mit freundlichen Grüßen,<br><strong>Ihr Ottbergen Locations Team</strong></p>
            <p style="color: #999; font-size: 11px;">&copy; <?= date('Y') ?> Ottbergen Locations | Diese E-Mail wurde automatisch generiert.</p>
        </div>
    </div>
</body>
</html>
