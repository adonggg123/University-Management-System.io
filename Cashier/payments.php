<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'db_conn.php';

// Handle payment processing
$pay_error = '';
$pay_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_order'])) {
    $order_id = intval($_POST['order_id']);
    $amount = floatval($_POST['amount']);
    $cashier_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND status = 'pending'");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if ($order && $amount == $order['amount']) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO payments (order_id, cashier_id, amount) VALUES (?, ?, ?)");
            $stmt->execute([$order_id, $cashier_id, $amount]);
            $payment_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
            $stmt->execute([$order_id]);

            $receipt_no = 'RCPT-' . date('Ymd') . '-' . str_pad($payment_id, 6, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare("INSERT INTO receipts (payment_id, receipt_no) VALUES (?, ?)");
            $stmt->execute([$payment_id, $receipt_no]);

            $pdo->commit();
            $pay_success = "Payment successful. Receipt No: " . htmlspecialchars($receipt_no);
        } catch (Exception $e) {
            $pdo->rollBack();
            $pay_error = "Payment failed. Please try again. Error: " . $e->getMessage();
        }
    } else {
        $pay_error = "Invalid order (may not be pending or amount mismatch).";
    }
}

// Handle student search
$search = trim($_GET['search'] ?? '');
$students_results = []; // Renamed to avoid conflict with $students in other files if included
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_no LIKE ? OR first_name LIKE ? OR last_name LIKE ? ORDER BY created_at DESC LIMIT 10");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
    $students_results = $stmt->fetchAll();
}

// Fetch orders for selected student
$orders = [];
$selected_student = null;
if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$student_id]);
    $selected_student = $stmt->fetch();

    if($selected_student){
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE student_id = ? AND status = 'pending' ORDER BY created_at DESC");
        $stmt->execute([$student_id]);
        $orders = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - UMS</title>
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="payments.php">Payments</a></li>
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
        <h2 class="mb-4 text-center">Process Payments</h2>

        <?php if ($pay_success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pay_success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($pay_error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($pay_error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Student Search Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Search Student for Payment</h5>
                <form method="get" action="payments.php" class="row g-3 align-items-center">
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
                                <tr>
                                    <th>Student No</th>
                                    <th>Name</th>
                                    <th>Course</th>
                                    <th>Year</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students_results as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['student_no']) ?></td>
                                    <td><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name'] . ' ' . $s['middle_name']) ?></td>
                                    <td><?= htmlspecialchars($s['course']) ?></td>
                                    <td><?= htmlspecialchars($s['year_level']) ?></td>
                                    <td>
                                        <a href="payments.php?student_id=<?= $s['id'] ?>&search=<?= htmlspecialchars($search) ?>" class="btn btn-sm btn-outline-primary">View Pending Orders</a>
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
                 <div class="card-header">
                    <h3 class="mb-0">Pending Orders for: <?= htmlspecialchars($selected_student['last_name'] . ', ' . $selected_student['first_name']) ?> (<?= htmlspecialchars($selected_student['student_no']) ?>)</h3>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($orders)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order Type</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($o['order_type']) ?></td>
                                        <td><?= htmlspecialchars(number_format($o['amount'], 2)) ?></td>
                                        <td><span class="badge bg-warning text-dark"><?= htmlspecialchars(ucfirst($o['status'])) ?></span></td>
                                        <td><?= htmlspecialchars(date('Y-m-d', strtotime($o['created_at']))) ?></td>
                                        <td>
                                            <form method="post" action="payments.php?student_id=<?= $selected_student['id'] ?>&search=<?= htmlspecialchars($search) ?>" class="pay-form-inline">
                                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                <input type="hidden" name="amount" value="<?= htmlspecialchars($o['amount']) ?>">
                                                <button type="submit" name="pay_order" class="btn btn-primary btn-sm">Pay PHP <?= htmlspecialchars(number_format($o['amount'],2)) ?> (Cash)</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="card-body">
                            <p class="mb-0">No pending orders found for this student.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
