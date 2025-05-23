<?php
$host = "localhost";
$dbname = "university_management_system";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, name, email, message, created_at FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);

$messages = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($messages);

$conn->close();
?>
