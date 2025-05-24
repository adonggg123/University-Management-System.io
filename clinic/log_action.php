<?php
$servername = "localhost";
$username = "root";
$password = "quest4inno@server";
$dbname = "university_management_system";

header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['action'], $input['module'], $input['status'])) {
        $stmt = $conn->prepare("INSERT INTO logs (action, module, date_time, status) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$input['action'], $input['module'], $input['status']]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Invalid input']);
    }
} catch (PDOException $e) {
    error_log("Logging failed: " . $e->getMessage(), 3, 'errors.log');
    echo json_encode(['error' => 'Database error']);
}
?>