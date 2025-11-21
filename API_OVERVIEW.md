# API-Übersicht - OttbergenLocations Backend

Vollständige Übersicht aller implementierten API-Endpoints für das OttbergenLocations-Projekt.

## Base URL
```
http://localhost/OttbergenLocations-Backend
```

## Authentifizierung

Session-basierte Authentifizierung. Nach erfolgreichem Login wird eine Session erstellt.

### Login
```http
POST /login.php
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login erfolgreich"
}
```

### Registrierung
```http
POST /register.php
Content-Type: application/json

{
  "firstName": "Max",
  "lastName": "Mustermann",
  "email": "max@example.com",
  "password": "password123",
  "phone": "+49 123 456789",
  "street": "Hauptstraße",
  "houseNumber": "123",
  "zipCode": "12345",
  "city": "Berlin"
}
```

### Aktueller User
```http
GET /me.php
```

### Provider werden
```http
POST /api/user/become-provider.php
```

**Beschreibung:**
Setzt den `is_provider`-Status des eingeloggten Users auf `true`. Erfordert Authentifizierung.

**Response:**
```json
{
  "success": true,
  "message": "Sie wurden erfolgreich als Provider registriert"
}
```

**Fehler:**
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Nicht authentifiziert. Bitte melden Sie sich an."
  }
}
```

---

## Places (Orte)

### 1. Liste aller Orte
```http
GET /api/places
```

**Query-Parameter (optional):**
- `search` - Suchbegriff (Name, Beschreibung, Location)
- `checkIn` - Anreisedatum (YYYY-MM-DD)
- `checkOut` - Abreisedatum (YYYY-MM-DD)
- `minCapacity` - Minimale Kapazität
- `maxPrice` - Maximaler Preis pro Tag
- `active` - Nur aktive Orte (true/false)

**Beispiel:**
```
GET /api/places?search=kulturraum&minCapacity=50&maxPrice=300
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Kulturraum Ottbergen",
      "description": "Ein wunderschöner Veranstaltungsraum...",
      "location": "Ottbergen",
      "capacity": 100,
      "pricePerDay": 250,
      "images": ["https://example.com/image1.jpg"],
      "latitude": 51.7234,
      "longitude": 9.3456,
      "address": "Hauptstraße 45",
      "postalCode": "37691",
      "active": true,
      "provider": {
        "id": 1,
        "name": "Max Mustermann",
        "memberSince": "2024-01-15",
        "verified": true
      }
    }
  ],
  "total": 1
}
```

---

### 2. Ort-Details
```http
GET /api/places/{id}
```

**Beispiel:**
```
GET /api/places/1
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Kulturraum Ottbergen",
    "description": "Ein wunderschöner Veranstaltungsraum...",
    "location": "Ottbergen",
    "capacity": 100,
    "pricePerDay": 250,
    "images": [
      "https://example.com/image1.jpg",
      "https://example.com/image2.jpg"
    ],
    "latitude": 51.7234,
    "longitude": 9.3456,
    "address": "Hauptstraße 45",
    "postalCode": "37691",
    "active": true,
    "features": [
      {
        "id": 1,
        "name": "WLAN verfügbar",
        "icon": "wifi",
        "available": true
      },
      {
        "id": 2,
        "name": "Parkmöglichkeiten",
        "icon": "parking",
        "available": true
      }
    ],
    "provider": {
      "id": 1,
      "name": "Max Mustermann",
      "email": "max@example.com",
      "phone": "+49 123 456789",
      "memberSince": "2024-01-15",
      "avatar": "https://example.com/avatar.jpg",
      "verified": true
    }
  }
}
```

---

### 3. Verfügbarkeitsprüfung
```http
GET /api/places/{id}/availability?checkIn=YYYY-MM-DD&checkOut=YYYY-MM-DD
```

**Beispiel:**
```
GET /api/places/1/availability?checkIn=2025-12-15&checkOut=2025-12-20
```

**Response (verfügbar):**
```json
{
  "success": true,
  "available": true,
  "blockedDates": [],
  "message": "Der Ort ist im gewählten Zeitraum verfügbar."
}
```

**Response (nicht verfügbar):**
```json
{
  "success": true,
  "available": false,
  "blockedDates": ["2025-12-15", "2025-12-16", "2025-12-17"],
  "message": "Der Ort ist in diesem Zeitraum bereits gebucht."
}
```

---

### 4. Buchungskalender
```http
GET /api/places/{id}/calendar?months=12
```

**Beispiel:**
```
GET /api/places/1/calendar
```

**Response:**
```json
{
  "success": true,
  "data": {
    "placeId": 1,
    "bookedDates": [
      {
        "start": "2025-12-15",
        "end": "2025-12-20"
      },
      {
        "start": "2026-01-05",
        "end": "2026-01-08"
      }
    ]
  }
}
```

---

## Places Management (Ortsverwaltung für Provider)

🔒 **Alle Management-Endpoints erfordern Authentifizierung als Provider**

### 1. Eigene Orte abrufen
```http
GET /api/places/my-places.php
```

**Beschreibung:**
Gibt alle Orte des eingeloggten Providers zurück.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Kulturraum Ottbergen",
      "description": "Ein wunderschöner Veranstaltungsraum...",
      "location": "Ottbergen",
      "capacity": 100,
      "pricePerDay": 250,
      "latitude": 51.7234,
      "longitude": 9.3456,
      "address": "Hauptstraße 45",
      "postalCode": "37691",
      "active": true,
      "images": ["https://example.com/image1.jpg"],
      "features": [
        {
          "id": 1,
          "name": "WLAN verfügbar",
          "icon": "wifi",
          "available": true
        }
      ]
    }
  ],
  "total": 1
}
```

