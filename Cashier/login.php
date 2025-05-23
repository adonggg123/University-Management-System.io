<?php
session_start();
require_once 'utils/db.php';
require_once 'utils/audit.php';

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (!$username || !$password) {
    header('Location: index.php?error=Please+enter+username+and+password');
    exit;
}

$stmt = $pdo->prepare('SELECT id, password_hash, full_name, role FROM users WHERE username = ?');
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    audit_log($pdo, $user['id'], 'login', 'User logged in');
    header('Location: dashboard.php');
    exit;
} else {
    audit_log($pdo, null, 'login_failed', 'Username: ' . $username);
    header('Location: index.php?error=Invalid+credentials');
    exit;
}  
