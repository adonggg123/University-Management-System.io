<?php
$host = "localhost";
$user = "root";  // default for XAMPP
$pass = "quest4inno@server";      // default for XAMPP
$db   = "university_management_system"; // your database name

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
