<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['staff', 'admin'])) {
    header('Location: dashboard.php'); // Redirect to dashboard if not authorized for orders
    exit;
}
require_once 'utils/db.php';
require_once 'utils/audit.php';

// Handle add order
$order_error = '';
$order_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $student_id = intval($_POST['student_id']);
    $order_type = $_POST['order_type'];
    $amount = floatval($_POST['amount']);

    $valid_types = [
        'Tuition', 'Miscellaneous', 'Fees', 'Fines',
        'TOR', 'Honorable Dismissal', 'Good Moral Certificate'
    ];
    if ($student_id && in_array($order_type, $valid_types) && $amount > 0) {
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (student_id, order_type, amount, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$student_id, $order_type, $amount]);
            $order_success = "Order created successfully.";
            audit_log($pdo, $_SESSION['user_id'], 'create_order', "Order for student_id=$student_id, type=$order_type, amount=$amount");
        } catch (PDOException $e) {
            $order_error = "Failed to create order. Error: " . $e->getMessage();
        }
    } else {
        $order_error = "Invalid input. Please fill all fields correctly and ensure amount is positive.";
    }
}

// Handle student search
$search = trim($_GET['search'] ?? '');
$students_results = [];
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_no LIKE ? OR first_name LIKE ? OR last_name LIKE ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    $students_results = $stmt->fetchAll();
}

// Fetch orders for selected student
$orders_list = []; // Renamed
$selected_student = null;
if (isset($_GET['student_id'])) {
    $student_id_get = intval($_GET['student_id']);
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id_get]);
    $selected_student = $stmt->fetch();

    if($selected_student){
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE student_id = ? ORDER BY created_at DESC");
        $stmt->execute([$student_id_get]);
        $orders_list = $stmt->fetchAll();
    }
}

$order_types_available = [
    'Tuition', 'Miscellaneous', 'Fees', 'Fines',
    'TOR', 'Honorable Dismissal', 'Good Moral Certificate'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - UMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="orders.php">Orders</a></li>
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

    <div class="container mt-4">
        <h2 class="mb-4 text-center">Order Management</h2>

        <?php if ($order_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($order_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($order_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($order_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Student Search Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Search Student to Manage Orders</h5>
                <form method="get" action="orders.php" class="row g-3 align-items-center">
                    <div class="col flex-grow-1">
                        <label for="searchStudent" class="visually-hidden">Search student...</label>
                        <input type="text" name="search" id="searchStudent" class="form-control" placeholder="Enter student no or name..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Search Student</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($search && empty($students_results) && !isset($_GET['student_id'])): ?>
            <div class="alert alert-info">No students found matching your search.</div>
        <?php elseif (!empty($students_results)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="mb-0">Search Results</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Student No</th><th>Name</th><th>Course</th><th>Year</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students_results as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['student_no']) ?></td>
                                    <td><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name'] . ' ' . $s['middle_name']) ?></td>
                                    <td><?= htmlspecialchars($s['course']) ?></td>
                                    <td><?= htmlspecialchars($s['year_level']) ?></td>
                                    <td>
                                        <a href="orders.php?student_id=<?= $s['id'] ?>&search=<?= htmlspecialchars($search) ?>" class="btn btn-sm btn-outline-primary">View/Add Orders</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($selected_student): ?>
            <div class="card shadow-sm mb-4">
                 <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Orders for: <?= htmlspecialchars($selected_student['last_name'] . ', ' . $selected_student['first_name']) ?> (<?= htmlspecialchars($selected_student['student_no']) ?>)</h3>
                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#addOrderForm-<?= $selected_student['id'] ?>" aria-expanded="false" aria-controls="addOrderForm-<?= $selected_student['id'] ?>">
                        <i class="bi bi-plus-circle me-1"></i> Add New Order
                    </button>
                </div>
                <div class="collapse mt-0" id="addOrderForm-<?= $selected_student['id'] ?>">
                    <div class="card-body border-top">
                        <form method="post" action="orders.php?student_id=<?= $selected_student['id'] ?><?= $search ? '&search='.urlencode($search) : '' ?>">
                            <input type="hidden" name="student_id" value="<?= $selected_student['id'] ?>">
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label for="order_type-<?= $selected_student['id'] ?>" class="form-label">Order Type*</label>
                                    <select name="order_type" id="order_type-<?= $selected_student['id'] ?>" class="form-select" required>
                                        <option value="">Select type...</option>
                                        <?php foreach ($order_types_available as $type): ?>
                                        <option value="<?= htmlspecialchars($type) ?>"><?= htmlspecialchars($type) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="amount-<?= $selected_student['id'] ?>" class="form-label">Amount (PHP)*</label>
                                    <input type="number" name="amount" id="amount-<?= $selected_student['id'] ?>" class="form-control" min="0.01" step="0.01" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-secondary me-md-2" data-bs-toggle="collapse" data-bs-target="#addOrderForm-<?= $selected_student['id'] ?>">Cancel</button>
                                <button type="submit" name="add_order" class="btn btn-primary">Create Order</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if (!empty($orders_list)): ?>
                    <div class="card-body p-0 border-top">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr><th>Order ID</th><th>Order Type</th><th>Amount</th><th>Status</th><th>Created At</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders_list as $o): ?>
                                    <tr>
                                        <td>#<?= htmlspecialchars($o['id']) ?></td>
                                        <td><?= htmlspecialchars($o['order_type']) ?></td>
                                        <td><?= htmlspecialchars(number_format($o['amount'], 2)) ?></td>
                                        <td>
                                            <?php 
                                            $status_badge = 'secondary';
                                            if ($o['status'] == 'paid') $status_badge = 'success';
                                            if ($o['status'] == 'pending') $status_badge = 'warning text-dark';
                                            if ($o['status'] == 'cancelled') $status_badge = 'danger';
                                            ?>
                                            <span class="badge bg-<?= $status_badge ?>"><?= htmlspecialchars(ucfirst($o['status'])) ?></span>
                                        </td>
                                        <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($o['created_at']))) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-body border-top">
                        <p class="mb-0 text-center">No orders found for this student.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
