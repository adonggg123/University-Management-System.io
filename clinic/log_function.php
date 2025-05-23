<?php
function logAction($conn, $action, $module, $status) {
    try {
        $stmt = $conn->prepare("INSERT INTO logs (action, module, date_time, status) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$action, $module, $status]);
    } catch (PDOException $e) {
        error_log("Logging failed: " . $e->getMessage(), 3, 'errors.log');
    }
}
?>