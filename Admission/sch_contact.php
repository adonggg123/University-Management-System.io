<?php
include 'connect.php';

header('Content-Type: application/json');

$response = ["success" => false, "message" => "Unknown error"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    $sql = "INSERT INTO contact_messages (student_id, fullname, email, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $student_id, $fullname, $email, $message);

    if ($stmt->execute()) {
        $response = ["success" => true, "message" => "✅ Message sent successfully!"];
    } else {
        $response = ["success" => false, "message" => "❌ Error: " . $stmt->error];
    }

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>
