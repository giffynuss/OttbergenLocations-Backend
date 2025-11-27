# Backend Validierungs-Anforderungen

## Datum: 2025-11-27

## Zusammenfassung
Das Frontend wurde refaktorisiert, um eine zentrale Validierungslogik zu verwenden. Alle Validierungen wurden standardisiert und redundanter Code entfernt. Das Backend sollte die gleichen Validierungsregeln implementieren, um konsistente Benutzererfahrung zu gewährleisten.

---

## Kritische Änderung: Passwort-Validierung

### ⚠️ WICHTIG: Login-Passwort Validierung anpassen

**Problem:**
- Das Frontend akzeptiert jetzt **konsistent** nur Passwörter mit **mindestens 10 Zeichen** (sowohl bei Registrierung als auch Login)
- Vorher: Login akzeptierte 6+ Zeichen, Registrierung forderte 10+ Zeichen
- Dies könnte zu Inkonsistenzen mit dem Backend führen

**Aktion erforderlich:**
1. **Prüfe die Backend Login-Validierung**:
   - Stellt das Backend sicher, dass die Passwort-Länge beim Login korrekt überprüft wird?
   - Es sollte KEINE Mindestlängen-Validierung beim Login geben (da bereits registrierte Passwörter validiert wurden)
   - ODER: Falls doch eine Validierung existiert, sollte sie mit der Registrierung übereinstimmen

2. **Registrierungs-Validierung im Backend sollte prüfen**:
   - Mindestens 10 Zeichen
   - Mindestens 1 Kleinbuchstabe
   - Mindestens 1 Großbuchstabe
   - Mindestens 1 Zahl
   - Mindestens 1 Sonderzeichen aus: `!@#$%^&*+(),.?":{}|<>_-`

---

## Standardisierte Frontend-Validierungsregeln

### 1. **Vorname & Nachname**
```
- Pflichtfeld
- Mindestens 2 Zeichen
```

### 2. **Anrede (Gender)**
```
- Pflichtfeld
- Erlaubte Werte: "herr", "frau"
```

### 3. **E-Mail**
```
- Pflichtfeld
- Regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
- Fehlermeldung: "Bitte geben Sie eine gültige E-Mail-Adresse ein"
```

### 4. **Telefon**
```
- Pflichtfeld
- Regex: /^[\d\s+()-]+$/
- Mindestlänge: 6 Zeichen
- Fehlermeldung: "Bitte geben Sie eine gültige Telefonnummer ein"
```

### 5. **Passwort (Registrierung)**
```
- Pflichtfeld
- Mindestens 10 Zeichen
- Muss enthalten:
  * Mindestens 1 Kleinbuchstabe: /[a-z]/
  * Mindestens 1 Großbuchstabe: /[A-Z]/
  * Mindestens 1 Zahl: /\d/
  * Mindestens 1 Sonderzeichen: /[!@#$%^&*+(),.?":{}|<>_\-]/
```

### 6. **Passwort-Bestätigung**
```
- Pflichtfeld
- Muss mit Passwort übereinstimmen
```

### 7. **Straße**
```
- Pflichtfeld
- Fehlermeldung: "Dieses Feld ist erforderlich"
```

### 8. **Hausnummer**
```
- Pflichtfeld
- Fehlermeldung: "Hausnummer ist erforderlich"
```

### 9. **PLZ (postalCode / zipCode)**
```
- Pflichtfeld
- Regex: /^\d{5}$/
- Muss genau 5 Ziffern sein
- Fehlermeldung: "PLZ muss 5 Ziffern haben"

Hinweis: Frontend verwendet intern "postalCode", sendet aber "zipCode" an das Backend
```

### 10. **Stadt**
```
- Pflichtfeld
- Fehlermeldung: "Dieses Feld ist erforderlich"
```

---

## API-Endpunkte zu überprüfen

### 1. **POST /api/register** (oder ähnlich)
**Erwartete Felder:**
```json
{
  "firstName": "string (min: 2)",
  "lastName": "string (min: 2)",
  "gender": "herr | frau",
  "email": "string (valid email)",
  "phone": "string (min: 6, format: numbers/spaces/+/-/())",
  "street": "string",
  "houseNumber": "string",
  "zipCode": "string (exactly 5 digits)",
  "city": "string",
  "password": "string (min: 10, complexity requirements)",
  "confirmPassword": "string (must match password)"
}
```

