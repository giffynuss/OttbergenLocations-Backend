<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($error_title ?? 'Fehler') ?> - OttbergenLocations</title>
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
            max-width: 600px;
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
            padding: 50px 30px;
            text-align: center;
        }

        .error-icon {
            margin-bottom: 30px;
        }

        .error-icon svg {
            width: 80px;
            height: 80px;
        }

        h2 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #d32f2f;
            font-size: 28px;
            margin-bottom: 20px;
            letter-spacing: 0.05em;
        }

        .error-message {
            font-size: 18px;
            color: #5c442f;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-box {
            background-color: #ffebee;
            border-left: 5px solid #d32f2f;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
            text-align: left;
        }

        .error-box p {
            color: #c62828;
            margin: 0;
            font-size: 15px;
        }

        .button-container {
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
            margin: 5px;
        }

        .button:hover {
            background-color: #a37d5a;
            box-shadow: 0 6px 8px rgba(61, 40, 23, 0.3);
            transform: translateY(-2px);
        }

        .button-secondary {
            background-color: #3d2817;
        }

        .button-secondary:hover {
            background-color: #2a1a0f;
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
                padding: 40px 20px;
            }

            h2 {
                font-size: 24px;
            }

            .error-message {
                font-size: 16px;
            }

            .button {
                display: block;
                width: 100%;
                margin: 10px 0;
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
            <!-- Error Icon -->
            <div class="error-icon">
                <svg viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="26" cy="26" r="25" stroke="#d32f2f" stroke-width="2"/>
                    <path d="M26 16v12M26 32v2" stroke="#d32f2f" stroke-width="3" stroke-linecap="round"/>
                </svg>
            </div>

            <h2><?= htmlspecialchars($error_title ?? 'Ein Fehler ist aufgetreten') ?></h2>

            <?php if (!empty($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>

            <?php if (!empty($error_details)): ?>
            <div class="error-box">
                <p><strong>Details:</strong> <?= htmlspecialchars($error_details) ?></p>
            </div>
            <?php endif; ?>

            <!-- Call-to-Action Buttons -->
            <div class="button-container">
                <?php if (!empty($retry_url)): ?>
                <a href="<?= htmlspecialchars($retry_url) ?>" class="button">Erneut versuchen</a>
                <?php endif; ?>
                <a href="<?= $frontend_url ?>/search" class="button button-secondary">Zur Startseite</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Bei Fragen oder Problemen erreichen Sie uns unter:</p>
            <p><a href="mailto:info@ottbergenlocations.de">info@ottbergenlocations.de</a></p>
        </div>
    </div>
</body>
</html>
