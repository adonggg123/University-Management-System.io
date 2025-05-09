<?php
session_start(); // if needed
include 'db_conn.php'; // your DB connection    
$conn->query("UPDATE borrow_requests SET user_notified = 1 WHERE status = 'approved' AND user_notified = 0");
echo json_encode(['status' => 'success']);
?>
