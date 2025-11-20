# Backend Anforderungen für OttbergenLocations - Buchungsprozess

## Zusammenfassung
Dieses Dokument beschreibt die Backend-Anforderungen für den Buchungsprozess der OttbergenLocations-Plattform. Das Frontend zeigt nun eine vollständige Detailansicht für Orte mit Buchungsmöglichkeit. Das Backend muss die entsprechenden API-Endpoints und Datenstrukturen bereitstellen.

---

## 1. Datenbank-Modelle

### 1.1 Places (Orte) - Erweitert

Das bestehende Places-Modell sollte folgende Felder umfassen oder erweitert werden:

```typescript
Place {
  id: number (Primary Key)
  name: string (Titel des Ortes)
  description: string (Ausführliche Beschreibung)
  location: string (Stadteil/Bereich z.B. "Ottbergen Nord")
  capacity: number (Maximale Personenanzahl)
  pricePerDay: number (Preis pro Tag in Euro)

  // Erweiterte Informationen
  images: string[] (Array von Bild-URLs)
  latitude: float (GPS-Koordinate)
  longitude: float (GPS-Koordinate)
  address: string (Straße + Hausnummer)
  postalCode: string (PLZ)

  // Anbieter-Beziehung
  providerId: number (Foreign Key zu Users)

  // Status & Timestamps
  active: boolean (Ist der Ort buchbar?)
  createdAt: timestamp
  updatedAt: timestamp
}
```

**Wichtig:** Das Frontend verwendet aktuell `pricePerDay` (nicht `price`). Bitte dieses Feld verwenden oder sicherstellen, dass die API-Response `pricePerDay` enthält.

---

### 1.2 PlaceFeatures (Ausstattung)

Speichert die Features/Ausstattung eines Ortes:

```typescript
PlaceFeature {
  id: number (Primary Key)
  placeId: number (Foreign Key zu Places)
  name: string (z.B. "WLAN", "Parkmöglichkeiten", "Küche", "Barrierearm")
  icon: string (Optional: Icon-Name oder SVG)
  available: boolean
  createdAt: timestamp
}
```

**Beispiel-Features:**
- WLAN verfügbar
- Parkmöglichkeiten
- Küche vorhanden
- Barrierearm/Barrierefrei
- Klimaanlage
- Bühne vorhanden
- Ton- und Lichttechnik
- Außenbereich
- Sanitäranlagen

---

### 1.3 Bookings (Buchungen) - Erweitert

```typescript
Booking {
  id: number (Primary Key)
  placeId: number (Foreign Key zu Places)
  userId: number (Foreign Key zu Users)

  // Buchungszeitraum
  checkIn: date (Anreisedatum)
  checkOut: date (Abreisedatum)

  // Gäste/Teilnehmer
  guests: number (Anzahl der Personen)

  // Preisberechnung
  subtotal: decimal (Preis pro Tag × Anzahl Tage)
  serviceFee: decimal (Servicegebühr, z.B. 5%)
  tax: decimal (MwSt. 19%)
  totalPrice: decimal (Gesamtpreis inkl. aller Gebühren)

  // Status-Management
  status: enum ['pending', 'confirmed', 'upcoming', 'completed', 'cancelled']

  // Stornierung
  cancelledAt: timestamp (Optional)
  cancellationReason: string (Optional)

  // Timestamps
  createdAt: timestamp
  updatedAt: timestamp
}
```

**Status-Beschreibungen:**
- `pending`: Buchung wurde angefragt, wartet auf Bestätigung
- `confirmed`: Buchung wurde bestätigt
- `upcoming`: Bestätigte Buchung, Anreise steht bevor
- `completed`: Buchung abgeschlossen (Check-out erfolgt)
- `cancelled`: Buchung wurde storniert

---

### 1.4 Providers (Anbieter) - Beziehung zu Users

Anbieter-Informationen können Teil des User-Modells sein oder als separate Tabelle:

