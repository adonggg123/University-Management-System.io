<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "university_management_system";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$productName = $_POST['name'];
$productPrice = $_POST['price'];
$imagePath = "";

// if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
//   $targetDir = "uploads/";
//   $targetFile = $targetDir . basename($_FILES["image"]["name"]);
//   if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
//     $imagePath = $targetFile;
//   }
// }

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
  $targetDir = "uploads/";
  $fileName = basename($_FILES["image"]["name"]);
  $targetFile = $targetDir . $fileName;

  if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
    $imagePath = $fileName; // ✅ Save only the filename
  }
}


$sql = "INSERT INTO products (name, price, image) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sds", $productName, $productPrice, $imagePath);

if ($stmt->execute()) {
  echo "success";
} else {
  echo "error";
}

$stmt->close();
$conn->close();
?>
