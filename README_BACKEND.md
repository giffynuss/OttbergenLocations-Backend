# OttbergenLocations Backend

PHP/MySQL Backend für die OttbergenLocations Buchungsplattform.

## Schnellstart

### 1. Installation

```bash
# Datenbank erstellen
mysql -u root -p < database/schema.sql

# Test-Daten einfügen (optional)
mysql -u root -p ottbergen_booking < database/seed.sql
```

### 2. Test-Accounts (nach Seed-Import)

Alle Test-Accounts haben das Passwort: **Test123!**

| Email | Rolle | Beschreibung |
|-------|-------|--------------|
| max.mustermann@example.com | Provider | Hat 2 Orte |
| maria.schmidt@example.com | Provider | Hat 1 Ort |
| hans.mueller@example.com | Provider | Hat 1 Ort |
| anna.wagner@example.com | Kunde | Hat 2 Buchungen |
| thomas.klein@example.com | Kunde | Hat 1 Buchung |

### 3. API testen

```bash
# Places abrufen
curl http://localhost/OttbergenLocations-Backend/api/places

# Login
curl -X POST http://localhost/OttbergenLocations-Backend/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"anna.wagner@example.com","password":"Test123!"}'
```

## Dokumentation

- **[SETUP.md](SETUP.md)** - Vollständige Setup-Anleitung
- **[API_OVERVIEW.md](API_OVERVIEW.md)** - Detaillierte API-Dokumentation
- **[BACKEND_REQUIREMENTS.md](BACKEND_REQUIREMENTS.md)** - Anforderungen & Spezifikation

## Struktur

```
OttbergenLocations-Backend/
├── api/
│   ├── places/          # Orte-Endpoints
│   ├── bookings/        # Buchungs-Endpoints
│   └── providers/       # Provider-Endpoints
├── config/
│   └── database.php     # DB-Konfiguration
├── helpers/
│   ├── auth.php         # Authentifizierung
│   ├── validation.php   # Validierungen
│   └── pricing.php      # Preisberechnung
├── database/
│   ├── schema.sql       # Datenbank-Schema
│   └── seed.sql         # Test-Daten
├── login.php            # Login-Endpoint
├── register.php         # Registrierung
└── me.php               # Aktueller User
```

## Wichtige API-Endpoints

### Öffentlich (ohne Login)
- `GET /api/places` - Liste aller Orte
- `GET /api/places/{id}` - Ort-Details
- `GET /api/places/{id}/availability` - Verfügbarkeit prüfen
- `POST /login.php` - Login
- `POST /register.php` - Registrierung

### Geschützt (Login erforderlich)
- `POST /api/bookings` - Buchung erstellen
- `GET /api/bookings` - Eigene Buchungen
- `GET /api/bookings/{id}` - Buchungsdetails
- `PATCH /api/bookings/{id}/cancel` - Buchung stornieren
- `PATCH /api/bookings/{id}/confirm` - Buchung bestätigen (nur Provider)

## Features

✅ Session-basierte Authentifizierung
✅ SQL Injection Schutz (Prepared Statements)
✅ Automatische Preisberechnung
✅ Verfügbarkeitsprüfung
✅ Status-Management für Buchungen
✅ CORS-Support für Frontend
✅ Saubere URL-Struktur via .htaccess
✅ Umfassende Validierung

## Entwicklung

### Datenbankverbindung anpassen

Bearbeite [config/database.php](config/database.php):

```php
private $host = "localhost";
private $db_name = "ottbergen_booking";
private $username = "root";
private $password = "";
```

### CORS für Production anpassen

In den API-Dateien `Access-Control-Allow-Origin` ändern:

```php
// Development (alle Origins erlaubt)
header("Access-Control-Allow-Origin: *");

// Production (nur eigene Domain)
header("Access-Control-Allow-Origin: https://dein-frontend.de");
```

## Troubleshooting

**Problem:** 404 bei API-Calls
→ Prüfe ob mod_rewrite aktiviert ist und .htaccess-Dateien vorhanden sind

**Problem:** "Database connection error"
→ Prüfe MySQL-Verbindung und Credentials in config/database.php

**Problem:** "Nicht authentifiziert"
→ Login-Endpoint aufrufen und Session-Cookie automatisch setzen lassen

Siehe [SETUP.md](SETUP.md) für weitere Details.

## Technologie-Stack

- **PHP** 7.4+ (mit PDO für sichere DB-Queries)
- **MySQL** 5.7+ (mit InnoDB Engine)
- **Apache** (mit mod_rewrite)
- **Session-basierte Auth** (für Multi-Domain Support)

## Lizenz

Projekt-spezifisch
