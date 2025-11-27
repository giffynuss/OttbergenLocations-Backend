╔═══════════════════════════════════════════════════════════════╗
║        SQL INJECTION SECURITY TEST SUITE                      ║
║        Backend: OttbergenLocations-Backend                    ║
╚═══════════════════════════════════════════════════════════════╝


[TEST 1] Login Endpoint - Email Field SQL Injection
─────────────────────────────────────────────────────────────────
✓ SAFE: Payload: ' OR '1'='1 → HTTP 400
✓ SAFE: Payload: ' OR 1=1-- → HTTP 400
✓ SAFE: Payload: ' OR 'a'='a → HTTP 400
✓ SAFE: Payload: admin'-- → HTTP 400
✓ SAFE: Payload: admin' # → HTTP 400
✓ SAFE: Payload: ' OR 1=1# → HTTP 400
✓ SAFE: Payload: ' UNION SELECT NULL-- → HTTP 400
✓ SAFE: Payload: ' UNION SELECT NULL,NULL,NULL-- → HTTP 400
✓ SAFE: Payload: ' AND SLEEP(5)-- → HTTP 400
✓ SAFE: Payload: ' OR SLEEP(5)-- → HTTP 400
✓ SAFE: Payload: ' AND '1'='1 → HTTP 400
✓ SAFE: Payload: ' AND '1'='2 → HTTP 400
✓ SAFE: Payload: '; DROP TABLE users-- → HTTP 400
✓ SAFE: Payload: '; DELETE FROM bookings-- → HTTP 400
✓ SAFE: Payload: ' OR 1=1 LIMIT 1-- → HTTP 400
✓ SAFE: Payload: admin' OR '1'='1'/* → HTTP 400


[TEST 2] Register Endpoint - Email Field SQL Injection
─────────────────────────────────────────────────────────────────
✓ SAFE: Payload: ' OR '1'='1 → HTTP 400
✓ SAFE: Payload: ' OR 1=1-- → HTTP 400
✓ SAFE: Payload: ' OR 'a'='a → HTTP 400
✓ SAFE: Payload: admin'-- → HTTP 400
✓ SAFE: Payload: admin' # → HTTP 400
✓ SAFE: Payload: ' OR 1=1# → HTTP 400
✓ SAFE: Payload: ' UNION SELECT NULL-- → HTTP 400
✓ SAFE: Payload: ' UNION SELECT NULL,NULL,NULL-- → HTTP 400
✓ SAFE: Payload: ' AND SLEEP(5)-- → HTTP 400
✓ SAFE: Payload: ' OR SLEEP(5)-- → HTTP 400
✓ SAFE: Payload: ' AND '1'='1 → HTTP 400
✓ SAFE: Payload: ' AND '1'='2 → HTTP 400
✓ SAFE: Payload: '; DROP TABLE users-- → HTTP 400
✓ SAFE: Payload: '; DELETE FROM bookings-- → HTTP 400
✓ SAFE: Payload: ' OR 1=1 LIMIT 1-- → HTTP 400
✓ SAFE: Payload: admin' OR '1'='1'/* → HTTP 400


[TEST 3] Places Detail Endpoint - ID Parameter SQL Injection
─────────────────────────────────────────────────────────────────
❌ VULNERABLE: Payload: 1' OR '1'='1
❌ VULNERABLE: Payload: 1 OR 1=1--
❌ VULNERABLE: Payload: 1' UNION SELECT NULL--
✓ SAFE: Payload: 999' AND SLEEP(5)-- → HTTP 404


[TEST 4] Bookings Create Endpoint - placeId SQL Injection
─────────────────────────────────────────────────────────────────
❌ VULNERABLE: Payload: 1' OR '1'='1
✓ SAFE: Payload: 1 OR 1=1-- → HTTP 409
✓ SAFE: Payload: 1' UNION SELECT NULL-- → HTTP 409
✓ SAFE: Payload: 999' AND SLEEP(5)-- → HTTP 404


[TEST 5] Token-based Endpoints - Token Parameter SQL Injection
─────────────────────────────────────────────────────────────────
✓ SAFE: Payload: abc' OR '1'='1 → HTTP 404
✓ SAFE: Payload: test' UNION SELECT NULL-- → HTTP 404


╔═══════════════════════════════════════════════════════════════╗
║                    TEST SUMMARY                               ║
╚═══════════════════════════════════════════════════════════════╝

Total Tests: 42
Vulnerable Tests: 4
Safe Tests: 38

⚠️  WARNING: 4 potential SQL injection vulnerabilities found!

VULNERABLE ENDPOINTS:
─────────────────────────────────────────────────────────────────
❌ /places/get.php (id)
   Payload: 1' OR '1'='1
   Reason: Erfolgreiche Response mit SQL-Payload

❌ /places/get.php (id)
   Payload: 1 OR 1=1--
   Reason: Erfolgreiche Response mit SQL-Payload

❌ /places/get.php (id)
   Payload: 1' UNION SELECT NULL--
   Reason: Erfolgreiche Response mit SQL-Payload

❌ /bookings/create.php (placeId)
   Payload: 1' OR '1'='1
   Reason: Buchung erfolgreich mit SQL-Payload


╔═══════════════════════════════════════════════════════════════╗
║                  RECOMMENDATIONS                              ║
╚═══════════════════════════════════════════════════════════════╝

✓ Continue using PDO prepared statements
✓ Validate and sanitize all user inputs
✓ Use parameterized queries for all database operations
✓ Never concatenate user input directly into SQL queries
✓ Implement proper error handling (don't expose SQL errors)
✓ Regular security audits