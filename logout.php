<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");

session_start();

// Falls keine Session existiert: success zurückgeben
if (!isset($_SESSION["user_id"])){
    echo json_encode(["success" => true, "message" => "Bereits ausgeloggt"
    ]);
    exit;
}

$_SESSION = [];

// Session-Cokkie löschen
if (ini_get("session.use_cookies")){
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 3600,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Session komplett zerstören
session_destroy();

// Erfolgsantwort
echo json_encode([
    "success" => true,
    "message"=> "Logout erfolgreich"
]);
exit;
?>