---

### 2. Neuen Ort erstellen
```http
POST /api/places/create.php
Content-Type: application/json

{
  "name": "Mein neuer Ort",
  "description": "Beschreibung des Ortes",
  "location": "Stadtname",
  "capacity": 50,
  "pricePerDay": 150.00,
  "latitude": 51.1234,
  "longitude": 9.5678,
  "address": "Musterstraße 1",
  "postalCode": "12345",
  "active": true,
  "images": ["https://example.com/image1.jpg"],
  "features": [
    {
      "name": "WLAN verfügbar",
      "icon": "wifi",
      "available": true
    }
  ]
}
```

**Pflichtfelder:**
- `name` - Name des Ortes
- `description` - Beschreibung
- `location` - Standort
- `capacity` - Kapazität (min. 1)
- `pricePerDay` - Preis pro Tag (positiv)

**Optionale Felder:**
- `latitude`, `longitude` - GPS-Koordinaten
- `address`, `postalCode` - Adresse
- `active` - Status (default: true)
- `images` - Array von Bild-URLs
- `features` - Array von Features

**Response (201 Created):**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "name": "Mein neuer Ort",
    ...
  },
  "message": "Ort erfolgreich erstellt"
}
```

**Fehler:**
- `NOT_A_PROVIDER` - User ist kein Provider
- `MISSING_FIELDS` - Pflichtfelder fehlen
- `INVALID_CAPACITY` - Ungültige Kapazität
- `INVALID_PRICE` - Ungültiger Preis

---

### 3. Ort aktualisieren
```http
PATCH /api/places/update.php?id={id}
Content-Type: application/json

{
  "name": "Aktualisierter Name",
  "pricePerDay": 200.00,
  "active": false
}
```

**Beschreibung:**
Aktualisiert einen bestehenden Ort. Nur eigene Orte können bearbeitet werden.
Alle Felder sind optional - es werden nur die angegebenen Felder aktualisiert.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Aktualisierter Name",
    ...
  },
  "message": "Ort erfolgreich aktualisiert"
}
```

**Fehler:**
- `PLACE_NOT_FOUND` - Ort existiert nicht
- `FORBIDDEN` - Keine Berechtigung (nicht eigener Ort)
- `NO_UPDATE_DATA` - Keine Daten zum Aktualisieren

---

### 4. Ort löschen
```http
DELETE /api/places/delete.php?id={id}
```

**Beschreibung:**
Löscht einen Ort. Nur eigene Orte können gelöscht werden.
Orte mit aktiven Buchungen können nicht gelöscht werden.

**Response:**
```json
{
  "success": true,
  "message": "Ort erfolgreich gelöscht"
}
```

**Fehler:**
- `PLACE_NOT_FOUND` - Ort existiert nicht
- `FORBIDDEN` - Keine Berechtigung (nicht eigener Ort)
- `HAS_ACTIVE_BOOKINGS` - Ort hat noch aktive Buchungen

---

### 5. Ort aktivieren/deaktivieren
```http
PATCH /api/places/toggle-active.php?id={id}
```

