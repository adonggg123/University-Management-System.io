<?php
session_start();
// Set dummy session values to avoid warnings
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // or any test user ID
}
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'testuser';
}
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'cashier';
}


$host = 'localhost';
$db   = 'university_management_system';
$user = 'root';
$pass = 'quest4inno@server';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle search
$search = trim($_GET['search'] ?? '');
$receipt_list = [];
if ($search) {
    $stmt = $pdo->prepare("
        SELECT r.*, p.amount, o.order_type, s.student_no, s.first_name, s.last_name
        FROM receipts1 r
        JOIN payments p ON r.payment_id = p.id
        JOIN orders o ON p.order_id = o.id
        JOIN students s ON o.student_id = s.id
        WHERE r.receipt_no LIKE :search_term_receipt 
           OR s.student_no LIKE :search_term_student 
           OR s.last_name LIKE :search_term_name
        ORDER BY r.issued_at DESC
        LIMIT 20
    ");
    $searchTerm = "%" . $search . "%";
    $stmt->execute([
        ':search_term_receipt' => $searchTerm,
        ':search_term_student' => $searchTerm,
        ':search_term_name' => $searchTerm
    ]);
    $receipt_list = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("
        SELECT r.*, p.amount, o.order_type, s.student_no, s.first_name, s.last_name
        FROM receipts1 r
        JOIN payments1 p ON r.payment_id = p.id
        JOIN orders1 o ON p.order_id = o.id
        JOIN students1 s ON o.student_id = s.id
        ORDER BY r.issued_at DESC
        LIMIT 20
    ");
    $receipt_list = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts - UMS</title>
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
        <h2 class="mb-4 text-center">View Receipts</h2>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title">Search Receipts</h5>
                <form method="get" action="receipts.php" class="row g-3 align-items-center">
                    <div class="col flex-grow-1">
                        <label for="searchReceipts" class="visually-hidden">Search receipt, student no, or last name...</label>
                        <input type="text" name="search" id="searchReceipts" class="form-control" placeholder="Search receipt no, student no, or last name..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                 <h3 class="mb-0">Receipt List</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt No</th>
                                <th>Student No</th>
                                <th>Name</th>
                                <th>Order Type</th>
                                <th>Amount</th>
                                <th>Issued At</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($receipt_list): ?>
                                <?php foreach ($receipt_list as $r): ?>
                                <tr>
                                    <td><?= htmlspecialchars($r['receipt_no']) ?></td>
                                    <td><?= htmlspecialchars($r['student_no']) ?></td>
                                    <td><?= htmlspecialchars($r['last_name'] . ', ' . $r['first_name']) ?></td>
                                    <td><?= htmlspecialchars($r['order_type']) ?></td>
                                    <td><?= htmlspecialchars(number_format($r['amount'], 2)) ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($r['issued_at']))) ?></td>
                                    <td class="text-center">
                                        <form method="get" action="view_receipt.php" target="_blank" style="display:inline;">
                                            <input type="hidden" name="receipt_no" value="<?= htmlspecialchars($r['receipt_no']) ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-printer me-1"></i> View/Print
                                            </button>
                                        </form>
                                         <a href="download_receipt.php?receipt_no=<?= htmlspecialchars($r['receipt_no']) ?>" class="btn btn-sm btn-outline-secondary ms-1" title="Download PDF">
                                            <i class="bi bi-download me-1"></i> PDF
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center p-3">No receipts found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
             <?php if ($receipt_list): ?>
                <div class="card-footer text-muted">
                    Displaying up to <?= count($receipt_list) ?> receipts.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
