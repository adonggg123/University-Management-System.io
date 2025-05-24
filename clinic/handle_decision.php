<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "quest4inno@server";
$dbname = "university_management_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);
    $appointment_id = filter_var($data['appointment_id'], FILTER_SANITIZE_NUMBER_INT);
    $decision = filter_var($data['decision'], FILTER_SANITIZE_STRING);
    $decline_reason = isset($data['decline_reason']) ? filter_var($data['decline_reason'], FILTER_SANITIZE_STRING) : null;

    // Update appointment decision
    $stmt = $conn->prepare("
        UPDATE appointments 
        SET decision = ?, decline_reason = ?
        WHERE id = ?
    ");
    $stmt->execute([$decision, $decline_reason, $appointment_id]);

    // Mark admin notification as read
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE user_type = 'admin' AND appointment_id = ?
    ");
    $stmt->execute([$appointment_id]);

    // Create notification for student
    $stmt = $conn->prepare("SELECT full_name, unique_key, preferred_date FROM appointments WHERE id = ?");
    $stmt->execute([$appointment_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    $message = $decision === 'accepted' 
        ? "Your appointment on {$appointment['preferred_date']} has been accepted."
        : "Your appointment on {$appointment['preferred_date']} has been declined. Reason: $decline_reason";

    $stmt = $conn->prepare("
        INSERT INTO notifications (user_type, unique_key, appointment_id, message)
        VALUES ('student', ?, ?, ?)
    ");
    $stmt->execute([$appointment['unique_key'], $appointment_id, $message]);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>