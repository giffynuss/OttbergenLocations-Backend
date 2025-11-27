<?php
/**
 * Unit-Tests für Validierungsfunktionen
 * Ausführen: php test_validations.php
 */

require_once __DIR__ . '/helpers/validation.php';

class ValidationTest {
    private $passed = 0;
    private $failed = 0;
    private $tests = [];

    public function assert($condition, $testName, $details = '') {
        if ($condition) {
            $this->passed++;
            echo "✓ PASS: {$testName}\n";
        } else {
            $this->failed++;
            echo "✗ FAIL: {$testName}";
            if ($details) {
                echo " - {$details}";
            }
            echo "\n";
        }
        $this->tests[] = ['name' => $testName, 'passed' => $condition];
    }

    public function summary() {
        $total = $this->passed + $this->failed;
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Test Summary:\n";
        echo "Total: {$total} | Passed: {$this->passed} | Failed: {$this->failed}\n";
        echo str_repeat("=", 50) . "\n";
        return $this->failed === 0;
    }
}

$test = new ValidationTest();

echo "\n";
echo "╔════════════════════════════════════════════════╗\n";
echo "║  Backend Validierungs-Tests                    ║\n";
echo "╔════════════════════════════════════════════════╗\n";
echo "\n";

// ========== validateName Tests ==========
echo "--- validateName Tests ---\n";
$result = validateName('Jo', 'Vorname');
$test->assert($result['valid'], 'Name mit 2 Zeichen ist gültig');

$result = validateName('J', 'Vorname');
$test->assert(!$result['valid'], 'Name mit 1 Zeichen ist ungültig');

$result = validateName('', 'Vorname');
$test->assert(!$result['valid'], 'Leerer Name ist ungültig');

$result = validateName('   ', 'Vorname');
$test->assert(!$result['valid'], 'Name mit nur Leerzeichen ist ungültig');

// ========== validateEmail Tests ==========
echo "\n--- validateEmail Tests ---\n";
$result = validateEmail('test@example.com');
$test->assert($result['valid'], 'Gültige E-Mail wird akzeptiert');

$result = validateEmail('invalid-email');
$test->assert(!$result['valid'], 'Ungültige E-Mail ohne @ wird abgelehnt');

$result = validateEmail('test@');
$test->assert(!$result['valid'], 'E-Mail ohne Domain wird abgelehnt');

$result = validateEmail('@example.com');
$test->assert(!$result['valid'], 'E-Mail ohne Lokalpart wird abgelehnt');

$result = validateEmail('');
$test->assert(!$result['valid'], 'Leere E-Mail wird abgelehnt');

// ========== validatePhone Tests ==========
echo "\n--- validatePhone Tests ---\n";
$result = validatePhone('123456');
$test->assert($result['valid'], 'Telefon mit 6 Ziffern ist gültig');

$result = validatePhone('+49 123 456789');
$test->assert($result['valid'], 'Telefon mit + und Leerzeichen ist gültig');

$result = validatePhone('(030) 12345678');
$test->assert($result['valid'], 'Telefon mit Klammern ist gültig');

$result = validatePhone('12345');
$test->assert(!$result['valid'], 'Telefon mit 5 Zeichen ist ungültig');

$result = validatePhone('abc123');
$test->assert(!$result['valid'], 'Telefon mit Buchstaben ist ungültig');

$result = validatePhone('');
$test->assert(!$result['valid'], 'Leeres Telefon ist ungültig');

// ========== validateZipCode Tests ==========
echo "\n--- validateZipCode Tests ---\n";
$result = validateZipCode('12345');
$test->assert($result['valid'], 'PLZ mit 5 Ziffern ist gültig');

$result = validateZipCode('1234');
$test->assert(!$result['valid'], 'PLZ mit 4 Ziffern ist ungültig');

$result = validateZipCode('123456');
$test->assert(!$result['valid'], 'PLZ mit 6 Ziffern ist ungültig');

$result = validateZipCode('1234a');
$test->assert(!$result['valid'], 'PLZ mit Buchstaben ist ungültig');

$result = validateZipCode('');
$test->assert(!$result['valid'], 'Leere PLZ ist ungültig');