```typescript
Provider {
  id: number (Primary Key = userId)
  userId: number (Foreign Key zu Users)
  name: string (Anzeigename)
  phone: string (Telefonnummer)
  memberSince: date (Mitglied seit)
  avatar: string (Profilbild-URL, Optional)
  verified: boolean (Verifizierter Anbieter?)
}
```

---

## 2. API-Endpoints

### 2.1 Places (Orte)

#### GET /api/places
Liste aller Orte mit optionalen Filtern.

**Query-Parameter:**
- `search` (string): Suchbegriff für Name, Beschreibung oder Location
- `checkIn` (date): Anreisedatum für Verfügbarkeitsprüfung
- `checkOut` (date): Abreisedatum für Verfügbarkeitsprüfung
- `minCapacity` (number): Minimale Kapazität
- `maxPrice` (number): Maximaler Preis pro Tag
- `active` (boolean): Nur aktive Orte (Default: true)

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
      "images": [
        "https://example.com/images/place1-1.jpg",
        "https://example.com/images/place1-2.jpg"
      ],
      "latitude": 51.7234,
      "longitude": 9.3456,
      "address": "Beispielstraße 123",
      "postalCode": "12345",
      "active": true,
      "provider": {
        "id": 5,
        "name": "Max Mustermann",
        "memberSince": "2024-01-15"
      }
    }
  ],
  "total": 1
}
```

---

#### GET /api/places/:id
Detailansicht eines einzelnen Ortes mit allen Informationen.

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
      "https://example.com/images/place1-1.jpg",
      "https://example.com/images/place1-2.jpg"
    ],
    "latitude": 51.7234,
    "longitude": 9.3456,
    "address": "Beispielstraße 123",
    "postalCode": "12345",
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
      },
      {
        "id": 3,
        "name": "Küche vorhanden",
        "icon": "kitchen",
        "available": true
      }
    ],
    "provider": {
      "id": 5,
      "name": "Max Mustermann",
      "email": "max@example.com",
      "phone": "+49 123 456789",
      "memberSince": "2024-01-15",
      "avatar": "https://example.com/avatars/user5.jpg"
    },
    "createdAt": "2024-01-20T10:00:00Z",
    "updatedAt": "2024-12-01T14:30:00Z"
  }
}
```

---

#### GET /api/places/:id/availability
Prüft die Verfügbarkeit eines Ortes für einen bestimmten Zeitraum.

**Query-Parameter:**
- `checkIn` (date, required): Anreisedatum
- `checkOut` (date, required): Abreisedatum

**Response:**
```json
{
  "success": true,
  "available": true,
  "blockedDates": [],
  "message": "Der Ort ist im gewählten Zeitraum verfügbar"
}
```

Oder wenn nicht verfügbar:
```json
{
  "success": true,
  "available": false,
  "blockedDates": [
    "2025-12-15",
    "2025-12-16",
    "2025-12-17"
  ],
  "message": "Der Ort ist in diesem Zeitraum bereits gebucht"
}
```

---

#### GET /api/places/:id/calendar
Gibt einen Kalender mit gebuchten Daten für die nächsten 12 Monate zurück.

**Query-Parameter:**
- `months` (number, optional): Anzahl der Monate (Default: 12)

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

### 2.2 Bookings (Buchungen)

#### POST /api/bookings
Erstellt eine neue Buchung.

**Request Body:**
```json
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
    "subtotal": 1250,
    "serviceFee": 62.50,
    "tax": 249.38,
    "totalPrice": 1561.88,
    "status": "pending",
    "createdAt": "2025-11-19T10:00:00Z"
  },
  "message": "Buchung erfolgreich erstellt. Bitte warten Sie auf die Bestätigung des Anbieters."
}
```

**Validierung:**
- Prüfen ob Ort existiert und aktiv ist
- Prüfen ob Zeitraum verfügbar ist
- Prüfen ob `checkOut` nach `checkIn` liegt
- Prüfen ob `guests` <= `capacity`
- User muss authentifiziert sein

---

#### GET /api/bookings
Liste aller Buchungen des eingeloggten Users.

