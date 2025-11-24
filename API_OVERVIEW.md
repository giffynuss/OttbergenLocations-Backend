# API-Übersicht - OttbergenLocations Backend

**Base URL:** `http://localhost/OttbergenLocations-Backend/api/`

## Authentifizierung
Session-basierte Authentifizierung. CORS für `http://localhost:5173` (Frontend).

---

## Auth-Endpoints

### POST /auth/register.php
Neuen User registrieren.
```json
{
  "firstName": "Max", "lastName": "Mustermann", "gender": "herr",
  "email": "max@example.com", "password": "password123",
  "phone": "+49 123 456789", "street": "Hauptstraße", "houseNumber": "123",
  "zipCode": "12345", "city": "Berlin"
}
```

### POST /auth/login.php
User einloggen (Session wird erstellt).
```json
{ "email": "user@example.com", "password": "password123" }
```

### POST /auth/logout.php
User ausloggen.

### GET /user/me.php
🔒 Aktuell eingeloggten User abrufen.

### POST /user/become-provider.php
🔒 User wird zum Provider (setzt `is_provider = true`).

### PATCH /user/update.php
🔒 User-Daten aktualisieren.

### POST /user/change_password.php
🔒 Passwort ändern.

---

## Places (Orte)

### GET /places/list.php
Liste aller aktiven Orte (Frontend-optimiert).

**Query-Parameter (optional):**
- `search` - Suche in Name, Beschreibung, Location
- `location` - Filter nach Ort
- `minCapacity` - Minimale Kapazität
- `maxPrice` - Maximaler Preis pro Tag
- `checkIn`, `checkOut` - Nur verfügbare Orte (YYYY-MM-DD)

**Response:**
```json
{
  "success": true,
  "places": [{
    "id": 1, "name": "Kulturraum Ottbergen",
    "description": "...", "location": "Ottbergen",
    "capacity": 100, "pricePerDay": 250,
    "latitude": 51.7234, "longitude": 9.3456,
    "address": "Hauptstraße 45", "postalCode": "37691",
    "active": true, "images": ["url1.jpg"],
    "provider": { "id": 1, "name": "Max Mustermann" }
  }]
}
```

### GET /places/get.php?id={id}
Einzelner Ort mit Details, Features und verfügbaren Zeiträumen.

**Response:**
```json
{
  "success": true,
  "place": {
    "id": 1, "name": "Kulturraum Ottbergen", "...",
    "features": [
      { "id": 1, "name": "WLAN verfügbar", "category": "wifi" }
    ],
    "provider": {
      "id": 1, "name": "Max Mustermann",
      "email": "max@example.com", "phone": "+49 123 456789"
    },
    "availableDates": [
      { "start": "2025-01-01", "end": "2025-01-15" }
    ]
  }
}
```

### GET /places/availability.php?id={id}&checkIn={date}&checkOut={date}
Verfügbarkeitsprüfung für Zeitraum.

**Response:**
```json
{
  "success": true,
  "available": true,
  "blockedDates": []
}
```

### GET /places/calendar.php?id={id}&months=12
Buchungskalender mit allen gebuchten Zeiträumen.

---

## Places Management (Provider) 🔒

### GET /places/my-places.php
🔒 Eigene Orte des Providers abrufen.

### POST /places/create.php
🔒 Neuen Ort erstellen (nur Provider).

**Pflichtfelder:** `name`, `description`, `location`, `capacity`, `pricePerDay`
**Optional:** `latitude`, `longitude`, `address`, `postalCode`, `active`, `images[]`, `features[]`

### PATCH /places/update.php?id={id}
🔒 Eigenen Ort aktualisieren.

### DELETE /places/delete.php?id={id}
🔒 Eigenen Ort löschen (nicht möglich bei aktiven Buchungen).

### PATCH /places/toggle-active.php?id={id}
🔒 Ort aktivieren/deaktivieren.

---

## Bookings (Buchungen)

### POST /bookings/create.php
Neue Buchung erstellen. **KEINE Auth erforderlich** (Gast-Buchungen möglich).

**Request:**
```json
{
  "placeId": 1,
  "checkIn": "2025-12-15",
  "checkOut": "2025-12-20",
  "guests": 50,
  "paymentMethod": "transfer",
  "userInfo": {
    "gender": "herr",
    "firstName": "Max",
    "lastName": "Mustermann",
    "email": "max@example.com",
    "phone": "+49 123 456789",
    "street": "Musterstraße 123",
    "postalCode": "12345",
    "city": "Musterstadt"
  }
}
```

**Pflichtfelder:**
- `placeId`, `checkIn`, `checkOut`, `guests`
- `paymentMethod`: `cash`, `paypal`, `transfer`, `wero`
- `userInfo`: `gender` (herr/frau), `firstName`, `lastName`, `email`, `phone`
- Bei `transfer`: zusätzlich `street`, `postalCode`, `city`

