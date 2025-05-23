<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "university_management_system";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Delete all products except those named 'Tempura'
$sql = "DELETE FROM products WHERE name != 'Tempura'";

if ($conn->query($sql) === TRUE) {
  echo "All products except Tempura deleted.";
} else {
  echo "Error deleting products: " . $conn->error;
}

$conn->close();
?>
