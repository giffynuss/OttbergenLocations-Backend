<?php
require_once __DIR__ . '/database.php';

$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    echo "<h2 style='color: green;'>✔ Database connection successful!</h2>";
} else {
    echo "<h2 style='color: red;'>✘ Database connection failed!</h2>";
}
?>
