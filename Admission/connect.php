<?php
$host = "localhost";
$user = "root";
$password = "quest4inno@server";  // Leave blank for XAMPP default
$db = "university_management_system";

$conn = mysqli_connect($host, $user, $password, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>