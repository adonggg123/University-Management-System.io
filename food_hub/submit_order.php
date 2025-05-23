<?php
include 'db_conn.php';

$data = json_decode(file_get_contents("php://input"), true);

$customerName = $data['customer_name'];
$roomNumber = $data['room_number'];
$paymentMethod = $data['payment_method'];
$cart = $data['items']; // updated to match frontend key

// Calculate total price
$totalPrice = 0;
foreach ($cart as $item) {
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($product) {
        $totalPrice += $product['price'] * $item['quantity'];
    }
}

// Insert into orders table
$stmt = $pdo->prepare("INSERT INTO orders (customer_name, room_number, payment_method, total_price) VALUES (?, ?, ?, ?)");
$stmt->execute([$customerName, $roomNumber, $paymentMethod, $totalPrice]);

$orderId = $pdo->lastInsertId();

// Insert order items and prepare message
$productDetails = [];
foreach ($cart as $item) {
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$orderId, $item['product_id'], $item['quantity']]);

    // Fetch product name
    $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->execute([$item['product_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $productDetails[] = $product['name'] . ' × ' . $item['quantity'];
    }
}

// Compose message
$productSummary = implode(', ', $productDetails);
$message = "New order from $customerName (Room $roomNumber): $productSummary";

// Insert into notifications table
$stmt = $pdo->prepare("INSERT INTO notifications (message, is_read, created_at) VALUES (?, 0, NOW())");
$stmt->execute([$message]);

echo "Order placed successfully!";
?>
