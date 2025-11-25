<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/database.php';

$placeId = $_GET["place_id"] ?? null;

if (!$placeId) {
    echo json_encode(["success" => false, "message" => "place_id fehlt"]);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Falls du start_date + end_date hast — BESTE Lösung:
$stmt = $conn->prepare("
    SELECT booking_id, check_in, check_out, status
    FROM bookings
    WHERE place_id = :place_id
");
$stmt->bindParam(":place_id", $placeId, PDO::PARAM_INT);
$stmt->execute();

$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "bookings" => $bookings
]);
