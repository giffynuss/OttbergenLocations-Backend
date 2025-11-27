# Installation & Setup

Detaillierte Setup-Anleitung für OttbergenLocations Backend.

## 📋 Voraussetzungen

- XAMPP (oder LAMP/WAMP)
- PHP 7.4+
- MySQL 5.7+
- Apache mit mod_rewrite
- Composer (für PHPMailer)

## 🚀 Installation

### Schritt 1: Projekt klonen/herunterladen

```bash
# Nach XAMPP htdocs kopieren
C:\xampp\htdocs\OttbergenLocations-Backend
```

### Schritt 2: Datenbank erstellen

**Option A: Via phpMyAdmin**
1. Öffne http://localhost/phpmyadmin
2. Neue Datenbank "ottbergen_booking" erstellen
3. Importiere `database/schema.sql`
4. Importiere `database/seed.sql` (optional für Test-Daten)

**Option B: Via Kommandozeile**
```bash
# Windows (XAMPP)
"C:\xampp\mysql\bin\mysql.exe" -u root < database/schema.sql
"C:\xampp\mysql\bin\mysql.exe" -u root ottbergen_booking < database/seed.sql

# Linux/Mac
mysql -u root -p < database/schema.sql
mysql -u root -p ottbergen_booking < database/seed.sql
```

### Schritt 3: Composer & Dependencies installieren

**Falls Composer nicht installiert:**
```bash
cd C:\xampp\htdocs\OttbergenLocations-Backend

# Composer herunterladen
curl -sS https://getcomposer.org/installer -o composer-setup.php
"C:\xampp\php\php.exe" composer-setup.php

# Dependencies installieren
"C:\xampp\php\php.exe" composer.phar install

# Cleanup
del composer-setup.php
```

**Falls bereits installiert:**
```bash
php composer.phar install
```

### Schritt 4: Apache mod_rewrite aktivieren

1. Öffne `C:\xampp\apache\conf\httpd.conf`
2. Suche `LoadModule rewrite_module modules/mod_rewrite.so`
3. Entferne `#` am Anfang (falls vorhanden)
4. Suche `AllowOverride None` und ändere zu `AllowOverride All`
5. Apache neu starten

### Schritt 5: Konfiguration anpassen

**Datenbankverbindung** ([config/database.php](config/database.php)):
```php
private $host = "localhost";
private $db_name = "ottbergen_booking";
private $username = "root";
private $password = "";
```

**E-Mail-Konfiguration** ([config/mail.php](config/mail.php)):
```php
'smtp_host' => 'smtp.gmail.com',
'smtp_port' => 587,
'smtp_secure' => 'tls',
'smtp_user' => 'deine-email@gmail.com',
'smtp_pass' => 'dein-app-passwort',  // NICHT normales Passwort!
'from_email' => 'deine-email@gmail.com',
'from_name' => 'Ottbergen Locations',
'base_url' => 'http://localhost/OttbergenLocations-Backend'
```

**⚠️ Wichtig:** Gmail erfordert ein **App-Passwort**, nicht dein normales Gmail-Passwort!
→ Erstellen: https://myaccount.google.com/apppasswords

## ✅ Testen

### 1. API-Basis-Test

```bash
# Places abrufen
curl http://localhost/OttbergenLocations-Backend/api/places/list.php
```

Erwartete Response:
```json
{
  "success": true,
  "places": [...]
}
```

### 2. Login testen

```bash
curl -X POST http://localhost/OttbergenLocations-Backend/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"anna.wagner@example.com","password":"Test123!"}'
```

### 3. Buchung erstellen (testet E-Mail)

```bash
curl -X POST http://localhost/OttbergenLocations-Backend/api/bookings/create.php \
  -H "Content-Type: application/json" \
  -d '{
    "placeId": 1,
    "checkIn": "2025-12-22",
    "checkOut": "2025-12-23",
    "guests": 30,
    "paymentMethod": "cash",
    "userInfo": {
      "gender": "herr",
      "firstName": "Test",
      "lastName": "User",
      "email": "test@example.com",
      "phone": "+49123456789"
    }
  }'
```

**Erwartetes Verhalten:**
- Buchung wird erstellt (Status 201)
- Provider erhält E-Mail mit Bestätigen/Ablehnen-Links
- Error-Log zeigt E-Mail-Debug-Info

