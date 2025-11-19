<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");

session_start();
require_once __DIR__ . "/config/database.php";

$input = json_decode(file_get_contents("php://input"), true);

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

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Serverfehler: " . $e->getMessage()
    ]);
    exit;
}
?>