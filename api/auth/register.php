<?php
// Keine HTML-Fehlerausgaben - JSON-Format sicherstellen
error_reporting(E_ALL);
ini_set('display_errors', 0);

// JSON Header als erstes setzen
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Credentials: true");

// OPTIONS Preflight Request behandeln
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Nur POST erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Methode nicht erlaubt']);
    exit;
}

require_once __DIR__ . '/../../config/database.php';

// Datenbankverbindung prüfen
try {
    $db = new Database();
    $conn = $db->getConnection();

    if (!$conn) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Datenbankverbindung fehlgeschlagen']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage()]);
    exit;
}

// JSON-Daten lesen und validieren
$json = file_get_contents("php://input");
$input = json_decode($json, true);

if (!$input || json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültige JSON-Daten']);
    exit;
}

// Pflichtfelder prüfen
$required_fields = ['firstName', 'lastName', 'email', 'password', 'phone', 'street', 'houseNumber', 'zipCode', 'city'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($input[$field]) || trim($input[$field]) === '') {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Fehlende oder leere Felder: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

// E-Mail-Format validieren
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ungültige E-Mail-Adresse']);
    exit;
}

// Passwort-Länge prüfen
if (strlen($input['password']) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Passwort muss mindestens 6 Zeichen lang sein']);
    exit;
}

try {
    // E-Mail-Duplikat prüfen
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->execute([':email' => $input['email']]);

    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Diese E-Mail-Adresse ist bereits registriert']);
        exit;
    }

    // Benutzer erstellen (ohne created_at - Spalte existiert nicht)
    $stmt = $conn->prepare("
        INSERT INTO users
        (first_name, last_name, gender, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
        VALUES
        (:first_name, :last_name, :gender, :email, :phone, :street, :house_number, :zip_code, :city, :password_hash, :salt, 0)
    ");

    $salt = bin2hex(random_bytes(16));
    $password_hash = hash("sha256", $input["password"] . $salt);

    // Gender: Standard 'frau' wenn nicht angegeben (DB erlaubt nur 'herr' oder 'frau')
    $gender = isset($input['gender']) && in_array($input['gender'], ['herr', 'frau'])
        ? $input['gender']
        : 'frau';

    $stmt->execute([
        ":first_name" => trim($input["firstName"]),
        ":last_name" => trim($input["lastName"]),
        ":gender" => $gender,
        ":email" => trim($input["email"]),
        ":phone" => trim($input["phone"]),
        ":street" => trim($input["street"]),
        ":house_number" => trim($input["houseNumber"]),
        ":zip_code" => trim($input["zipCode"]),
        ":city" => trim($input["city"]),
        ":password_hash" => $password_hash,
        ":salt" => $salt
    ]);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Registrierung erfolgreich'
    ]);

} catch (PDOException $e) {
    // Generische Fehlermeldung für Sicherheit
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fehler beim Speichern der Daten'
    ]);

    // Detaillierten Fehler ins Error-Log schreiben (nicht an Client)
    error_log("Registration error: " . $e->getMessage());
}
?>