**Beschreibung:**
Schaltet den aktiven Status eines Ortes um (aktiv ↔ inaktiv).

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "active": false
  },
  "message": "Ort wurde deaktiviert"
}
```

**Fehler:**
- `PLACE_NOT_FOUND` - Ort existiert nicht
- `FORBIDDEN` - Keine Berechtigung (nicht eigener Ort)

---

## Bookings (Buchungen)

🔒 **Alle Booking-Endpoints erfordern Authentifizierung**

### 1. Buchung erstellen
```http
POST /api/bookings
Content-Type: application/json

{
  "placeId": 1,
  "checkIn": "2025-12-15",
  "checkOut": "2025-12-20",
  "guests": 50
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "placeId": 1,
    "placeName": "Kulturraum Ottbergen",
    "placeLocation": "Ottbergen",
    "userId": 42,
    "checkIn": "2025-12-15",
    "checkOut": "2025-12-20",
    "guests": 50,
    "subtotal": 1250.00,
    "serviceFee": 62.50,
    "tax": 249.38,
    "totalPrice": 1561.88,
    "status": "pending"
  },
  "message": "Buchung erfolgreich erstellt. Bitte warten Sie auf die Bestätigung des Anbieters."
}
```

**Fehler-Codes:**
- `PLACE_NOT_FOUND` - Ort existiert nicht
- `PLACE_NOT_ACTIVE` - Ort ist nicht buchbar
- `PLACE_NOT_AVAILABLE` - Ort ist im Zeitraum bereits gebucht
- `INVALID_DATE_RANGE` - Ungültiger Datumsbereich
- `CAPACITY_EXCEEDED` - Zu viele Gäste
- `UNAUTHORIZED` - Nicht eingeloggt

---

### 2. Eigene Buchungen
```http
GET /api/bookings?status=confirmed&page=1&limit=20
```

**Query-Parameter (optional):**
- `status` - Filter nach Status (pending, confirmed, upcoming, completed, cancelled)
- `page` - Seitennummer (Default: 1)
- `limit` - Items pro Seite (Default: 20, Max: 100)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "placeId": 1,
      "placeName": "Kulturraum Ottbergen",
      "placeLocation": "Ottbergen",
      "checkIn": "2025-12-15",
      "checkOut": "2025-12-20",
      "guests": 50,
      "totalPrice": 1561.88,
      "status": "confirmed"
    }
  ],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 1,
    "pages": 1
  }
}
```

---

### 3. Buchungsdetails
```http
GET /api/bookings/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "placeId": 1,
    "place": {
      "name": "Kulturraum Ottbergen",
      "location": "Ottbergen",
      "address": "Hauptstraße 45",
      "postalCode": "37691",
      "images": ["https://example.com/image1.jpg"]
    },
    "provider": {
      "id": 1,
      "name": "Max Mustermann",
      "email": "max@example.com",
      "phone": "+49 123 456789"
    },
    "checkIn": "2025-12-15",
    "checkOut": "2025-12-20",
    "guests": 50,
    "subtotal": 1250.00,
    "serviceFee": 62.50,
    "tax": 249.38,
    "totalPrice": 1561.88,
    "status": "confirmed"
  }
}
```

---

### 4. Buchung stornieren
```http
PATCH /api/bookings/{id}/cancel
Content-Type: application/json

{
  "reason": "Terminänderung"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "status": "cancelled",
    "cancelledAt": "2025-11-20T09:00:00Z",
    "cancellationReason": "Terminänderung"
  },
  "message": "Buchung erfolgreich storniert"
}
```

**Hinweise:**
- Nur eigene Buchungen oder Buchungen für eigene Orte (als Provider) können storniert werden
- Nur Status `pending`, `confirmed`, `upcoming` können storniert werden
- Bei Überschreitung der Stornierungsfrist (48h) wird eine Warnung zurückgegeben

---

### 5. Buchung bestätigen (nur Provider)
```http
PATCH /api/bookings/{id}/confirm
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "status": "confirmed"
  },
  "message": "Buchung erfolgreich bestätigt"
}
```

**Hinweise:**
- Nur der Anbieter des gebuchten Ortes kann bestätigen
- Nur Status `pending` kann bestätigt werden
- Status wird automatisch zu `upcoming` wenn Check-in < 7 Tage

---

## Providers (Anbieter)

### 1. Anbieter-Informationen
```http
GET /api/providers/{id}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Max Mustermann",
    "memberSince": "2024-01-15",
    "avatar": "https://example.com/avatar.jpg",
    "verified": true,
    "placesCount": 3
  }
}
```

