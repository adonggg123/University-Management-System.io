<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqli = new mysqli('localhost', 'root', '', 'university_management_system');

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

$query = "SELECT id, name, price, image FROM products_fh";
$result = $mysqli->query($query);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . $mysqli->error]);
    exit;
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
echo json_encode($products);
exit;
