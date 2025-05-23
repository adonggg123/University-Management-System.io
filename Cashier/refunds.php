<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'db_conn.php';

// diri mag Handle refund processing
$refund_error = '';
$refund_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['refund_payment'])) {
    $payment_id = intval($_POST['payment_id']);
    $reason = trim($_POST['reason']);
    $user_id = $_SESSION['user_id'];

    // Validate payment (must not be already refunded)
    $stmt = $pdo->prepare("SELECT p.*, o.amount AS order_amount FROM payments p
        JOIN orders o ON p.order_id = o.id
        WHERE p.id = ? AND NOT EXISTS (SELECT 1 FROM refunds r WHERE r.payment_id = p.id)");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch();

    if ($payment && $reason) {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO refunds (payment_id, processed_by, amount, reason) VALUES (?, ?, ?, ?)");
            $stmt->execute([$payment_id, $user_id, $payment['amount'], $reason]);
            $pdo->commit();
            $refund_success = "Refund processed successfully.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $refund_error = "Refund failed. Please try again.";
        }
    } else {
        $refund_error = "Invalid payment or reason for refund (payment might already be refunded or does not exist).";
    }
}

// Handle search for payments to refund
$search = trim($_GET['search'] ?? '');
// $payments = []; // Removed: $payments will be assigned by fetchAll()

// permi na ni sya mag run ang search query
// If $search is empty, $searchTerm will be "%%", matching all non-refunded payments.
// If $search has a value, $searchTerm will be "%value%", filtering by that value.
$stmt = $pdo->prepare("
    SELECT p.id, p.amount, p.payment_date, o.order_type, s.student_no, s.first_name, s.last_name
    FROM payments1 p
    JOIN orders1 o ON p.order_id = o.id
    JOIN students1 s ON o.student_id = s.id
    WHERE (s.student_no LIKE :search_term_student_no OR s.last_name LIKE :search_term_last_name)
    AND NOT EXISTS (SELECT 1 FROM refunds1 r WHERE r.payment_id = p.id)
    ORDER BY p.payment_date DESC
    LIMIT 20
");
$searchTerm = "%" . $search . "%";
$stmt->execute([
    ':search_term_student_no' => $searchTerm,
    ':search_term_last_name' => $searchTerm
]);
$payments = $stmt->fetchAll();

// diri mo seek or Fetch refunds history
$stmt_refunds = $pdo->query("
    SELECT r.*, p.amount AS paid_amount, p.payment_date, o.order_type, s.student_no, s.first_name, s.last_name, u.full_name AS processed_by_name
    FROM refunds1 r
    JOIN payments1 p ON r.payment_id = p.id
    JOIN orders1 o ON p.order_id = o.id
    JOIN students1 s ON o.student_id = s.id
    JOIN users1 u ON r.processed_by = u.id
    ORDER BY r.processed_at DESC
    LIMIT 20
");
$refunds_history = $stmt_refunds->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refunds - UMS</title>
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="refunds.php">Refunds</a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4 text-center">Process Refunds</h2>

        <?php if ($refund_success): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($refund_success) ?></div>
        <?php endif; ?>
        <?php if ($refund_error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($refund_error) ?></div>
        <?php endif; ?>

        <!-- Search Payments Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Search Payments to Refund</h5>
                <form method="get" class="row g-3 align-items-center">
                    <div class="col flex-grow-1">
                        <label for="searchPayments" class="visually-hidden">Search student no or last name...</label>
                        <input type="text" name="search" id="searchPayments" class="form-control" placeholder="Search student no or last name..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Search Payments</button>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($search && empty($payments)): ?>
             <div class="alert alert-info" role="alert">No pending payments found matching your search criteria.</div>
        <?php elseif (!empty($payments)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h3 class="mb-0">Payments Available for Refund</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student No</th>
                                    <th>Name</th>
                                    <th>Order Type</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['student_no']) ?></td>
                                    <td><?= htmlspecialchars($p['last_name'] . ', ' . $p['first_name']) ?></td>
                                    <td><?= htmlspecialchars($p['order_type']) ?></td>
                                    <td><?= htmlspecialchars(number_format($p['amount'], 2)) ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d', strtotime($p['payment_date']))) ?></td>
                                    <td>
                                        <form method="post" class="refund-form-inline">
                                            <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                            <div class="input-group input-group-sm">
                                                <textarea name="reason" class="form-control" placeholder="Reason for refund" required rows="1"></textarea>
                                                <button type="submit" name="refund_payment" class="btn btn-warning btn-sm">Refund</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recent Refunds Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                 <h3 class="mb-0">Recent Refunds History</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student No</th>
                                <th>Name</th>
                                <th>Order Type</th>
                                <th>Paid Amount</th>
                                <th>Refunded Amount</th>
                                <th>Reason</th>
                                <th>Processed By</th>
                                <th>Processed At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($refunds_history): ?>
                                <?php foreach ($refunds_history as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['student_no']) ?></td>
                                    <td><?= htmlspecialchars($r['last_name'] . ', ' . $r['first_name']) ?></td>
                                    <td><?= htmlspecialchars($r['order_type']) ?></td>
                                    <td><?= htmlspecialchars(number_format($r['paid_amount'], 2)) ?></td>
                                    <td><?= htmlspecialchars(number_format($r['amount'], 2)) ?></td>
                                    <td><?= htmlspecialchars($r['reason']) ?></td>
                                    <td><?= htmlspecialchars($r['processed_by_name']) ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($r['processed_at']))) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center p-3">No refunds found in history.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
