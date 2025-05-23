<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UMS Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="custom-style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-ums-theme fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">UMS Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
                    <?php if (in_array($_SESSION['role'], ['staff', 'admin'])): ?>
                        <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="payments.php">Payments</a></li>
                    <li class="nav-item"><a class="nav-link" href="receipts.php">Receipts</a></li>
                    <li class="nav-item"><a class="nav-link" href="refunds.php">Refunds</a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-content mt-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="card-title text-center mb-3">Welcome, <?= htmlspecialchars($_SESSION['full_name'] ?? 'Guest') ?>!</h2>
                <p class="text-center text-muted">Role: <?= htmlspecialchars($_SESSION['role']) ?></p>
                <!-- Additional dashboard content can go here -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
