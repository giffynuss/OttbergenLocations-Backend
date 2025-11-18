<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/../../config/database.php';

$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $stmt = $conn->prepare("
        INSERT INTO users
        (first_name, last_name, email, phone, street, house_number, zip_code, city, password_hash, salt, is_provider)
        VALUES
        (:first_name, :last_name, :email, :phone, :street, :house_number, :zip_code, :city, :password_hash, :salt, 0)
    ");

    $salt = bin2hex(random_bytes(16));
    $password_hash = hash("sha256", $input["password"] . $salt);

    $stmt->execute([
        ":first_name" => $input["firstName"],
        ":last_name" => $input["lastName"],
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