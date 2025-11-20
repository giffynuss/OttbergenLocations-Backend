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

if (!$input){
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
        "success"=> false,
        "message"=> "Bitte alle Felder ausfüllen"
    ]);
    exit;
}

if (!$newPassword !== $confirmPassword){
    echo json_encode([
        "success"=> false,
        "message"=> "Neue Passwörter stimmen nicht überein"
    ]);
    exit;
}

if (strlen($newPassword) < 12) {
    echo json_encode([
        "success"=> false,
        "message"=> "Das neue Passwort muss mindestens 12 Zeichen lang sein"
    ]);
    exit;
}
?>