**Query-Parameter:**
- `status` (string, optional): Filter nach Status (pending, confirmed, upcoming, completed, cancelled)
- `page` (number, optional): Pagination
- `limit` (number, optional): Anzahl pro Seite (Default: 20)

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
      "status": "confirmed",
      "createdAt": "2025-11-19T10:00:00Z"
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

#### GET /api/bookings/:id
Detailansicht einer einzelnen Buchung.

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
      "address": "Beispielstraße 123",
      "postalCode": "12345",
      "images": ["https://example.com/images/place1-1.jpg"]
    },
    "provider": {
      "name": "Max Mustermann",
      "email": "max@example.com",
      "phone": "+49 123 456789"
    },
    "checkIn": "2025-12-15",
    "checkOut": "2025-12-20",
    "guests": 50,
    "subtotal": 1250,
    "serviceFee": 62.50,
    "tax": 249.38,
    "totalPrice": 1561.88,
    "status": "confirmed",
    "createdAt": "2025-11-19T10:00:00Z",
    "updatedAt": "2025-11-19T11:30:00Z"
  }
}
```

---

#### PATCH /api/bookings/:id/cancel
Storniert eine Buchung.

**Request Body:**
```json
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

**Validierung:**
- Nur eigene Buchungen können storniert werden
- Buchung muss Status `pending`, `confirmed` oder `upcoming` haben
- Optional: Prüfen ob Stornierungsfrist eingehalten wird (z.B. 48h vor checkIn)

---

#### PATCH /api/bookings/:id/confirm
Bestätigt eine Buchung (nur für Anbieter des Ortes).

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "status": "confirmed",
    "updatedAt": "2025-11-20T10:00:00Z"
  },
  "message": "Buchung erfolgreich bestätigt"
}
```

**Validierung:**
- Nur der Anbieter des gebuchten Ortes darf bestätigen
- Status muss `pending` sein

---

### 2.3 Provider (Anbieter)

#### GET /api/providers/:id
Öffentliche Informationen eines Anbieters.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "name": "Max Mustermann",
    "memberSince": "2024-01-15",
    "avatar": "https://example.com/avatars/user5.jpg",
    "verified": true,
    "placesCount": 3
  }
}
```

---

