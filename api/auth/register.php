<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Credentials: true");

require_once __DIR__ . '/../../config/database.php';

$input = json_decode(file_get_contents("php://input"), true);
file_put_contents("debug.txt", print_r($input, true));


if (!$input) {
    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    exit;
}

// Pflichtfelder prüfen
$required = [
    "firstName",
    "lastName",
    "gender",
    "email",
    "phone",
    "street",
    "houseNumber",
    "zipCode",
    "city",
    "password"
];

foreach ($required as $field) {
    if (!isset($input[$field]) || trim($input[$field]) === "") {
        echo json_encode(["success" => false, "message" => "Feld '$field' fehlt oder ist leer"]);
        exit;
    }
}

// EMAIL vailidieren
if (!filter_var($input["email"], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Bitte geben Sie eine gültige E-Mail-Adresse ein"]);
}

// Passwörter prüfen
$password = $input["password"];
$confirmPassword = $input["confirmPassword"];

if (strlen($password) < 10) {
    echo json_encode(["success" => false, "message" => "Passwort muss mindestens 10 Zeichen lang sein"]);
}
if (!preg_match('/[a-z]/', $password)) {
    echo json_encode(["success" => false, "message" => "Mindestens 1 Kleinbuchstabe muss enthalten sein"]);
}
if (!preg_match('/[A-Z]/', $password)) {
    echo json_encode(["success" => false, "message" => "Mindestens 1 Großbuchstabe muss enthalten sein"]);
}
if (!preg_match('/\d/', $password)) {
    echo json_encode(["success" => false, "message" => "Mindestens 1 Zahl muss enthalten sein"]);
}
if (!preg_match('/[!@#$%^&*+(),.?\":{}|<>_\-]/', $password)) {
    echo json_encode(["success" => false, "message" => "Mindestens 1 Sonderzeichen muss enthalten sein"]);
}
if (isset($confirmPassword) && $confirmPassword !== $password) {
    echo json_encode(["success" => false, "message" => "Passwörter stimmen nicht überein"]);
}

$db = new Database();
$conn = $db->getConnection();

try {
    $stmt = $conn->prepare("
        INSERT INTO users
        (first_name, last_name, gender, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
        VALUES
        (:first_name, :last_name, :gender, :email, :phone, :street, :house_number, :zip_code, :city, :password_hash, :salt, 0)
    ");

    $salt = bin2hex(random_bytes(16));
    $password_hash = hash("sha256", $input["password"] . $salt);

    $stmt->execute([
        ":first_name" => $input["firstName"],
        ":last_name" => $input["lastName"],
        ":gender" => $input["gender"],
        ":email" => $input["email"],
        ":phone" => $input["phone"],
        ":street" => $input["street"],
        ":house_number" => $input["houseNumber"],
        ":zip_code" => $input["zipCode"],
        ":city" => $input["city"],
        ":password_hash" => $password_hash,
        ":salt" => $salt
    ]);
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>