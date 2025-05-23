<?php
require_once 'utils/db.php';

$users = [
    ['admin', 'System Administrator', 'admin'],
    ['cashier1', 'Default Cashier', 'cashier'],
    ['staff1', 'Default Staff', 'staff'],
];

$password = 'admin123';

foreach ($users as $user) {
    $username = $user[0];
    $full_name = $user[1];
    $role = $user[2];
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        echo "User '$username' already exists.<br>";
        continue;
    }

    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $hash, $full_name, $role])) {
        echo "User '$username' created successfully.<br>";
    } else {
        echo "Failed to create user '$username'.<br>";
    }
}
?>