**Response (Success):**
```json
{
  "success": true,
  "booking": {
    "id": 123,
    "placeId": 1,
    "placeName": "Kulturraum Ottbergen",
    "placeLocation": "Ottbergen",
    "userId": null,
    "checkIn": "2025-12-15",
    "checkOut": "2025-12-20",
    "guests": 50,
    "totalPrice": 1250.00,
    "paymentMethod": "transfer",
    "bookingReference": "BK20251124-1234",
    "status": "pending",
    "guestInfo": { "gender": "herr", "firstName": "Max", "..." }
  },
  "paymentDetails": {
    "accountHolder": "Ottbergen Locations GmbH",
    "iban": "DE89 3704 0044 0532 0130 00",
    "bic": "COBADEFFXXX",
    "bankName": "Commerzbank"
  },
  "message": "Buchung erfolgreich erstellt"
}
```

**Hinweise:**
- `bookingReference` wird automatisch generiert (Format: `BK{YYYYMMDD}-{4digits}`)
- `paymentDetails` nur bei `paymentMethod === "transfer"`
- `userId` ist `null` bei Gast-Buchungen
- Preisberechnung erfolgt serverseitig
- Gast-Informationen werden in `booking_guest_info` gespeichert

### GET /bookings/index.php 🔒
Eigene Buchungen abrufen (mit Filter & Pagination).

**Query-Parameter:**
- `status` - Filter: pending, confirmed, upcoming, completed, cancelled
- `page`, `limit` - Pagination

### GET /bookings/detail.php?id={id} 🔒
Buchungsdetails abrufen.

### PATCH /bookings/cancel.php?id={id} 🔒
Buchung stornieren (eigene oder als Provider).

**Request:** `{ "reason": "Terminänderung" }`

### PATCH /bookings/confirm.php?id={id} 🔒
Buchung bestätigen (nur Provider für eigene Orte).

---

## Fehlerbehandlung

**Error Format:**
```json
{
  "success": false,
  "message": "Fehlermeldung",
  "errors": { /* optional details */ }
}
```

### HTTP Status Codes
- `200` OK
- `201` Created
- `400` Bad Request (Validierungsfehler)
- `401` Unauthorized (nicht eingeloggt)
- `403` Forbidden (keine Berechtigung)
- `404` Not Found
- `405` Method Not Allowed
- `409` Conflict (z.B. Ort nicht verfügbar)
- `500` Internal Server Error

### Häufige Error Codes
- `UNAUTHORIZED` - Nicht authentifiziert
- `FORBIDDEN` - Keine Berechtigung
- `PLACE_NOT_FOUND` - Ort existiert nicht
- `PLACE_NOT_AVAILABLE` - Ort im Zeitraum gebucht
- `BOOKING_NOT_FOUND` - Buchung existiert nicht
- `INVALID_JSON` - Ungültige JSON-Daten
- `MISSING_FIELDS` - Pflichtfelder fehlen
- `INVALID_DATE_RANGE` - Ungültiger Datumsbereich
- `CAPACITY_EXCEEDED` - Zu viele Gäste
- `INVALID_EMAIL` - Ungültige E-Mail-Adresse
- `INVALID_GENDER` - Gender muss "herr" oder "frau" sein
- `NOT_A_PROVIDER` - User ist kein Provider
- `HAS_ACTIVE_BOOKINGS` - Ort hat aktive Buchungen

---

## Booking-Status

| Status | Beschreibung |
|--------|--------------|
| `pending` | Buchung angefragt, wartet auf Bestätigung |
| `confirmed` | Buchung bestätigt, Check-in > 7 Tage |
| `upcoming` | Bestätigt, Check-in < 7 Tage |
| `completed` | Abgeschlossen, Check-out erfolgt |
| `cancelled` | Storniert |

**Erlaubte Übergänge:**
```
pending → confirmed (Provider)
pending → cancelled (User/Provider)
confirmed → upcoming (automatisch < 7 Tage)
confirmed → cancelled (User/Provider)
upcoming → completed (automatisch am Check-out)
upcoming → cancelled (nur Provider)
```

---

## Preisberechnung

```
Anzahl Tage = checkOut - checkIn
Gesamtpreis = pricePerDay × Anzahl Tage
```

---

## Datum-Format

- **Datum:** `YYYY-MM-DD` (z.B. `2025-12-15`)
- **Zeitstempel:** ISO 8601 `YYYY-MM-DDTHH:mm:ssZ`

---

## Implementierungsstatus

### ✅ Vollständig implementiert
- Alle Places-Endpoints (öffentlich + Management)
- Booking-System mit Gast-Buchungen
- Auth-System (Login, Register, Provider)
- Zahlungsmethoden (cash, transfer mit Mock-Bankdaten)
- Validierung & Fehlerbehandlung
- Verfügbarkeitsprüfung
- Autorisierung & Zugriffskontrolle

### ⏳ Noch nicht implementiert
- E-Mail-Benachrichtigungen
- PayPal/Wero Integration
- Provider-Bankdaten-Verwaltung
- Bild-Upload
- Bewertungssystem
- Admin-Panel

---

## Wichtige Hinweise

1. **CORS:** Konfiguriert für `http://localhost:5173`
2. **Sessions:** Cookie-basiert mit `credentials: include`
3. **Gender:** Nur "herr" oder "frau" (lowercase)
4. **Booking-Reference:** Wird automatisch generiert
5. **Mock-Bankdaten:** Bei transfer-Zahlung von Ottbergen Locations GmbH
6. **Gast-Buchungen:** Möglich ohne Login, `user_id = NULL`

---

**Version:** 1.1
**Letztes Update:** 2024-11-24
**Backend-Team**