---

### 2. Orte eines Anbieters
```http
GET /api/providers/{id}/places
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Kulturraum Ottbergen",
      "location": "Ottbergen",
      "capacity": 100,
      "pricePerDay": 250,
      "images": ["https://example.com/image1.jpg"],
      "active": true
    }
  ],
  "total": 1
}
```

---

## Fehlerbehandlung

Alle Fehler-Responses folgen diesem Format:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Benutzerfreundliche Fehlermeldung",
    "details": {}
  }
}
```

### Häufige Fehler-Codes:

| Code | HTTP Status | Beschreibung |
|------|-------------|--------------|
| `UNAUTHORIZED` | 401 | Nicht authentifiziert |
| `FORBIDDEN` | 403 | Keine Berechtigung |
| `PLACE_NOT_FOUND` | 404 | Ort existiert nicht |
| `BOOKING_NOT_FOUND` | 404 | Buchung existiert nicht |
| `INVALID_JSON` | 400 | Ungültige JSON-Daten |
| `MISSING_FIELDS` | 400 | Pflichtfelder fehlen |
| `MISSING_PLACE_ID` | 400 | Ort-ID fehlt |
| `INVALID_DATE_RANGE` | 400 | Ungültiger Datumsbereich |
| `INVALID_CAPACITY` | 400 | Ungültige Kapazität |
| `INVALID_PRICE` | 400 | Ungültiger Preis |
| `CAPACITY_EXCEEDED` | 400 | Zu viele Gäste |
| `NO_UPDATE_DATA` | 400 | Keine Daten zum Aktualisieren |
| `PLACE_NOT_AVAILABLE` | 409 | Ort ist bereits gebucht |
| `HAS_ACTIVE_BOOKINGS` | 409 | Ort hat aktive Buchungen |
| `INVALID_STATUS` | 400 | Ungültiger Status-Übergang |
| `NOT_A_PROVIDER` | 403 | User ist kein Provider |
| `METHOD_NOT_ALLOWED` | 405 | HTTP-Methode nicht erlaubt |
| `SERVER_ERROR` | 500 | Interner Serverfehler |

---

## Status-Definitionen

### Booking-Status:

| Status | Beschreibung |
|--------|--------------|
| `pending` | Buchung angefragt, wartet auf Bestätigung |
| `confirmed` | Buchung bestätigt, Check-in > 7 Tage |
| `upcoming` | Bestätigt, Check-in < 7 Tage |
| `completed` | Abgeschlossen, Check-out erfolgt |
| `cancelled` | Storniert |

### Erlaubte Status-Übergänge:

```
pending → confirmed (durch Provider)
pending → cancelled (durch User/Provider)
confirmed → upcoming (automatisch bei < 7 Tagen)
confirmed → cancelled (durch User/Provider)
upcoming → completed (automatisch am Check-out)
upcoming → cancelled (nur Provider)
```

---

## Preisberechnung

Alle Preise werden automatisch berechnet:

```
Anzahl Tage = checkOut - checkIn
Zwischensumme = pricePerDay × Anzahl Tage
Servicegebühr = Zwischensumme × 5%
MwSt = (Zwischensumme + Servicegebühr) × 19%
Gesamtpreis = Zwischensumme + Servicegebühr + MwSt
```

Prozentsätze sind in der `settings`-Tabelle konfigurierbar.

---

## Datum-Format

**API sendet/empfängt:**
- Datum: `YYYY-MM-DD` (z.B. `2025-12-15`)
- Zeitstempel: ISO 8601 `YYYY-MM-DDTHH:mm:ssZ` (z.B. `2025-11-19T10:00:00Z`)

---

## Implementierungsstatus

✅ **Vollständig implementiert:**
- Alle Places-Endpoints (öffentlich)
- Places Management für Provider (CRUD)
- Alle Bookings-Endpoints
- Provider-Endpoints
- Auth-System (Login, Register, Provider-Registrierung)
- Validierung & Fehlerbehandlung
- Preisberechnung
- Verfügbarkeitsprüfung
- Autorisierung & Zugriffskontrolle

⏳ **Noch zu implementieren (Nice-to-have):**
- Email-Benachrichtigungen
- Bild-Upload für Places
- Bewertungssystem
- Favoriten/Merkliste
- Zahlungsintegration
- Admin-Panel
