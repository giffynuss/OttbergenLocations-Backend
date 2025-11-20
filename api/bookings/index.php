<?php
// GET /api/bookings - Liste aller Buchungen des eingeloggten Users

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'METHOD_NOT_ALLOWED',
            'message' => 'Nur GET-Requests erlaubt.'
        ]
    ]);
    exit;
}

try {
    // Authentifizierung erforderlich
    $userId = requireAuth();

    $db = new Database();
    $conn = $db->getConnection();

    // Query-Parameter
    $status = $_GET['status'] ?? null;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;

    // Base Query
    $sql = "
        SELECT
            b.booking_id as id,
            b.place_id as placeId,
            p.name as placeName,
            p.location as placeLocation,
            b.check_in as checkIn,
            b.check_out as checkOut,
            b.guests,
            b.total_price as totalPrice,
            b.status,
            b.created_at as createdAt
        FROM bookings b
        JOIN places p ON b.place_id = p.place_id
        WHERE b.user_id = :user_id
    ";

    $params = ['user_id' => $userId];

    // Filter: Status
    if ($status) {
        $sql .= " AND b.status = :status";
        $params['status'] = $status;
    }

    $sql .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Datentypen konvertieren
    foreach ($bookings as &$booking) {
        $booking['id'] = (int)$booking['id'];
        $booking['placeId'] = (int)$booking['placeId'];
        $booking['guests'] = (int)$booking['guests'];
        $booking['totalPrice'] = (float)$booking['totalPrice'];
    }

    // Gesamtanzahl für Pagination
    $countSql = "
        SELECT COUNT(*) as total
        FROM bookings b
        WHERE b.user_id = :user_id
    ";
    if ($status) {
        $countSql .= " AND b.status = :status";
    }

    $countStmt = $conn->prepare($countSql);
    foreach ($params as $key => $value) {
        if ($key !== 'limit' && $key !== 'offset') {
            $countStmt->bindValue($key, $value);
        }
    }
    $countStmt->execute();
    $total = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        'success' => true,
        'data' => $bookings,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => 'SERVER_ERROR',
            'message' => 'Serverfehler: ' . $e->getMessage()
        ]
    ], JSON_UNESCAPED_UNICODE);
}
