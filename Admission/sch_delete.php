<?php
include 'connect.php'; // adjust this to your DB connection file

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // get and sanitize the ID

    // Perform the delete query
    $sql = "DELETE FROM scholarship_applications WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        // Redirect back after deletion
        header("Location: " . $_SERVER['HTTP_REFERER']);
        // Replace 'previous_page.php' with your actual page
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
