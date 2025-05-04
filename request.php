<?php
include 'db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];

    // Insert into supplies table
    $stmt = $conn->prepare("INSERT INTO supplies (item_name, quantity) VALUES (?, ?)");
    $stmt->bind_param("si", $item_name, $quantity);
    $stmt->execute();

    // Insert into notifications
    $note = "New purchase request: $item_name ($quantity pcs)";
    $conn->query("INSERT INTO notifications (message, status) VALUES ('$note', 'unread')");

    header('Location: index.php');
    exit;
}
?>
