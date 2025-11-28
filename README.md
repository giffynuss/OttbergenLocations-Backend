# OttbergenLocations Backend

PHP/MySQL Backend für die OttbergenLocations Buchungsplattform mit E-Mail-Benachrichtigungen.

## ⚡ Schnellstart

### 1. Datenbank einrichten

```bash
# Schema erstellen
mysql -u root -p < database/schema.sql

# Test-Daten einfügen (optional)
mysql -u root -p ottbergen_booking < database/seed.sql
```

### 2. Dependencies installieren

```bash
cd C:\xampp\htdocs\OttbergenLocations-Backend
php composer.phar install
```

### 3. Test-Accounts (nach Seed-Import)

Alle Test-Accounts haben das Passwort: **Test123!**

| Email | Rolle | Orte |
|-------|-------|------|
| max.mustermann@example.com | Provider | 2 |
| maria.schmidt@example.com | Provider | 1 |
| anna.wagner@example.com | Kunde | - |

### 4. API testen

```bash
# Places abrufen
curl http://localhost/OttbergenLocations-Backend/api/places/list.php

# Login
curl -X POST http://localhost/OttbergenLocations-Backend/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"anna.wagner@example.com","password":"Test123!"}'
```

## 📚 Dokumentation

- **[API_OVERVIEW.md](API_OVERVIEW.md)** - Vollständige API-Dokumentation
- **[INSTALLATION.md](INSTALLATION.md)** - Detaillierte Setup-Anleitung & Troubleshooting

## 🏗️ Projektstruktur

```
OttbergenLocations-Backend/
├── api/
│   ├── auth/              # Login, Register
│   ├── places/            # Orte (CRUD + Suche)
│   ├── bookings/          # Buchungen + E-Mail-Tokens
│   └── user/              # User-Verwaltung
├── config/
│   ├── database.php       # DB-Verbindung
│   └── mail.php          # E-Mail-Konfiguration (Gmail/Outlook)
├── services/
│   └── EmailService.php   # E-Mail-Versand
├── templates/emails/      # E-Mail-Templates
├── helpers/               # Validierung, Auth, Pricing
├── database/
│   ├── schema.sql        # DB-Schema
│   └── seed.sql          # Test-Daten
└── vendor/               # Composer Dependencies (PHPMailer)
```

## ✨ Features

### ✅ Implementiert
- **Auth-System** - Session-basiert, Provider-Registrierung
- **Places Management** - CRUD für Orte (nur Provider)
- **Booking-System** - Gast-Buchungen möglich (ohne Login)
- **E-Mail-Benachrichtigungen** - Bestätigung/Ablehnung per Token-Link
- **Verfügbarkeitsprüfung** - Automatische Kollisionserkennung
- **Zahlungsmethoden** - Cash, PayPal, Überweisung, Wero
- **Security** - ✅ SQL Injection Audit bestanden (42 Tests, 0 Vulnerabilities)
  - 100% PDO Prepared Statements
  - Input Validierung & Sanitization
  - Authorization & Authentication
  - Security Report: [SECURITY_AUDIT_REPORT.md](SECURITY_AUDIT_REPORT.md)

### 📧 E-Mail-System
- Provider erhält E-Mail bei neuer Buchung
- Bestätigen/Ablehnen per Link (ohne Login)
- Automatische Benachrichtigungen an Gast
- Unterstützt Gmail & Outlook SMTP
- Konfiguration: [config/mail.php](config/mail.php)

## 🔌 Wichtige Endpoints

| Methode | Endpoint | Beschreibung | Auth |
|---------|----------|--------------|------|
| GET | `/api/places/list.php` | Orte mit Filtern | - |
| GET | `/api/places/get.php?id={id}` | Ort-Details | - |
| POST | `/api/auth/register.php` | Registrierung | - |
| POST | `/api/auth/login.php` | Login | - |
| POST | `/api/bookings/create.php` | Buchung erstellen | - |
| GET | `/api/bookings/confirm-token.php?token={token}` | Bestätigung per E-Mail | - |
| GET | `/api/user/me.php` | Aktueller User | ✓ |
| POST | `/api/places/create.php` | Ort erstellen | ✓ Provider |
| PATCH | `/api/bookings/confirm.php?id={id}` | Buchung bestätigen | ✓ Provider |

Vollständige API-Doku: [API_OVERVIEW.md](API_OVERVIEW.md)

## 🔧 Konfiguration

### Datenbank

[config/database.php](config/database.php):
```php
private $host = "localhost";
private $db_name = "ottbergen_booking";
private $username = "root";
private $password = "";
```

### E-Mail (Gmail)

[config/mail.php](config/mail.php):
```php
'smtp_host' => 'smtp.gmail.com',
'smtp_user' => 'your-email@gmail.com',
'smtp_pass' => 'app-passwort',  // https://myaccount.google.com/apppasswords
```

### CORS (Frontend)

Aktuell konfiguriert für: `http://localhost:5173`

Für Production in allen API-Dateien anpassen:
```php
header("Access-Control-Allow-Origin: https://deine-domain.de");
```

## 🐛 Troubleshooting

**404 bei API-Calls**
→ Apache mod_rewrite aktivieren, .htaccess-Dateien prüfen

**Database connection error**
→ MySQL läuft? Credentials in `config/database.php` korrekt?

**E-Mails kommen nicht an**
→ Gmail App-Passwort erstellen (nicht normales Passwort!)
→ Debug-Logs prüfen: `C:\xampp\apache\logs\error.log`

**Session-Fehler**
→ Cookies im Browser aktiviert? CORS korrekt konfiguriert?

Detaillierte Hilfe: [INSTALLATION.md](INSTALLATION.md)

## 📊 Technologie-Stack

- **PHP** 7.4+ mit PDO
- **MySQL** 5.7+ mit InnoDB
- **Apache** mit mod_rewrite
- **PHPMailer** 6.9+ für E-Mail-Versand
- **Session-Auth** (Cookie-basiert)

## 🚀 Deployment-Hinweise

1. ✅ HTTPS verwenden
2. ✅ Error Reporting deaktivieren (`ini_set('display_errors', 0)`)
3. ✅ CORS auf Frontend-Domain beschränken
4. ✅ DB-Credentials als Umgebungsvariablen
5. ✅ Rate Limiting implementieren
6. ✅ BASE_URL in `config/mail.php` auf Production-Domain setzen

## 📝 Version

**v1.5** (2025-11-28)
- **Verfügbarkeitsfilter für Places optimiert** ✅
- `checkIn` & `checkOut` Parameter in `/places/list.php` korrekt implementiert
- Performance-Optimierung durch SQL-basierte Filterung

**v1.4** (2025-11-27)
- **SQL Injection Security Audit** durchgeführt (42 Tests, 0 Vulnerabilities) ✅
- Security Report: [SECURITY_AUDIT_REPORT.md](SECURITY_AUDIT_REPORT.md)
- Dokumentation vollständig aktualisiert

**v1.3** (2025-11-27)
- Frontend-Kompatibilität verbessert
- CORS-Header optimiert

**v1.2** (2025-11-25)
- E-Mail-Benachrichtigungen mit Token-Links
- Status 'rejected' für Buchungen
- Composer & PHPMailer Integration

**v1.1** - Gast-Buchungen & Checkout-System
**v1.0** - Basis-APIs (Places, Bookings, Auth)

---

**Base URL:** `http://localhost/OttbergenLocations-Backend`
**Frontend:** `http://localhost:5173` (konfiguriert für CORS)
