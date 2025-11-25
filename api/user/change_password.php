<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: PUT, OPTIONS");

// CORS Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

session_start();

require_once __DIR__ . "/../../config/database.php";

// Prüfen ob User eingeloggt ist
if (!isset($_SESSION["user_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Nicht eingeloggt"
    ]);
    exit;
}

// JSON Body einlesen
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode([
        "success" => false,
        "message" => "Ungültiges JSON"
    ]);
    exit;
}

$currentPassword = $input["currentPassword"];
$newPassword = $input["newPassword"];
$confirmPassword = $input["confirmPassword"];

// Validierung
if (!$currentPassword || !$newPassword || !$confirmPassword) {
    echo json_encode([
        "success" => false,
        "message" => "Bitte alle Felder ausfüllen"
    ]);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode([
        "success" => false,
        "message" => "Neue Passwörter stimmen nicht überein"
    ]);
    exit;
}

if (strlen($newPassword) < 10) {
    echo json_encode([
        "success" => false,
        "message" => "Das neue Passwort muss mindestens 10 Zeichen lang sein"
    ]);
    exit;
}

if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{10,}$/', $newPassword)) {
    echo json_encode([
        "success" => false,
        "message" => "Passwort muss Groß- und Kleinbuchstaben, eine Zahl und ein Sonderzeichen enthalten"
    ]);
    exit;
}

// DB Verbindung
try {
    $db = new Database();
    $conn = $db->getConnection();

    // Altes Passwort laden
    $stmt = $conn->prepare("
        SELECT password_hash, salt
        FROM users
        WHERE user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute(["user_id" => $_SESSION["user_id"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "Benutzer nicht gefunden"
        ]);
        exit;
    }

    // Passwort prüfen
    $oldHash = hash("sha256", $currentPassword . $user["salt"]);

    if ($oldHash !== $user["password_hash"]) {
        echo json_encode([
            "success" => false,
            "message" => "Aktuelles Password ist falsch"
        ]);
        exit;
    }

    // Neues Passwort generieren
    $newSalt = bin2hex(random_bytes(16));
    $newHash = hash("sha256", $newPassword . $newSalt);

    $stmt = $conn->prepare("
        UPDATE users
        SET password_hash = :newHash, salt = :newSalt
        WHERE user_id = :user_id
    ");

    $stmt->execute([
        "newHash" => $newHash,
        "newSalt" => $newSalt,
        "user_id" => $_SESSION["user_id"]
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Passwort erfolgreich geändert"
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