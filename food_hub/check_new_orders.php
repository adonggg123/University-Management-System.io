<?php
// check_new_orders.php
$host = "localhost";
$user = "root";
$pass = "quest4inno@server";
$db = "university_management_system";

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query for unread orders (status 'pending' or 'new')
$sql = "SELECT COUNT(*) as new_orders FROM orders WHERE status = 'pending'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo json_encode(["new_orders" => $row['new_orders']]);

$conn->close();
?>
