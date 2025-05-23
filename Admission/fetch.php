<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "university_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM student_applications";
$result = $conn->query($sql);

$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

echo json_encode($applications);
$conn->close();
?>