### 4. E-Mail-Logs prüfen

**Windows:**
```bash
# Letzte 100 Zeilen
Get-Content "C:\xampp\apache\logs\error.log" -Tail 100

# Live-Monitoring
Get-Content "C:\xampp\apache\logs\error.log" -Wait -Tail 50
```

**Linux/Mac:**
```bash
tail -n 100 /opt/lampp/logs/error.log
tail -f /opt/lampp/logs/error.log  # Live
```

**Erwartete Log-Einträge:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📧 E-MAIL BENACHRICHTIGUNG - Booking #123
✓ PHPMailer gefunden
✓ EmailService geladen
✓✓✓ E-MAIL ERFOLGREICH VERSENDET
```

**Bei Fehlern suche nach:**
- `SMTP Error:` - Authentifizierungsfehler, falsches Passwort
- `Connection refused` - Port blockiert oder SMTP-Server nicht erreichbar
- `Could not authenticate` - App-Passwort erforderlich

## 🔧 E-Mail-Konfiguration

### Gmail App-Passwort erstellen

1. Gehe zu https://myaccount.google.com/apppasswords
2. Wähle "Mail" und "Anderes Gerät"
3. Generiere Passwort
4. Trage es in `config/mail.php` ein

### Outlook konfigurieren

```php
'smtp_host' => 'smtp-mail.outlook.com',
'smtp_port' => 587,
'smtp_user' => 'deine-email@outlook.com',
'smtp_pass' => 'dein-passwort',
```

### Debug-Modus aktivieren/deaktivieren

In [services/EmailService.php](services/EmailService.php):

**Development (Debug aktiviert):**
```php
$this->mailer->SMTPDebug = 2;  // 0=off, 1=client, 2=client+server
$this->mailer->Debugoutput = function($str, $level) {
    error_log("PHPMailer Debug [$level]: $str");
};
```

**Production (Debug deaktiviert):**
```php
$this->mailer->SMTPDebug = 0;  // Keine Debug-Ausgabe
// Debugoutput-Zeilen entfernen oder auskommentieren
```

## 🐛 Troubleshooting

### Problem: 404 bei API-Aufrufen

**Ursache:** mod_rewrite nicht aktiviert oder .htaccess fehlt

**Lösung:**
1. Prüfe ob mod_rewrite aktiviert ist (siehe Schritt 4)
2. Prüfe ob `.htaccess` existiert in:
   - `api/places/.htaccess`
   - `api/bookings/.htaccess`
   - `api/auth/.htaccess`
3. Apache neu starten

### Problem: "Database connection error"

**Ursache:** MySQL nicht erreichbar oder falsche Credentials

**Lösung:**
1. Prüfe ob MySQL läuft (XAMPP Control Panel)
2. Prüfe Credentials in `config/database.php`
3. Prüfe ob Datenbank `ottbergen_booking` existiert:
   ```sql
   SHOW DATABASES LIKE 'ottbergen_booking';
   ```

### Problem: E-Mails kommen nicht an

**Ursache 1: Falsches Gmail-Passwort**
- **Symptom:** `SMTP Error: Could not authenticate`
- **Lösung:** App-Passwort erstellen (nicht normales Passwort!)
  1. Gehe zu https://myaccount.google.com/apppasswords
  2. Wähle "Mail" und "Anderes Gerät"
  3. Kopiere das generierte Passwort (16 Zeichen ohne Leerzeichen)
  4. Trage es in `config/mail.php` ein

**Ursache 2: SMTP-Port blockiert**
- **Symptom:** `Connection refused` oder `Connection timed out`
- **Lösung:** Versuche Port 465 mit SSL:
  ```php
  'smtp_port' => 465,
  'smtp_secure' => 'ssl',
  ```

**Ursache 3: Firewall blockiert ausgehende Verbindungen**
- **Lösung:** Erlaube ausgehende Verbindungen für XAMPP
  - Windows Firewall: Port 587 (TLS) oder 465 (SSL) freigeben
  - Antivirus: XAMPP/PHP als Ausnahme hinzufügen

**Ursache 4: PHPMailer nicht installiert**
- **Symptom:** `vendor/autoload.php not found` im Error-Log
- **Lösung:** `php composer.phar install` ausführen

**Debug-Schritte:**
```bash
# 1. Error-Log prüfen (Windows PowerShell)
Get-Content "C:\xampp\apache\logs\error.log" -Tail 50 | Select-String "E-MAIL|SMTP|PHPMailer"

