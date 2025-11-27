<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buchung ablehnen - OttbergenLocations</title>
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
            padding: 40px 30px;
        }

        h2 {
            font-family: 'Playfair Display', Georgia, serif;
            color: #d32f2f;
            font-size: 28px;
            margin-bottom: 15px;
            text-align: center;
            letter-spacing: 0.05em;
        }

        .subtitle {
            text-align: center;
            color: #5c442f;
            font-size: 16px;
            margin-bottom: 30px;
        }

        .error-alert {
            background-color: #ffebee;
            border-left: 5px solid #d32f2f;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .error-alert p {
            color: #c62828;
            font-weight: 600;
            margin: 0;
        }

        .info-box {
            background-color: #e3f2fd;
            border-left: 5px solid #1976d2;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .info-box p {
            margin: 0;
            color: #0d47a1;
            font-size: 15px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #3d2817;
            font-size: 16px;
        }

        .required {
            color: #d32f2f;
        }

        textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e8dfd5;
            border-radius: 4px;
            font-family: 'Lato', sans-serif;
            font-size: 15px;
            resize: vertical;
            min-height: 150px;
            transition: border-color 0.3s;
        }

        textarea:focus {
            outline: none;
            border-color: #b8946f;
        }

        textarea::placeholder {
            color: #999;
        }

        .char-count {
            text-align: right;
            font-size: 13px;
            color: #999;
            margin-top: 5px;
        }

        .button-container {
            margin-top: 30px;
            text-align: center;
        }

        .button {
            display: inline-block;
            padding: 16px 40px;
            background-color: #d32f2f;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(211, 47, 47, 0.2);
            width: 100%;
        }

        .button:hover {
            background-color: #b71c1c;
            box-shadow: 0 6px 8px rgba(211, 47, 47, 0.3);
            transform: translateY(-2px);
        }

        .button:active {
            transform: translateY(0);
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

            .subtitle {
                font-size: 14px;
            }
        }
    </style>
    <script>
        function updateCharCount() {
            const textarea = document.getElementById('reason');
            const charCount = document.getElementById('char-count');
            const length = textarea.value.length;
            charCount.textContent = length + ' Zeichen';
        }
    </script>
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
            <h2>Buchung ablehnen</h2>
            <p class="subtitle">Bitte geben Sie einen Grund für die Ablehnung an</p>

            <?php if (!empty($error_message)): ?>
            <div class="error-alert">
                <p>⚠️ <?= htmlspecialchars($error_message) ?></p>
            </div>
            <?php endif; ?>

            <div class="info-box">
                <p><strong>💡 Hinweis:</strong> Der Gast wird per E-Mail über die Ablehnung informiert und erhält den von Ihnen angegebenen Grund. Bitte formulieren Sie höflich und nachvollziehbar.</p>
            </div>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="reason">
                        Ablehnungsgrund <span class="required">*</span>
                    </label>
                    <textarea
                        id="reason"
                        name="reason"
                        required
                        placeholder="z.B. Der angefragte Zeitraum ist leider bereits anderweitig vergeben. Gerne können wir Ihnen alternative Termine anbieten..."
                        oninput="updateCharCount()"
                    ><?= htmlspecialchars($previous_reason ?? '') ?></textarea>
                    <div class="char-count" id="char-count">0 Zeichen</div>
                </div>

                <div class="button-container">
                    <button type="submit" class="button">Buchung ablehnen</button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Bei Fragen erreichen Sie uns unter:</p>
            <p><a href="mailto:info@ottbergenlocations.de">info@ottbergenlocations.de</a></p>
        </div>
    </div>

    <script>
        // Initialisiere Zeichenzähler beim Laden
        document.addEventListener('DOMContentLoaded', updateCharCount);
    </script>
</body>
</html>
