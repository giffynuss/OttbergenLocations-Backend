<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");

require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION["user_id"])) {
    echo json_encode((["success" => false, "message" => "Nicht eingeloggt"]));
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare("
    UPDATE users SET
        first_name = :firstName,
        last_name = :lastName,
        gender = :gender,
        email = :email,
        phone = :phone,
        street = :street,
        house_number = :houseNumber,
        zip_code = :zipCode,
        city = :city
    WHERE user_id = :id
");

$success = $stmt->execute([
    ":firstName" => $input["firstName"],
    ":lastName" => $input["lastName"],
    ":gender" => $input["gender"],
    ":email" => $input["email"],
    ":phone" => $input["phone"],
    ":street" => $input["street"],
    ":houseNumber" => $input["houseNumber"],
    ":zipCode" => $input["zipCode"],
    ":city" => $input["city"],
    ":id" => $_SESSION["user_id"]
]);

echo json_encode((["success"=> $success]));
?>