# 2. Error-Log prüfen (Linux/Mac)
tail -n 50 /opt/lampp/logs/error.log | grep -E "E-MAIL|SMTP|PHPMailer"

# 3. SMTP-Verbindung testen (wenn telnet installiert)
telnet smtp.gmail.com 587
```

**Erwartete Ausgabe bei Erfolg:**
```
✓ PHPMailer gefunden
✓ EmailService geladen
=== EMAIL CONFIG ===
SMTP Host: smtp.gmail.com
SMTP Port: 587
✓✓✓ E-MAIL ERFOLGREICH VERSENDET
```

### Problem: "CORS-Fehler" im Frontend

**Ursache:** Frontend-Domain nicht erlaubt

**Lösung:** CORS-Header in API-Dateien anpassen:
```php
// Aktuell (Development):
header("Access-Control-Allow-Origin: http://localhost:5173");

// Für andere Domains:
header("Access-Control-Allow-Origin: https://deine-domain.de");
```

### Problem: Session funktioniert nicht

**Ursache:** Cookies werden nicht gesetzt

**Lösung:**
1. Prüfe ob Cookies im Browser aktiviert sind
2. Prüfe `credentials: 'include'` im Frontend
3. Prüfe ob `session_start()` in PHP läuft
4. Lösche Browser-Cache und Cookies

### Problem: Token-Links in E-Mails funktionieren nicht

**Symptom:** Klick auf "Bestätigen"/"Ablehnen" führt zu 404 oder falscher URL

**Ursache:** BASE_URL falsch konfiguriert

**Lösung:** Prüfe [config/mail.php](config/mail.php):
```php
'base_url' => 'http://localhost/OttbergenLocations-Backend',
// WICHTIG: Ohne trailing slash!
```

**Bei Production:**
```php
'base_url' => 'https://deine-domain.de/api',
```

## 📊 Datenbankstruktur

### Wichtige Tabellen:

- **users** - Benutzer (Kunden & Provider)
- **places** - Orte/Locations
- **place_images** - Bilder für Orte
- **place_features** - Ausstattung/Features
- **bookings** - Buchungen (mit `confirmation_token`)
- **booking_guest_info** - Gast-Kontaktdaten

### Felder prüfen:

```sql
-- Prüfe Booking-Tabelle
DESCRIBE ottbergen_booking.bookings;

-- Wichtige Felder:
-- confirmation_token | varchar(64) | YES | MUL | NULL
-- status | enum(...,'rejected') | NO | | pending

-- Teste Datenbank-Verbindung
SELECT COUNT(*) as place_count FROM places;
SELECT COUNT(*) as user_count FROM users WHERE is_provider = 1;
```

## 🔒 Sicherheit

### Implementierte Maßnahmen:

✅ SQL Injection Schutz (Prepared Statements)
✅ Session-basierte Authentifizierung
✅ Input-Validierung (alle Endpoints)
✅ Autorisierung (nur eigene Daten bearbeiten)
✅ Password-Hashing (SHA256 + Salt)
✅ CORS-Schutz
✅ XSS-Schutz (HTML-Escaping in Templates)

### Production-Checkliste:

- [ ] **HTTPS aktivieren** (SSL-Zertifikat)
- [ ] **Error Reporting deaktivieren**
  ```php
  ini_set('display_errors', 0);
  error_reporting(0);
  ```
- [ ] **CORS auf Frontend-Domain beschränken**
  ```php
  header("Access-Control-Allow-Origin: https://deine-domain.de");
  ```
- [ ] **DB-Credentials sichern** (ENV-Variablen oder außerhalb von htdocs)
- [ ] **Rate Limiting** implementieren (z.B. mit PHP-RateLimit)
- [ ] **BASE_URL anpassen** in `config/mail.php`
- [ ] **SMTP-Debug deaktivieren** (`SMTPDebug = 0`)
- [ ] **Error-Logs regelmäßig prüfen** und rotieren
- [ ] **Backup-Strategie** für Datenbank etablieren
- [ ] **PHP-Version aktualisieren** (mind. 8.0 empfohlen)

## 📞 Support & Debugging

### Error-Logs

**Windows (PowerShell):**
```powershell
# Live-Monitoring
Get-Content "C:\xampp\apache\logs\error.log" -Wait -Tail 50

