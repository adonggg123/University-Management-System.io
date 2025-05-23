<?php
// get_orders.php
require 'db_conn.php';

$sql = "SELECT o.id, o.customer_name, o.room_number, o.payment_method, o.created_at,
               oi.product_id, oi.quantity, p.name AS product_name
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group orders by order ID
$orders = [];
foreach ($rows as $row) {
    $orderId = $row['id'];
    if (!isset($orders[$orderId])) {
        $orders[$orderId] = [
            'id' => $row['id'],
            'customer_name' => $row['customer_name'],
            'room_number' => $row['room_number'],
            'payment_method' => $row['payment_method'],
            'created_at' => $row['created_at'],
            'items' => []
        ];
    }
    $orders[$orderId]['items'][] = [
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'quantity' => $row['quantity']
    ];
}

echo json_encode(array_values($orders));
?>
