# OttbergenLocations Backend - Setup-Anleitung

## Voraussetzungen

- XAMPP (oder ähnliche LAMP/WAMP-Umgebung)
- PHP 7.4 oder höher
- MySQL 5.7 oder höher
- Apache mit mod_rewrite aktiviert

## Installation

### 1. Datenbank erstellen

1. Öffne phpMyAdmin (http://localhost/phpmyadmin)
2. Führe die Datei `database/schema.sql` aus:
   - Erstellt die Datenbank `ottbergen_booking`
   - Erstellt alle notwendigen Tabellen
   - Fügt Standard-Einstellungen ein

```sql
-- Importiere schema.sql in phpMyAdmin oder via CLI:
mysql -u root -p < database/schema.sql
```

### 2. Seed-Daten einfügen (Optional, aber empfohlen)

Füge Beispiel-Daten für Entwicklung/Testing ein:

```sql
-- Importiere seed.sql
mysql -u root -p ottbergen_booking < database/seed.sql
```

**Die Seed-Daten erstellen automatisch:**
- 5 Test-User (3 Provider, 2 Kunden)
- 4 Beispiel-Orte mit Bildern und Features
- 3 Beispiel-Buchungen

**Test-Accounts:**

| Email | Passwort | Rolle | Name |
|-------|----------|-------|------|
| max.mustermann@example.com | Test123! | Provider | Max Mustermann |
| maria.schmidt@example.com | Test123! | Provider | Maria Schmidt |
| hans.mueller@example.com | Test123! | Provider | Hans Müller |
| anna.wagner@example.com | Test123! | Kunde | Anna Wagner |
| thomas.klein@example.com | Test123! | Kunde | Thomas Klein |

### 3. Apache mod_rewrite aktivieren

Stelle sicher, dass mod_rewrite in Apache aktiviert ist:

1. Öffne `xampp/apache/conf/httpd.conf`
2. Suche nach `LoadModule rewrite_module modules/mod_rewrite.so`
3. Entferne das `#` am Anfang der Zeile (falls vorhanden)
4. Suche nach allen `AllowOverride None` und ändere sie zu `AllowOverride All` (für dein Verzeichnis)
5. Starte Apache neu

### 4. Datenbankverbindung prüfen

Die Datenbankverbindung ist in `config/database.php` konfiguriert:

```php
private $host = "localhost";
private $db_name = "ottbergen_booking";
private $username = "root";
private $password = "";
```

Passe diese Werte bei Bedarf an.

### 5. CORS konfigurieren (falls nötig)

Alle API-Endpoints haben bereits CORS-Header eingebaut:
```php
header("Access-Control-Allow-Origin: *");
```

Für Production solltest du dies auf deine Frontend-Domain einschränken:
```php
header("Access-Control-Allow-Origin: https://dein-frontend.de");
```

## API-Endpoints

### Places (Orte)

| Methode | Endpoint | Beschreibung |
|---------|----------|--------------|
| GET | `/api/places` | Liste aller Orte (mit Filtern) |
| GET | `/api/places/{id}` | Detailansicht eines Ortes |
| GET | `/api/places/{id}/availability` | Verfügbarkeitsprüfung |
| GET | `/api/places/{id}/calendar` | Buchungskalender |

**Beispiel:**
```
GET http://localhost/OttbergenLocations-Backend/api/places
GET http://localhost/OttbergenLocations-Backend/api/places/1
GET http://localhost/OttbergenLocations-Backend/api/places/1/availability?checkIn=2025-12-15&checkOut=2025-12-20
```

### Bookings (Buchungen)

| Methode | Endpoint | Beschreibung |
|---------|----------|--------------|
| POST | `/api/bookings` | Neue Buchung erstellen |
| GET | `/api/bookings` | Liste eigener Buchungen |
| GET | `/api/bookings/{id}` | Buchungsdetails |
| PATCH | `/api/bookings/{id}/cancel` | Buchung stornieren |
| PATCH | `/api/bookings/{id}/confirm` | Buchung bestätigen (nur Provider) |

**Beispiel:**
```
POST http://localhost/OttbergenLocations-Backend/api/bookings
Body: {"placeId": 1, "checkIn": "2025-12-15", "checkOut": "2025-12-20", "guests": 50}

GET http://localhost/OttbergenLocations-Backend/api/bookings
GET http://localhost/OttbergenLocations-Backend/api/bookings/1
PATCH http://localhost/OttbergenLocations-Backend/api/bookings/1/cancel
```

### Providers (Anbieter)

| Methode | Endpoint | Beschreibung |
|---------|----------|--------------|
| GET | `/api/providers/{id}` | Anbieter-Informationen |
| GET | `/api/providers/{id}/places` | Alle Orte eines Anbieters |

### Auth (Authentifizierung)

| Methode | Endpoint | Beschreibung |
|---------|----------|--------------|
| POST | `/register.php` | Registrierung |
| POST | `/login.php` | Login |
| GET | `/me.php` | Aktueller User |

### User (Benutzer)

| Methode | Endpoint | Beschreibung |
|---------|----------|--------------|
| POST | `/api/user/become-provider.php` | Als Provider registrieren (Auth erforderlich) |

## Testing

### 1. API-Test mit Browser oder Postman

**Places testen:**
```
GET http://localhost/OttbergenLocations-Backend/api/places
```

Erwartete Response:
```json
{
  "success": true,
  "data": [...],
  "total": 4
}
```

### 2. Login testen

Mit einem der Test-Accounts aus den Seed-Daten:

```
POST http://localhost/OttbergenLocations-Backend/login.php
Content-Type: application/json

{
  "email": "anna.wagner@example.com",
  "password": "Test123!"
}
```

Erwartete Response:
```json
{
  "success": true,
  "message": "Login erfolgreich"
}
```

### 3. Geschützte Endpoints testen

Nach dem Login erhältst du eine Session. Nachfolgende Requests müssen mit derselben Session gemacht werden (Cookie wird automatisch gesetzt).

```
POST http://localhost/OttbergenLocations-Backend/api/bookings
Content-Type: application/json

{
  "placeId": 1,
  "checkIn": "2025-12-25",
  "checkOut": "2025-12-27",
  "guests": 30
}
```

## Troubleshooting

### Problem: 404 Fehler bei API-Calls

**Lösung:**
- Prüfe ob mod_rewrite aktiviert ist
- Prüfe ob `.htaccess`-Dateien vorhanden sind in:
  - `api/places/.htaccess`
  - `api/bookings/.htaccess`
  - `api/providers/.htaccess`
- Prüfe ob `AllowOverride All` in Apache-Config gesetzt ist

### Problem: CORS-Fehler im Frontend

**Lösung:**
- Prüfe ob die CORS-Header in den PHP-Dateien korrekt gesetzt sind
- Bei Production: Ersetze `*` mit deiner Frontend-Domain

### Problem: "Database connection error"

**Lösung:**
- Prüfe ob MySQL läuft
- Prüfe Datenbank-Credentials in `config/database.php`
- Prüfe ob Datenbank `ottbergen_booking` existiert

### Problem: "Nicht authentifiziert" bei geschützten Endpoints

**Lösung:**
- Stelle sicher, dass Sessions funktionieren (`session_start()`)
- Login muss vor dem Aufruf geschützter Endpoints erfolgen
- Cookies müssen vom Browser akzeptiert werden

## Datenbankstruktur

### Wichtige Tabellen:

1. **users** - User-Accounts (Kunden & Anbieter)
2. **providers** - Erweiterte Anbieter-Informationen
3. **places** - Orte/Locations
4. **place_images** - Bilder für Orte
5. **place_features** - Ausstattung/Features
6. **bookings** - Buchungen
7. **settings** - Konfigurierbare Einstellungen (Gebühren, etc.)

### Wichtige Beziehungen:

- Ein User kann Provider sein (`is_provider = 1`)
- Ein Provider kann mehrere Places haben
- Ein Place gehört zu einem Provider
- Eine Buchung verbindet einen User mit einem Place

## Sicherheit

### Implementierte Maßnahmen:

1. **SQL Injection Schutz**: Prepared Statements überall
2. **Session-basierte Auth**: Sichere Sessions für Login
3. **Input-Validierung**: Alle Eingaben werden validiert
4. **Autorisierung**: Users können nur eigene Daten sehen/ändern
5. **Password-Hashing**: SHA256 + Salt (bereits implementiert)

### Empfehlungen für Production:

1. Verwende HTTPS
2. Aktiviere PHP Error Reporting nur für Development
3. Setze strikte CORS-Policies
4. Implementiere Rate Limiting
5. Verwende Umgebungsvariablen für DB-Credentials
6. Erwäge JWT statt Sessions für API-Auth

## Nächste Schritte

1. **Email-Benachrichtigungen** implementieren (bei neuen Buchungen, etc.)
2. **Bild-Upload** für Places implementieren
3. **Admin-Panel** für Verwaltung
4. **Bewertungssystem** hinzufügen
5. **Zahlungsintegration** (Stripe, PayPal)

## Support

Bei Fragen oder Problemen:
- Prüfe die `BACKEND_REQUIREMENTS.md` für API-Spezifikation
- Öffne ein Issue im Repository
