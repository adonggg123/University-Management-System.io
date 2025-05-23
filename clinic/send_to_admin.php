<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);
    $id = filter_var($data['id'], FILTER_SANITIZE_NUMBER_INT);

    // Update appointment status
    $stmt = $conn->prepare("UPDATE appointments SET status = 'sent' WHERE id = ?");
    $stmt->execute([$id]);

    // Create notification for admin
    $stmt = $conn->prepare("SELECT full_name, student_id FROM appointments WHERE id = ?");
    $stmt->execute([$id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    $message = "New appointment request from {$appointment['full_name']} (Student ID: {$appointment['student_id']})";
 
    $stmt = $conn->prepare("
        INSERT INTO notifications (user_type, appointment_id, message)
        VALUES ('admin', ?, ?)
    ");
    $stmt->execute([$id, $message]);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>