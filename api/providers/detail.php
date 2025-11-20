<?php
// GET /api/providers/:id - Öffentliche Informationen eines Anbieters

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Nur GET-Requests erlaubt.'
        ]
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $providerId = isset($_GET['id']) ? intval($_GET['id']) : null;

    if (!$providerId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'INVALID_ID',
                'message' => 'Ungültige Provider-ID.'
            ]
        ]);
        exit;
    }

    // Provider laden
    $stmt = $conn->prepare("
        SELECT
            u.user_id as id,
            pr.name,
            pr.member_since as memberSince,
            pr.avatar,
            pr.verified,
            COUNT(p.place_id) as placesCount
        FROM users u
        LEFT JOIN providers pr ON u.user_id = pr.user_id
        LEFT JOIN places p ON u.user_id = p.provider_id AND p.active = 1
        WHERE u.user_id = :provider_id AND u.is_provider = 1
        GROUP BY u.user_id
    ");
    $stmt->execute(['provider_id' => $providerId]);
    $provider = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$provider) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 'PROVIDER_NOT_FOUND',
                'message' => 'Anbieter nicht gefunden.'
            ]
        ]);
        exit;
    }

    // Datentypen konvertieren
    $provider['id'] = (int)$provider['id'];
    $provider['verified'] = (bool)$provider['verified'];
    $provider['placesCount'] = (int)$provider['placesCount'];

    echo json_encode([
        'success' => true,
        'data' => $provider
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Serverfehler: ' . $e->getMessage()
        ]
    ], JSON_UNESCAPED_UNICODE);
}
