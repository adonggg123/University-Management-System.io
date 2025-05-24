<?php
$host = "localhost";
$user = "root"; // or your DB user
$pass = "quest4inno@server";     // or your DB password
$db = "university_management_system"; // your database name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT name, price, image FROM products_fh";
$result = $conn->query($sql);

$products = [];

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $products[] = $row;
  }
}

echo json_encode($products);

$conn->close();
?>