# Letzte 100 Zeilen
Get-Content "C:\xampp\apache\logs\error.log" -Tail 100

# Nach Keyword filtern
Get-Content "C:\xampp\apache\logs\error.log" -Tail 200 | Select-String "SMTP|E-MAIL|Fatal"
```

**Linux/Mac:**
```bash
# Live-Monitoring
tail -f /opt/lampp/logs/error.log

# Nach Keyword filtern
tail -n 200 /opt/lampp/logs/error.log | grep -E "SMTP|E-MAIL|Fatal"
```

### Nützliche SQL-Queries

```sql
-- Alle Buchungen mit Token
SELECT booking_id, status, confirmation_token
FROM bookings
WHERE confirmation_token IS NOT NULL;

-- Provider-E-Mails
SELECT user_id, first_name, last_name, email
FROM users
WHERE is_provider = 1;

-- Letzte 10 Buchungen
SELECT * FROM bookings
ORDER BY booking_id DESC
LIMIT 10;
```

### API-Tests mit curl

```bash
# Places abrufen
curl http://localhost/OttbergenLocations-Backend/api/places/list.php

# Verfügbarkeit prüfen
curl "http://localhost/OttbergenLocations-Backend/api/places/availability.php?id=1&checkIn=2025-12-15&checkOut=2025-12-20"

# Login testen
curl -X POST http://localhost/OttbergenLocations-Backend/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"anna.wagner@example.com","password":"Test123!"}'

# Buchung erstellen (testet auch E-Mail-System)
curl -X POST http://localhost/OttbergenLocations-Backend/api/bookings/create.php \
  -H "Content-Type: application/json" \
  -d '{
    "placeId": 1,
    "checkIn": "2025-12-22",
    "checkOut": "2025-12-23",
    "guests": 30,
    "paymentMethod": "cash",
    "userInfo": {
      "gender": "herr",
      "firstName": "Test",
      "lastName": "User",
      "email": "test@example.com",
      "phone": "+49123456789"
    }
  }'
```

### Häufige Fehlermeldungen

| Error | Ursache | Lösung |
|-------|---------|--------|
| `SyntaxError: Unexpected token '<'` | PHP-Fehler als HTML statt JSON | `display_errors = 0` setzen |
| `Column not found: confirmation_token` | Datenbank-Schema veraltet | Migration ausführen |
| `vendor/autoload.php not found` | Composer nicht installiert | `composer install` |
| `SMTP Error: Could not authenticate` | Falsches Passwort | Gmail App-Passwort verwenden |
| `Access to fetch has been blocked by CORS` | CORS nicht konfiguriert | CORS-Header prüfen |
| `Session not found` | Cookies nicht gesendet | `credentials: 'include'` im Fetch |

## 📚 Weitere Dokumentation

- **[README.md](README.md)** - Projekt-Übersicht & Schnellstart
- **[API_OVERVIEW.md](API_OVERVIEW.md)** - Vollständige API-Dokumentation

## 🆘 Support

Bei Problemen:
1. Prüfe Error-Logs (siehe oben)
2. Teste API-Endpoints mit curl
3. Prüfe Datenbank-Schema mit SQL-Queries
4. Checke PHP/MySQL-Versionen
5. Verifiziere Konfiguration in `config/`

---

**Version:** 1.4 (2025-11-27)
**Letzte Aktualisierung:** Security Audit durchgeführt, Dokumentation aktualisiert

## 🔒 Security

Das Backend wurde einem umfassenden SQL Injection Security Audit unterzogen:
- ✅ **42 Tests durchgeführt** - Alle Endpoints getestet
- ✅ **0 Vulnerabilities gefunden** - 100% Prepared Statements
- ✅ **False Positives geklärt** - Input Sanitization funktioniert korrekt
- 📄 **Vollständiger Report:** [SECURITY_AUDIT_REPORT.md](SECURITY_AUDIT_REPORT.md)
