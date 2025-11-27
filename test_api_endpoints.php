<?php
/**
 * Integration-Tests für API-Endpunkte
 * Ausführen: php test_api_endpoints.php
 *
 * Testet die Validierung an den API-Endpunkten:
 * - POST /api/auth/register
 * - POST /api/auth/login
 * - POST /api/bookings/create
 */

class ApiTest {
    private $passed = 0;
    private $failed = 0;
    private $baseUrl = 'http://localhost/OttbergenLocations-Backend';

    public function assert($condition, $testName, $details = '') {
        if ($condition) {
            $this->passed++;
            echo "✓ PASS: {$testName}\n";
        } else {
            $this->failed++;
            echo "✗ FAIL: {$testName}";
            if ($details) {
                echo "\n  Details: {$details}";
            }
            echo "\n";
        }
    }

    public function summary() {
        $total = $this->passed + $this->failed;
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "Test Summary:\n";
        echo "Total: {$total} | Passed: {$this->passed} | Failed: {$this->failed}\n";
        echo str_repeat("=", 70) . "\n";
        return $this->failed === 0;
    }

    private function makeRequest($endpoint, $data) {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'httpCode' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    public function testRegisterEndpoint() {
        echo "\n--- Register Endpoint Tests ---\n";

        // Test 1: Gültige Registrierung
        $validData = [
            'firstName' => 'Max',
            'lastName' => 'Mustermann',
            'gender' => 'herr',
            'email' => 'test_' . time() . '@example.com',
            'phone' => '0123456789',
            'street' => 'Hauptstraße',
            'houseNumber' => '123',
            'zipCode' => '12345',
            'city' => 'Berlin',
            'password' => 'Test1234!@',
            'confirmPassword' => 'Test1234!@'
        ];

        $response = $this->makeRequest('/api/auth/register', $validData);
        $this->assert(
            $response['httpCode'] === 201 || $response['httpCode'] === 409,
            'Gültige Registrierung wird akzeptiert oder E-Mail existiert bereits',
            "HTTP {$response['httpCode']}"
        );

        // Test 2: Ungültiges Passwort (zu kurz)
        $invalidData = $validData;
        $invalidData['email'] = 'test2_' . time() . '@example.com';
        $invalidData['password'] = 'short';
        $invalidData['confirmPassword'] = 'short';

        $response = $this->makeRequest('/api/auth/register', $invalidData);
        $this->assert(
            $response['httpCode'] === 400,
            'Registrierung mit zu kurzem Passwort wird abgelehnt (400)',
            "HTTP {$response['httpCode']}, Body: " . json_encode($response['body'])
        );

        // Test 3: Ungültige E-Mail
        $invalidData = $validData;
        $invalidData['email'] = 'invalid-email';

        $response = $this->makeRequest('/api/auth/register', $invalidData);
        $this->assert(
            $response['httpCode'] === 400,
            'Registrierung mit ungültiger E-Mail wird abgelehnt (400)',
            "HTTP {$response['httpCode']}"
        );

        // Test 4: Ungültige PLZ
        $invalidData = $validData;
        $invalidData['email'] = 'test4_' . time() . '@example.com';
        $invalidData['zipCode'] = '1234';

        $response = $this->makeRequest('/api/auth/register', $invalidData);
        $this->assert(
            $response['httpCode'] === 400,
            'Registrierung mit ungültiger PLZ (4 Ziffern) wird abgelehnt (400)',
            "HTTP {$response['httpCode']}"
        );

        // Test 5: Zu kurzer Vorname
        $invalidData = $validData;
        $invalidData['email'] = 'test5_' . time() . '@example.com';
        $invalidData['firstName'] = 'M';

        $response = $this->makeRequest('/api/auth/register', $invalidData);
        $this->assert(
            $response['httpCode'] === 400,
            'Registrierung mit zu kurzem Vornamen wird abgelehnt (400)',
            "HTTP {$response['httpCode']}"
        );

        // Test 6: Ungültige Telefonnummer
        $invalidData = $validData;
        $invalidData['email'] = 'test6_' . time() . '@example.com';
        $invalidData['phone'] = '123';

        $response = $this->makeRequest('/api/auth/register', $invalidData);
        $this->assert(
            $response['httpCode'] === 400,
            'Registrierung mit zu kurzer Telefonnummer wird abgelehnt (400)',
            "HTTP {$response['httpCode']}"
        );
    }

    public function testLoginEndpoint() {
        echo "\n--- Login Endpoint Tests ---\n";

        // Test 1: Ungültige E-Mail
        $invalidData = [
            'email' => 'invalid-email',
            'password' => 'anypassword'
        ];

        $response = $this->makeRequest('/api/auth/login', $invalidData);
        $this->assert(
            $response['httpCode'] === 400,
            'Login mit ungültiger E-Mail wird abgelehnt (400)',
            "HTTP {$response['httpCode']}"
        );

        // Test 2: Leeres Passwort
        $invalidData = [
            'email' => 'test@example.com',
            'password' => ''
        ];

        $response = $this->makeRequest('/api/auth/login', $invalidData);
        $this->assert(
            $response['httpCode'] === 400,
            'Login mit leerem Passwort wird abgelehnt (400)',
            "HTTP {$response['httpCode']}"
        );

        // Test 3: Nicht existierende E-Mail
        $validData = [
            'email' => 'nonexistent_' . time() . '@example.com',
            'password' => 'Test1234!@'
        ];

        $response = $this->makeRequest('/api/auth/login', $validData);
        $this->assert(
            $response['httpCode'] === 200,
            'Login mit nicht existierendem Benutzer gibt Fehlermeldung',
            "HTTP {$response['httpCode']}, Success: " . ($response['body']['success'] ? 'true' : 'false')
        );
        $this->assert(
            !($response['body']['success'] ?? true),
            'Login mit nicht existierendem Benutzer hat success=false'
        );
    }

    public function testBookingEndpoint() {
        echo "\n--- Booking Endpoint Tests ---\n";

        // Test 1: Ungültige userInfo (fehlende Pflichtfelder)
        $invalidData = [
            'placeId' => 1,
            'checkIn' => date('Y-m-d', strtotime('+1 day')),
            'checkOut' => date('Y-m-d', strtotime('+3 days')),
            'guests' => 2,
            'paymentMethod' => 'cash',
            'userInfo' => [
                'firstName' => 'M', // Zu kurz
                'lastName' => 'Mustermann',
                'gender' => 'herr',
                'email' => 'test@example.com',
                'phone' => '123' // Zu kurz
            ]
        ];

        $response = $this->makeRequest('/api/bookings/create', $invalidData);
        $this->assert(
            $response['httpCode'] === 400 || $response['httpCode'] === 404,
            'Buchung mit ungültigen userInfo wird abgelehnt (400) oder Ort nicht gefunden (404)',
            "HTTP {$response['httpCode']}"
        );

        // Test 2: Ungültige PLZ bei Überweisung
        $invalidData = [
            'placeId' => 1,
            'checkIn' => date('Y-m-d', strtotime('+1 day')),
            'checkOut' => date('Y-m-d', strtotime('+3 days')),
            'guests' => 2,
            'paymentMethod' => 'transfer',
            'userInfo' => [
                'firstName' => 'Max',
                'lastName' => 'Mustermann',
                'gender' => 'herr',
                'email' => 'test@example.com',
                'phone' => '0123456789',
                'street' => 'Hauptstraße',
                'houseNumber' => '123',
                'postalCode' => '1234', // Ungültig: nur 4 Ziffern
                'city' => 'Berlin'
            ]
        ];

        $response = $this->makeRequest('/api/bookings/create', $invalidData);
        $this->assert(
            $response['httpCode'] === 400 || $response['httpCode'] === 404,
            'Buchung mit ungültiger PLZ bei Überweisung wird abgelehnt (400)',
            "HTTP {$response['httpCode']}"
        );

        echo "\n  ℹ Info: Booking-Tests sind limitiert ohne aktive Datenbank und gültige placeId\n";
    }
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║  Backend API Integration-Tests                                     ║\n";
echo "╔════════════════════════════════════════════════════════════════════╗\n";

$test = new ApiTest();

// Warnung wenn Server nicht läuft
echo "\n⚠ Hinweis: Diese Tests benötigen einen laufenden XAMPP/Apache Server!\n";
echo "  Stelle sicher, dass Apache läuft bevor du diese Tests ausführst.\n";

// Prüfe ob Server erreichbar ist
$ch = curl_init('http://localhost');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
curl_setopt($ch, CURLOPT_NOBODY, true);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 0) {
    echo "\n✗ FEHLER: Apache Server nicht erreichbar auf http://localhost\n";
    echo "  Bitte starte Apache in XAMPP und führe die Tests erneut aus.\n\n";
    exit(1);
}

echo "\n✓ Server erreichbar, starte Tests...\n";

$test->testRegisterEndpoint();
$test->testLoginEndpoint();
$test->testBookingEndpoint();

$allPassed = $test->summary();
exit($allPassed ? 0 : 1);
