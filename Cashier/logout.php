<?php
session_start();
require_once 'utils/db.php';
require_once 'utils/audit.php';
if (isset($_SESSION['user_id'])) {
    audit_log($pdo, $_SESSION['user_id'], 'logout', 'User logged out');
}
session_unset();
session_destroy();
header('Location: index.php');
exit;
