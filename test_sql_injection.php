<?php
/**
 * SQL Injection Security Test Suite
 * ===================================
 * Testet alle API-Endpoints auf SQL-Injection-Vulnerabilities
 *
 * WICHTIG: Nur für Sicherheitstests in Entwicklungsumgebung verwenden!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Basis-URL des Backends
$baseUrl = 'http://localhost/OttbergenLocations-Backend/api';

// Test-Payloads für SQL-Injection
$sqlInjectionPayloads = [
    // Classic SQL Injection
    "' OR '1'='1",
    "' OR 1=1--",
    "' OR 'a'='a",
    "admin'--",
    "admin' #",
    "' OR 1=1#",

    // Union-based
    "' UNION SELECT NULL--",
    "' UNION SELECT NULL,NULL,NULL--",

    // Time-based blind
    "' AND SLEEP(5)--",
    "' OR SLEEP(5)--",

    // Boolean-based blind
    "' AND '1'='1",
    "' AND '1'='2",

    // Stacked queries
    "'; DROP TABLE users--",
    "'; DELETE FROM bookings--",

    // MySQL specific
    "' OR 1=1 LIMIT 1--",
    "admin' OR '1'='1'/*",
];

// Test-Counter
$totalTests = 0;
$vulnerableTests = 0;
$results = [];

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║        SQL INJECTION SECURITY TEST SUITE                      ║\n";
echo "║        Backend: OttbergenLocations-Backend                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

/**
 * Hilfsfunktion: HTTP-Request senden
 */
function sendRequest($url, $method = 'POST', $data = null, $headers = []) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        $headers[] = 'Content-Type: application/json';
    }

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => $response,
        'json' => json_decode($response, true)
    ];
}

/**
 * Test 1: Login Endpoint - Email Field
 */
echo "\n[TEST 1] Login Endpoint - Email Field SQL Injection\n";
echo str_repeat("─", 65) . "\n";

foreach ($sqlInjectionPayloads as $payload) {
    $totalTests++;

    $response = sendRequest("$baseUrl/auth/login.php", 'POST', [
        'email' => $payload,
        'password' => 'testpassword'
    ]);

    // Vulnerability Check
    $isVulnerable = false;
    $reason = '';

    // Check 1: Erfolgreicher Login trotz ungültiger Email
    if ($response['code'] === 200 && isset($response['json']['success']) && $response['json']['success'] === true) {
        $isVulnerable = true;
        $reason = 'Login erfolgreich mit SQL-Payload';
    }

    // Check 2: SQL-Fehler in Response
    if (stripos($response['body'], 'SQL') !== false ||
        stripos($response['body'], 'mysql') !== false ||
        stripos($response['body'], 'syntax error') !== false) {
        $isVulnerable = true;
        $reason = 'SQL-Fehler in Response sichtbar';
    }

    // Check 3: Unerwartete 500 Errors (könnte auf SQL-Fehler hindeuten)
    if ($response['code'] === 500 && stripos($response['body'], 'PDO') !== false) {
        $isVulnerable = true;
        $reason = 'PDO-Fehler bei SQL-Payload';
    }

    if ($isVulnerable) {
        $vulnerableTests++;
        echo "❌ VULNERABLE: Payload: " . substr($payload, 0, 30) . "\n";
        echo "   Reason: $reason\n";
        echo "   HTTP: {$response['code']}, Response: " . substr($response['body'], 0, 100) . "\n";

        $results[] = [
            'endpoint' => '/auth/login.php',
            'field' => 'email',
            'payload' => $payload,
            'vulnerable' => true,
            'reason' => $reason
        ];
    } else {
        echo "✓ SAFE: Payload: " . substr($payload, 0, 40) . " → HTTP {$response['code']}\n";
    }
}

/**
 * Test 2: Register Endpoint - Email Field
 */
echo "\n\n[TEST 2] Register Endpoint - Email Field SQL Injection\n";
echo str_repeat("─", 65) . "\n";

foreach ($sqlInjectionPayloads as $payload) {
    $totalTests++;

    $response = sendRequest("$baseUrl/auth/register.php", 'POST', [
        'firstName' => 'Test',
        'lastName' => 'User',
        'gender' => 'herr',
        'email' => $payload,
        'password' => 'Password123!',
        'phone' => '+49 123 456789',
        'street' => 'Teststr',
        'houseNumber' => '1',
        'zipCode' => '12345',
        'city' => 'Berlin'
    ]);

    $isVulnerable = false;
    $reason = '';

    if ($response['code'] === 201 && isset($response['json']['success']) && $response['json']['success'] === true) {
        $isVulnerable = true;
        $reason = 'Registrierung erfolgreich mit SQL-Payload';
    }

    if (stripos($response['body'], 'SQL') !== false || stripos($response['body'], 'mysql') !== false) {
        $isVulnerable = true;
        $reason = 'SQL-Fehler in Response';
    }

    if ($isVulnerable) {
        $vulnerableTests++;
        echo "❌ VULNERABLE: Payload: " . substr($payload, 0, 30) . "\n";
        $results[] = [
            'endpoint' => '/auth/register.php',
            'field' => 'email',
            'payload' => $payload,
            'vulnerable' => true,
            'reason' => $reason
        ];
    } else {
        echo "✓ SAFE: Payload: " . substr($payload, 0, 40) . " → HTTP {$response['code']}\n";
    }
}

/**
 * Test 3: Places Endpoint - GET Parameter (id)
 */
echo "\n\n[TEST 3] Places Detail Endpoint - ID Parameter SQL Injection\n";
echo str_repeat("─", 65) . "\n";