#### GET /api/providers/:id/places
Alle Orte eines Anbieters.

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
      "images": ["https://example.com/images/place1-1.jpg"],
      "active": true
    }
  ],
  "total": 1
}
```

---

## 3. Geschäftslogik & Regeln

### 3.1 Preisberechnung
Das Frontend berechnet aktuell:
```
Zwischensumme = pricePerDay × Anzahl_Tage
Servicegebühr = Zwischensumme × 0.05 (5%)
MwSt = (Zwischensumme + Servicegebühr) × 0.19 (19%)
Gesamtpreis = Zwischensumme + Servicegebühr + MwSt
```

**Backend sollte:**
- Diese Berechnung serverseitig durchführen und validieren
- Prozentsätze konfigurierbar machen (z.B. in Settings-Tabelle)
- Rundung auf 2 Dezimalstellen sicherstellen

---

### 3.2 Verfügbarkeitsprüfung
Beim Erstellen einer Buchung muss geprüft werden:
1. Gibt es überlappende Buchungen für diesen Ort?
   ```sql
   WHERE placeId = :placeId
   AND status IN ('confirmed', 'upcoming')
   AND (
     (checkIn BETWEEN :newCheckIn AND :newCheckOut) OR
     (checkOut BETWEEN :newCheckIn AND :newCheckOut) OR
     (checkIn <= :newCheckIn AND checkOut >= :newCheckOut)
   )
   ```
2. Wenn Überschneidung gefunden → Buchung ablehnen

---

### 3.3 Status-Übergangs-Regeln

**Erlaubte Status-Übergänge:**
```
pending → confirmed (durch Anbieter)
pending → cancelled (durch User oder Anbieter)
confirmed → upcoming (automatisch, wenn checkIn < 7 Tage)
confirmed → cancelled (durch User mit Frist oder Anbieter)
upcoming → completed (automatisch am checkOut-Tag)
upcoming → cancelled (nur durch Anbieter mit Begründung)
```

---

### 3.4 Stornierungsbedingungen
- **Kostenlose Stornierung:** Bis 48 Stunden vor checkIn
- **Nach der Frist:** Rückerstattung nach Ermessen des Anbieters
- Status `cancelled` setzen mit Zeitstempel und Grund

---

### 3.5 Benachrichtigungen (Optional, aber empfohlen)

**E-Mail-Benachrichtigungen sollten gesendet werden bei:**
- Neue Buchungsanfrage (an Anbieter)
- Buchung bestätigt (an User)
- Buchung storniert (an beide Parteien)
- Erinnerung 7 Tage vor Check-in (an User)

---

## 4. Authentifizierung & Autorisierung

### 4.1 Authentifizierung
- Alle Buchungs-Endpoints benötigen einen authentifizierten User
- JWT oder Session-basierte Auth
- Token im Authorization Header: `Bearer <token>`

### 4.2 Autorisierung

**User-Rollen:**
- `user`: Kann Buchungen erstellen und eigene Buchungen verwalten
- `provider`: Zusätzlich: Kann eigene Orte verwalten und Buchungen bestätigen
- `admin`: Vollzugriff

**Berechtigungen:**
- User kann nur eigene Buchungen sehen und stornieren
- Provider kann nur Buchungen für eigene Orte bestätigen
- Provider kann eigene Orte bearbeiten (über /api/places/:id)

---

## 5. Fehlerbehandlung

### 5.1 Standard-Fehlerformat
```json
{
  "success": false,
  "error": {
    "code": "PLACE_NOT_AVAILABLE",
    "message": "Der Ort ist im gewählten Zeitraum nicht verfügbar",
    "details": {
      "blockedDates": ["2025-12-15", "2025-12-16"]
    }
  }
}
```

### 5.2 Wichtige Error-Codes

| Code | HTTP Status | Beschreibung |
|------|-------------|--------------|
| `PLACE_NOT_FOUND` | 404 | Ort existiert nicht |
| `PLACE_NOT_AVAILABLE` | 409 | Ort ist im Zeitraum gebucht |
| `INVALID_DATE_RANGE` | 400 | Check-out liegt vor Check-in |
| `CAPACITY_EXCEEDED` | 400 | Zu viele Gäste für den Ort |
| `BOOKING_NOT_FOUND` | 404 | Buchung existiert nicht |
| `UNAUTHORIZED` | 401 | Nicht authentifiziert |
| `FORBIDDEN` | 403 | Keine Berechtigung |
| `CANCELLATION_DEADLINE_PASSED` | 400 | Stornierungsfrist überschritten |

---

## 6. Bilder & Datei-Uploads

### 6.1 Bild-Upload für Orte
- Endpoint: `POST /api/places/:id/images`
- Akzeptierte Formate: JPEG, PNG, WebP
- Max. Dateigröße: 5 MB pro Bild
- Max. Anzahl: 10 Bilder pro Ort
- Automatische Komprimierung und Thumbnail-Generierung empfohlen

**Response:**
```json
{
  "success": true,
  "data": {
    "url": "https://example.com/images/place1-3.jpg",
    "thumbnailUrl": "https://example.com/images/thumbnails/place1-3.jpg"
  }
}
```

### 6.2 Bild-Speicherung
- Cloud-Storage (z.B. AWS S3, Cloudinary, etc.) oder
- Lokaler Dateisystem mit CDN
- URLs in der Datenbank speichern (nicht die Binärdaten)

---

## 7. Performance & Caching

### 7.1 Empfohlenes Caching
- `GET /api/places`: Cache 5 Minuten
- `GET /api/places/:id`: Cache 10 Minuten
- `GET /api/places/:id/availability`: Kein Cache (Echtzeit-Daten)
- Invalidierung bei Änderungen an Places oder neuen Buchungen

### 7.2 Pagination
- Alle Listen-Endpoints sollten Pagination unterstützen
- Default: 20 Items pro Seite
- Max: 100 Items pro Seite

---

## 8. Testing-Anforderungen

### 8.1 Unit-Tests
- Preisberechnung
- Verfügbarkeitsprüfung
- Status-Übergänge
- Datumsvalidierung

### 8.2 Integration-Tests
- Buchungsprozess: Erstellen → Bestätigen → Stornieren
- Überlappende Buchungen verhindern
- Authentifizierung & Autorisierung

---

## 9. Migration & Daten-Seeding

### 9.1 Seed-Daten
Für Entwicklung und Testing sollten Beispiel-Daten erstellt werden:

**4 Beispiel-Orte:**
1. Kulturraum Ottbergen (100 Personen, 250€/Tag)
2. Gemeindesaal St. Marien (60 Personen, 150€/Tag)
3. Dorfgemeinschaftshaus (40 Personen, 120€/Tag)
4. Scheune am Waldrand (80 Personen, 300€/Tag)

**Für jeden Ort:**
- 3-5 Features hinzufügen
- GPS-Koordinaten für Ottbergen-Region
- Mindestens 1 Beispielbild (Platzhalter-URLs ok)
- 1 Anbieter zuweisen

**2 Beispiel-Buchungen:**
- 1 mit Status `confirmed`
- 1 mit Status `upcoming`

---

## 10. Offene Fragen & Weitere Features

### Für spätere Implementierung (Nice-to-have):
1. **Bewertungssystem:** User können Orte nach Buchung bewerten
2. **Favoriten/Merkliste:** User können Orte speichern
3. **Nachrichten-System:** Direkter Chat zwischen User und Anbieter
4. **Zahlungsintegration:** Stripe oder PayPal für direkte Zahlungen
5. **Mehrsprachigkeit:** i18n-Unterstützung für API-Responses
6. **Rabattcodes:** Promocodes für Buchungen
7. **Wiederholende Buchungen:** Automatische Buchungen (z.B. jeden Freitag)
8. **Angebote:** Anbieter können zeitlich begrenzte Angebote erstellen

---

## 11. Zusammenfassung der kritischen Endpoints

**Für den Buchungsprozess JETZT notwendig:**

1. ✅ `GET /api/places` - Liste mit Suchfunktion
2. ✅ `GET /api/places/:id` - Details mit Features und Provider
3. ✅ `GET /api/places/:id/availability` - Verfügbarkeitsprüfung
4. ✅ `POST /api/bookings` - Buchung erstellen
5. ✅ `GET /api/bookings` - Eigene Buchungen anzeigen
6. ✅ `GET /api/bookings/:id` - Buchungsdetails
7. ✅ `PATCH /api/bookings/:id/cancel` - Buchung stornieren

**Für Anbieter (später):**
8. ⏳ `PATCH /api/bookings/:id/confirm` - Buchung bestätigen
9. ⏳ `GET /api/providers/:id/bookings` - Eingehende Buchungsanfragen

---

## 12. Frontend-Backend Schnittstelle

### 12.1 Erwartete Response-Struktur
Das Frontend erwartet konsistente API-Responses im folgenden Format:

**Erfolgreiche Response:**
```json
{
  "success": true,
  "data": { ... },
  "message": "Optional: Erfolgsmeldung"
}
```

**Fehler-Response:**
```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Benutzerfreundliche Fehlermeldung",
    "details": { }
  }
}
```

### 12.2 Datum-Format
- **API sendet:** ISO 8601 (`YYYY-MM-DD` oder `YYYY-MM-DDTHH:mm:ssZ`)
- **Frontend sendet:** ISO 8601 (`YYYY-MM-DD`)
- **Anzeige im Frontend:** Deutsches Format (`DD.MM.YYYY` oder `Tag, DD. Monat YYYY`)

---

## Kontakt & Fragen

Bei Fragen zur Frontend-Implementierung oder zur API-Spezifikation, bitte im gemeinsamen Repository ein Issue erstellen oder im Team-Chat nachfragen.

**Frontend TypeScript Types:** Siehe `src/types/place.ts` für die vollständigen Interface-Definitionen.

---

**Erstellt am:** 19.11.2025
**Version:** 1.0
**Status:** Initial Draft
