<?php
// MySQL connection setup
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "university_management_system";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check for connection error
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Prepare and execute query to fetch new orders (or notifications)
$stmt = $conn->prepare("SELECT * FROM notifications ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];

while ($row = $result->fetch_assoc()) {
  $notifications[] = $row;
}

header('Content-Type: application/json');
echo json_encode($notifications);
?>
