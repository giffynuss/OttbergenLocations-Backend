╔════════════════════════════════════════════════╗
║  Backend Validierungs-Tests                    ║
╔════════════════════════════════════════════════╗

--- validateName Tests ---
✓ PASS: Name mit 2 Zeichen ist gültig
✓ PASS: Name mit 1 Zeichen ist ungültig
✓ PASS: Leerer Name ist ungültig
✓ PASS: Name mit nur Leerzeichen ist ungültig

--- validateEmail Tests ---
✓ PASS: Gültige E-Mail wird akzeptiert
✓ PASS: Ungültige E-Mail ohne @ wird abgelehnt
✓ PASS: E-Mail ohne Domain wird abgelehnt
✓ PASS: E-Mail ohne Lokalpart wird abgelehnt
✓ PASS: Leere E-Mail wird abgelehnt

--- validatePhone Tests ---
✓ PASS: Telefon mit 6 Ziffern ist gültig
✓ PASS: Telefon mit + und Leerzeichen ist gültig
✓ PASS: Telefon mit Klammern ist gültig
✓ PASS: Telefon mit 5 Zeichen ist ungültig
✓ PASS: Telefon mit Buchstaben ist ungültig
✓ PASS: Leeres Telefon ist ungültig

--- validateZipCode Tests ---
✓ PASS: PLZ mit 5 Ziffern ist gültig
✓ PASS: PLZ mit 4 Ziffern ist ungültig
✓ PASS: PLZ mit 6 Ziffern ist ungültig
✓ PASS: PLZ mit Buchstaben ist ungültig
✓ PASS: Leere PLZ ist ungültig

--- validatePassword Tests ---
✓ PASS: Gültiges Passwort (10 Zeichen, Groß, Klein, Zahl, Sonder) wird akzeptiert
✓ PASS: Passwort ohne Sonderzeichen wird abgelehnt
✓ PASS: Passwort ohne Großbuchstaben wird abgelehnt
✓ PASS: Passwort ohne Kleinbuchstaben wird abgelehnt
✓ PASS: Passwort ohne Zahlen wird abgelehnt
✓ PASS: Passwort mit nur 9 Zeichen wird abgelehnt
✓ PASS: Passwort ohne Sonderzeichen wird abgelehnt

--- validateGender Tests ---
✓ PASS: Gender "herr" ist gültig
✓ PASS: Gender "frau" ist gültig
✓ PASS: Gender "HERR" (Großbuchstaben) ist gültig
✓ PASS: Gender "divers" ist ungültig
✓ PASS: Leeres Gender ist ungültig

--- validateRegistrationData Tests ---
✓ PASS: Vollständige gültige Registrierungsdaten werden akzeptiert
✓ PASS: Registrierung mit zu kurzem Vornamen wird abgelehnt
✓ PASS: Registrierung mit ungültiger E-Mail wird abgelehnt
✓ PASS: Registrierung mit ungültiger PLZ wird abgelehnt
✓ PASS: Registrierung mit schwachem Passwort wird abgelehnt
✓ PASS: Registrierung mit nicht übereinstimmenden Passwörtern wird abgelehnt

==================================================
Test Summary:
Total: 38 | Passed: 38 | Failed: 0
==================================================

















╔════════════════════════════════════════════════════════════════════╗
║  Direkter Register-Endpunkt Test                                   ║
╔════════════════════════════════════════════════════════════════════╗

--- Test 1: Ungültiges Passwort (zu kurz) ---
✓ PASS: Validierung erkannte ungültiges Passwort
  Fehler: Passwort muss mindestens 10 Zeichen lang sein. Passwort muss mindestens einen Großbuchstaben enthalten. Passwort muss mindestens eine Zahl enthalten. Passwort muss mindestens ein Sonderzeichen enthalten (!@#$%^&*+(),.?":{}|<>_-)

--- Test 2: Ungültige PLZ (4 Ziffern statt 5) ---
✓ PASS: Validierung erkannte ungültige PLZ
  Fehler: PLZ muss 5 Ziffern haben

--- Test 3: Zu kurzer Vorname (1 Zeichen) ---
✓ PASS: Validierung erkannte zu kurzen Vornamen
  Fehler: Vorname muss mindestens 2 Zeichen lang sein

--- Test 4: Ungültige E-Mail ---
✓ PASS: Validierung erkannte ungültige E-Mail
  Fehler: Bitte geben Sie eine gültige E-Mail-Adresse ein

--- Test 5: Zu kurze Telefonnummer (< 6 Zeichen) ---
✓ PASS: Validierung erkannte ungültige Telefonnummer
  Fehler: Bitte geben Sie eine gültige Telefonnummer ein

--- Test 6: Vollständig gültige Registrierungsdaten ---
✓ PASS: Gültige Daten werden akzeptiert

╔════════════════════════════════════════════════════════════════════╗
║  Alle direkten Validierungstests abgeschlossen                     ║
╔════════════════════════════════════════════════════════════════════╗