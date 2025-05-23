<?php
require 'db_conn.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $price = $_POST['price'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = uniqid() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $imageName);

        // Update with image
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image = ? WHERE id = ?");
        $stmt->bind_param("sdsi", $name, $price, $imageName, $id);
    } else {
        // Update without image
        $stmt = $conn->prepare("UPDATE products SET name = ?, price = ? WHERE id = ?");
        $stmt->bind_param("sdi", $name, $price, $id);
    }

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
}
?>