**Backend sollte prüfen:**
- Alle Frontend-Validierungsregeln (siehe oben)
- E-Mail bereits registriert?
- Passwort-Hashing mit sicherem Algorithmus (bcrypt, argon2, etc.)

---

### 2. **POST /api/login** (oder ähnlich)
**Erwartete Felder:**
```json
{
  "email": "string (valid email)",
  "password": "string"
}
```

**Backend sollte prüfen:**
- E-Mail-Format validieren
- **KEINE** Passwort-Längen-Validierung (da bereits bei Registrierung validiert)
- Oder: Falls Validierung existiert, sollte sie >= 10 Zeichen sein
- Passwort-Hash-Vergleich

---

### 3. **POST /api/bookings** (oder ähnlich - Checkout)
**Erwartete Felder:**
```json
{
  "placeId": "number",
  "checkIn": "string (ISO date)",
  "checkOut": "string (ISO date)",
  "guests": "number",
  "paymentMethod": "cash | paypal | transfer | wero",
  "userInfo": {
    "gender": "herr | frau",
    "firstName": "string (min: 2)",
    "lastName": "string (min: 2)",
    "email": "string (valid email)",
    "phone": "string (min: 6)",
    "street": "string (optional, required for transfer)",
    "houseNumber": "string (optional, required for transfer)",
    "postalCode": "string (optional, required for transfer, exactly 5 digits)",
    "city": "string (optional, required for transfer)"
  }
}
```

**Backend sollte prüfen:**
- Alle Basis-Validierungsregeln
- Bei `paymentMethod === 'transfer'`: Adressfelder sind Pflichtfelder
- Buchungszeitraum verfügbar?
- Gästeanzahl <= Kapazität

---

## Empfehlungen für Backend-Validierung

### 1. **Zentrale Validierungslogik**
Erstelle eine zentrale Validierungsklasse/-funktion, die von allen Endpunkten genutzt wird.

### 2. **Konsistente Fehlermeldungen**
Verwende die gleichen Fehlermeldungen wie das Frontend (siehe oben) für bessere UX.

### 3. **HTTP Status Codes**
```
- 400 Bad Request: Validierungsfehler
- 401 Unauthorized: Login fehlgeschlagen
- 409 Conflict: E-Mail bereits registriert
- 422 Unprocessable Entity: Semantische Fehler (z.B. ungültige Daten)
```

### 4. **Fehler-Response Format**
```json
{
  "success": false,
  "message": "Validierung fehlgeschlagen",
  "errors": {
    "email": "Bitte geben Sie eine gültige E-Mail-Adresse ein",
    "password": "Passwort muss mindestens 10 Zeichen lang sein"
  }
}
```

### 5. **Sicherheit**
- **Rate Limiting** auf Login-Endpunkt (z.B. max 5 Versuche pro Minute)
- **CAPTCHA** validieren (falls implementiert)
- **SQL Injection** Schutz durch Prepared Statements
- **XSS** Schutz durch Output Encoding
- **CSRF** Token bei state-changing Operationen

---

## Testing-Checkliste für Backend

- [ ] Registrierung mit gültigem Passwort (10+ Zeichen, Komplexität)
- [ ] Registrierung mit ungültigem Passwort (< 10 Zeichen)
- [ ] Registrierung mit ungültiger E-Mail
- [ ] Registrierung mit bereits existierender E-Mail
- [ ] Login mit korrekten Credentials
- [ ] Login mit falschen Credentials
- [ ] Login mit ungültiger E-Mail-Format
- [ ] Buchung mit vollständigen Daten
- [ ] Buchung mit fehlenden Adressfeldern bei Überweisung
- [ ] PLZ-Validierung (muss genau 5 Ziffern sein)
- [ ] Telefonnummer-Validierung (mindestens 6 Zeichen, korrektes Format)

---

## Nächste Schritte

1. **Backend-Code Review**: Überprüfe alle Validierungen im Backend
2. **Tests schreiben**: Unit- und Integrationstests für alle Validierungsregeln
3. **Passwort-Validierung angleichen**: Stelle sicher, dass Login und Registrierung konsistent sind
4. **API-Dokumentation aktualisieren**: Swagger/OpenAPI mit neuen Validierungsregeln
5. **End-to-End Tests**: Teste den kompletten Flow (Registrierung → Login → Buchung)

---

## Kontakt
Bei Fragen zur Frontend-Validierung, siehe: `src/composables/useValidation.ts`
