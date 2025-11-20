<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET");

session_start();
require_once __DIR__ . "/../../config/database.php";

// Prüfen, ob der Nutzer eingeloggt ist
if (!isset($_SESSION["user_id"])) {
    echo json_encode(["success" => false,"message"=> "Nicht eingeloggt"]);
    exit;
}

try{
    $db = new Database();
    $conn = $db->getConnection();

    // Nutzer anhand der Session-ID laden
    $stmt = $conn->prepare("
        SELECT
            user_id,
            first_name,
            last_name,
            gender,
            email,
            phone,
            street,
            house_number,
            zip_code,
            city
        FROM users
        WHERE user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute(["user_id" => $_SESSION["user_id"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["success"=> false,"message"=> "Benutzer nicht gefunden"]);
        exit;
    }

    echo json_encode(["success" => true,"user" => $user]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Serverfehler: " . $e->getMessage()]);
    exit;
}
?>