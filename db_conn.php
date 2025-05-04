<?php
    $servername = "localhost";
    $username = "root";  // Default username for MySQL
    $password = "";  // Default password for MySQL
    $dbname = "inventory_system";  // Name of your database

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