$idPayloads = [
    "1' OR '1'='1",
    "1 OR 1=1--",
    "1' UNION SELECT NULL--",
    "999' AND SLEEP(5)--"
];

foreach ($idPayloads as $payload) {
    $totalTests++;

    $response = sendRequest("$baseUrl/places/get.php?id=" . urlencode($payload), 'GET');

    $isVulnerable = false;
    $reason = '';

    if ($response['code'] === 200 && isset($response['json']['success']) && $response['json']['success'] === true) {
        $isVulnerable = true;
        $reason = 'Erfolgreiche Response mit SQL-Payload';
    }

    if (stripos($response['body'], 'SQL') !== false || stripos($response['body'], 'mysql') !== false) {
        $isVulnerable = true;
        $reason = 'SQL-Fehler sichtbar';
    }

    if ($isVulnerable) {
        $vulnerableTests++;
        echo "❌ VULNERABLE: Payload: $payload\n";
        $results[] = [
            'endpoint' => '/places/get.php',
            'field' => 'id',
            'payload' => $payload,
            'vulnerable' => true,
            'reason' => $reason
        ];
    } else {
        echo "✓ SAFE: Payload: $payload → HTTP {$response['code']}\n";
    }
}

/**
 * Test 4: Bookings Create - Numeric Fields
 */
echo "\n\n[TEST 4] Bookings Create Endpoint - placeId SQL Injection\n";
echo str_repeat("─", 65) . "\n";

foreach ($idPayloads as $payload) {
    $totalTests++;

    $response = sendRequest("$baseUrl/bookings/create.php", 'POST', [
        'placeId' => $payload,
        'checkIn' => '2025-12-01',
        'checkOut' => '2025-12-05',
        'guests' => 50,
        'paymentMethod' => 'cash',
        'userInfo' => [
            'gender' => 'herr',
            'firstName' => 'Test',
            'lastName' => 'User',
            'email' => 'test@example.com',
            'phone' => '+49 123 456'
        ]
    ]);

    $isVulnerable = false;
    $reason = '';

    if ($response['code'] === 201 && isset($response['json']['success']) && $response['json']['success'] === true) {
        $isVulnerable = true;
        $reason = 'Buchung erfolgreich mit SQL-Payload';
    }

    if (stripos($response['body'], 'SQL') !== false || stripos($response['body'], 'PDOException') !== false) {
        $isVulnerable = true;
        $reason = 'SQL-Fehler in Response';
    }

    if ($isVulnerable) {
        $vulnerableTests++;
        echo "❌ VULNERABLE: Payload: $payload\n";
        $results[] = [
            'endpoint' => '/bookings/create.php',
            'field' => 'placeId',
            'payload' => $payload,
            'vulnerable' => true,
            'reason' => $reason
        ];
    } else {
        echo "✓ SAFE: Payload: $payload → HTTP {$response['code']}\n";
    }
}

/**
 * Test 5: Token-based Endpoints
 */
echo "\n\n[TEST 5] Token-based Endpoints - Token Parameter SQL Injection\n";
echo str_repeat("─", 65) . "\n";

$tokenPayloads = [
    "abc' OR '1'='1",
    "test' UNION SELECT NULL--",
];

foreach ($tokenPayloads as $payload) {
    $totalTests++;

    $response = sendRequest("$baseUrl/bookings/cancel-token.php?token=" . urlencode($payload), 'GET');

    $isVulnerable = false;
    $reason = '';

    if (stripos($response['body'], 'SQL') !== false || stripos($response['body'], 'mysql') !== false) {
        $isVulnerable = true;
        $reason = 'SQL-Fehler in Response';
    }

    // Erfolgreiches Cancelling trotz ungültigem Token
    if ($response['code'] === 200 && stripos($response['body'], 'storniert') !== false) {
        $isVulnerable = true;
        $reason = 'Erfolgreiche Aktion mit SQL-Payload';
    }

    if ($isVulnerable) {
        $vulnerableTests++;
        echo "❌ VULNERABLE: Payload: $payload\n";
        $results[] = [
            'endpoint' => '/bookings/cancel-token.php',
            'field' => 'token',
            'payload' => $payload,
            'vulnerable' => true,
            'reason' => $reason
        ];
    } else {
        echo "✓ SAFE: Payload: $payload → HTTP {$response['code']}\n";
    }
}

// ==================== FINAL REPORT ====================

echo "\n\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                    TEST SUMMARY                               ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "Total Tests: $totalTests\n";
echo "Vulnerable Tests: $vulnerableTests\n";
echo "Safe Tests: " . ($totalTests - $vulnerableTests) . "\n\n";

if ($vulnerableTests > 0) {
    echo "⚠️  WARNING: $vulnerableTests potential SQL injection vulnerabilities found!\n\n";
    echo "VULNERABLE ENDPOINTS:\n";
    echo str_repeat("─", 65) . "\n";

    foreach ($results as $result) {
        if ($result['vulnerable']) {
            echo "❌ {$result['endpoint']} ({$result['field']})\n";
            echo "   Payload: {$result['payload']}\n";
            echo "   Reason: {$result['reason']}\n\n";
        }
    }
} else {
    echo "✅ EXCELLENT: No SQL injection vulnerabilities detected!\n";
    echo "   All endpoints properly use prepared statements.\n";
}

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                  RECOMMENDATIONS                              ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

echo "✓ Continue using PDO prepared statements\n";
echo "✓ Validate and sanitize all user inputs\n";
echo "✓ Use parameterized queries for all database operations\n";
echo "✓ Never concatenate user input directly into SQL queries\n";
echo "✓ Implement proper error handling (don't expose SQL errors)\n";
echo "✓ Regular security audits\n\n";
?>
