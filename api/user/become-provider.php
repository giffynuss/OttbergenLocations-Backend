<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Nur POST-Anfragen erlaubt'
        ]
    ]);
    exit;
}

require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Authentifizierung erforderlich
$userId = requireAuth();

$db = new Database();
$conn = $db->getConnection();

try {
    // is_provider auf 1 setzen
    $stmt = $conn->prepare("UPDATE users SET is_provider = 1 WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $userId]);

    // Session aktualisieren
    $_SESSION['is_provider'] = 1;

    echo json_encode([
        'success' => true,
        'message' => 'Sie wurden erfolgreich als Provider registriert'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Serverfehler beim Aktualisieren des Provider-Status'
        ]
    ]);
}
?>
