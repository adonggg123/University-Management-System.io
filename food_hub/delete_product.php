<?php
require 'db_conn.php'; // imong koneksyon file

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Optional: delete image file here kung gusto nimo
    // Get image path first
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($image);
    $stmt->fetch();
    $stmt->close();

    if ($image && file_exists("uploads/" . $image)) {
        unlink("uploads/" . $image);
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
}
?>
