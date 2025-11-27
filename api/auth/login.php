<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");

session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../helpers/validation.php";

$input = json_decode(file_get_contents("php://input"), true);

// JSON-Validierung
if (!$input || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültige JSON-Daten']);
    exit;
}

// E-Mail-Format validieren
$emailValidation = validateEmail($input['email'] ?? '');
if (!$emailValidation['valid']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $emailValidation['error']]);
    exit;
}

// Passwort vorhanden prüfen (KEINE Längen-Validierung beim Login!)
if (empty($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Passwort ist erforderlich']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    // Nutzer anhand der E-Mail suchen
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(["email" => $input["email"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success" => false, "message" => "Benutzer existiert nicht"]);
        exit;
    }

    // Passwort prüfen
    $password_hash = hash("sha256", $input["password"] . $user["salt"]);

    if (!hash_equals($password_hash, $user["password_hash"])) {
        echo json_encode(["success" => false, "message" => "Falsches Passwort"]);
        exit;
    }

    $_SESSION["user_id"] = $user["user_id"];
    $_SESSION["is_provider"] = $user["is_provider"];

    // Erfolgsantwort
    echo json_encode(["success" => true, "message" => "Login erfolgreich",
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Serverfehler: " . $e->getMessage()
    ]);
    exit;
}
?>