<?php
function audit_log($pdo, $user_id, $action, $details = null) {
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $action, $details]);
}
