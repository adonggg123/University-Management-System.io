<?php
// update_user_page.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents('php://input'), true);

  // Save product data to the user-facing product list (this could be a session, cache, or database)
  // For simplicity, we'll simulate saving this in a session or just echoing back the product data.

  // Example (store data in session for now):
  session_start();
  if (!isset($_SESSION['products'])) {
    $_SESSION['products'] = [];
  }
  
  // Add new product to session
  $_SESSION['products'][] = $data;

  // Return a success response
  echo json_encode(['status' => 'success', 'product' => $data]);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