// ========== validatePassword Tests ==========
echo "\n--- validatePassword Tests ---\n";
$result = validatePassword('Test1234!@');
$test->assert($result['valid'], 'Gültiges Passwort (10 Zeichen, Groß, Klein, Zahl, Sonder) wird akzeptiert');

$result = validatePassword('Test1234');
$test->assert(!$result['valid'], 'Passwort ohne Sonderzeichen wird abgelehnt');

$result = validatePassword('test1234!@');
$test->assert(!$result['valid'], 'Passwort ohne Großbuchstaben wird abgelehnt');

$result = validatePassword('TEST1234!@');
$test->assert(!$result['valid'], 'Passwort ohne Kleinbuchstaben wird abgelehnt');

$result = validatePassword('TestABCD!@');
$test->assert(!$result['valid'], 'Passwort ohne Zahlen wird abgelehnt');

$result = validatePassword('Test123!');
$test->assert(!$result['valid'], 'Passwort mit nur 9 Zeichen wird abgelehnt');

$result = validatePassword('Test12345');
$test->assert(!$result['valid'], 'Passwort ohne Sonderzeichen wird abgelehnt');

// ========== validateGender Tests ==========
echo "\n--- validateGender Tests ---\n";
$result = validateGender('herr');
$test->assert($result['valid'], 'Gender "herr" ist gültig');

$result = validateGender('frau');
$test->assert($result['valid'], 'Gender "frau" ist gültig');

$result = validateGender('HERR');
$test->assert($result['valid'], 'Gender "HERR" (Großbuchstaben) ist gültig');

$result = validateGender('divers');
$test->assert(!$result['valid'], 'Gender "divers" ist ungültig');

$result = validateGender('');
$test->assert(!$result['valid'], 'Leeres Gender ist ungültig');

// ========== validateRegistrationData Tests ==========
echo "\n--- validateRegistrationData Tests ---\n";
$validData = [
    'firstName' => 'Max',
    'lastName' => 'Mustermann',
    'gender' => 'herr',
    'email' => 'max@example.com',
    'phone' => '0123456789',
    'street' => 'Hauptstraße',
    'houseNumber' => '123',
    'zipCode' => '12345',
    'city' => 'Berlin',
    'password' => 'Test1234!@',
    'confirmPassword' => 'Test1234!@'
];

$result = validateRegistrationData($validData);
$test->assert($result['valid'], 'Vollständige gültige Registrierungsdaten werden akzeptiert', json_encode($result['errors'] ?? []));

// Test mit ungültigem Vornamen
$invalidData = $validData;
$invalidData['firstName'] = 'M';
$result = validateRegistrationData($invalidData);
$test->assert(!$result['valid'] && isset($result['errors']['firstName']), 'Registrierung mit zu kurzem Vornamen wird abgelehnt');

// Test mit ungültiger E-Mail
$invalidData = $validData;
$invalidData['email'] = 'invalid-email';
$result = validateRegistrationData($invalidData);
$test->assert(!$result['valid'] && isset($result['errors']['email']), 'Registrierung mit ungültiger E-Mail wird abgelehnt');

// Test mit ungültiger PLZ
$invalidData = $validData;
$invalidData['zipCode'] = '1234';
$result = validateRegistrationData($invalidData);
$test->assert(!$result['valid'] && isset($result['errors']['zipCode']), 'Registrierung mit ungültiger PLZ wird abgelehnt');

// Test mit schwachem Passwort
$invalidData = $validData;
$invalidData['password'] = '12345';
$invalidData['confirmPassword'] = '12345';
$result = validateRegistrationData($invalidData);
$test->assert(!$result['valid'] && isset($result['errors']['password']), 'Registrierung mit schwachem Passwort wird abgelehnt');

// Test mit nicht übereinstimmenden Passwörtern
$invalidData = $validData;
$invalidData['confirmPassword'] = 'Different123!@';
$result = validateRegistrationData($invalidData);
$test->assert(!$result['valid'] && isset($result['errors']['confirmPassword']), 'Registrierung mit nicht übereinstimmenden Passwörtern wird abgelehnt');

// Zusammenfassung
$allPassed = $test->summary();
exit($allPassed ? 0 : 1);
