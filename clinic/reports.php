<?php
$host = 'localhost';
$dbname = 'clinic_management';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage(), 3, 'errors.log');
    $error = "An unexpected error occurred. Please try again later.";
}

function generateReport($pdo, $reportType, $status, $page = 1, $limit = 10) {
    $sql = "";
    $countSql = "";
    $params = [];
    $offset = ($page - 1) * $limit;

    switch ($reportType) {
        case 'Patients Report':
            $sql = "SELECT a.id, a.full_name AS name, a.preferred_date AS date, 'Appointments' AS type, 
                           CASE 
                               WHEN a.status = 'completed' THEN 'Complete'
                               WHEN a.decision = 'accepted' THEN 'Accepted'
                               WHEN a.decision = 'declined' THEN 'Declined'
                               ELSE 'Pending'
                           END AS status,
                           a.student_id, a.reason, a.symptoms, a.decline_reason
                    FROM appointments a 
                    WHERE 1=1";
            $countSql = "SELECT COUNT(*) FROM appointments a WHERE 1=1";
            break;

        case 'Appointments Report':
            $sql = "SELECT a.id, a.full_name AS name, a.preferred_date AS date, 'Appointments' AS type, 
                           CASE 
                               WHEN a.status = 'completed' THEN 'Complete'
                               WHEN a.decision = 'accepted' THEN 'Accepted'
                               WHEN a.decision = 'declined' THEN 'Declined'
                               ELSE 'Pending'
                           END AS status,
                           a.student_id, a.reason, a.symptoms, a.decline_reason
                    FROM appointments a 
                    WHERE 1=1";
            $countSql = "SELECT COUNT(*) FROM appointments a WHERE 1=1";
            break;

        case 'Inventory Report':
            $sql = "SELECT i.id, i.name, i.date_added AS date, i.category AS type, i.status, 
                           i.quantity
                    FROM inventory i 
                    WHERE 1=1";
            $countSql = "SELECT COUNT(*) FROM inventory i WHERE 1=1";
            break;

        case 'Monthly Statistic Treatment Report':
            $sql = "SELECT CONCAT(MONTHNAME(a.preferred_date), ' ', YEAR(a.preferred_date)) AS name, 
                           MIN(a.preferred_date) AS date, 
                           'Monthly Statistics' AS type, 
                           'Summary' AS status,
                           COUNT(CASE WHEN a.status = 'completed' THEN 1 END) AS completed_count,
                           COUNT(CASE WHEN a.decision = 'accepted' THEN 1 END) AS accepted_count,
                           COUNT(CASE WHEN a.decision = 'declined' THEN 1 END) AS declined_count
                    FROM appointments a 
                    GROUP BY YEAR(a.preferred_date), MONTH(a.preferred_date)
                    ORDER BY MIN(a.preferred_date) DESC";
            $countSql = "SELECT COUNT(DISTINCT CONCAT(YEAR(a.preferred_date), MONTH(a.preferred_date))) FROM appointments a";
            break;

        default:
            $sql = "SELECT a.id, a.full_name AS name, a.preferred_date AS date, 'Appointments' AS type, 
                           CASE 
                               WHEN a.status = 'completed' THEN 'Complete'
                               WHEN a.decision = 'accepted' THEN 'Accepted'
                               WHEN a.decision = 'declined' THEN 'Declined'
                               ELSE 'Pending'
                           END AS status,
                           a.student_id, a.reason, a.symptoms, a.decline_reason
                    FROM appointments a 
                    WHERE 1=1
                    UNION 
                    SELECT i.id, i.name, i.date_added AS date, i.category AS type, i.status, 
                           i.quantity, NULL, NULL, NULL
                    FROM inventory i 
                    WHERE 1=1";
            $countSql = "SELECT (SELECT COUNT(*) FROM appointments) + (SELECT COUNT(*) FROM inventory) AS total";
            break;
    }

    $bindStatus = false;

    if (!empty($status) && $status != 'All Status' && $reportType != 'Monthly Statistic Treatment Report') {
        if ($reportType == 'Inventory Report') {
            $sql .= " AND i.status = :status";
            $countSql .= " AND i.status = :status";
            $bindStatus = true;
            $params[':status'] = $status;
        } else {
            if ($status == 'Complete') {
                $sql .= " AND a.status = 'completed'";
                $countSql .= " AND a.status = 'completed'";
            } elseif ($status == 'Accepted') {
                $sql .= " AND a.decision = 'accepted'";
                $countSql .= " AND a.decision = 'accepted'";
            } elseif ($status == 'Declined') {
                $sql .= " AND a.decision = 'declined'";
                $countSql .= " AND a.decision = 'declined'";
            }
        }
    }

    if ($reportType != 'Monthly Statistic Treatment Report') {
        $sql .= " LIMIT :limit OFFSET :offset";
    }

    $stmt = $pdo->prepare($sql);

    if ($bindStatus) {
        $stmt->bindValue(':status', $params[':status'], PDO::PARAM_STR);
    }
    if ($reportType != 'Monthly Statistic Treatment Report') {
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    }

    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countStmt = $pdo->prepare($countSql);
    if ($bindStatus) {
        $countStmt->bindValue(':status', $params[':status'], PDO::PARAM_STR);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    $totalPages = ceil($total / $limit);

    return ['reports' => $reports, 'totalPages' => $totalPages, 'total' => $total];
}

$reports = [];
$totalPages = 1;
$total = 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reportType = $_POST['reportType'] ?? 'All Report Types';
    $status = $_POST['status'] ?? 'All Status';
    $result = generateReport($pdo, $reportType, $status, $page, $limit);
    $reports = $result['reports'];
    $totalPages = $result['totalPages'];
    $total = $result['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>USTP Clinic - Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #2A9D8F;
            --secondary-color: #4BA8A0;
            --accent-color: #A3D5D1;
            --light-color: #F1FAEE;
            --dark-color: #1D3A44;
            --success-color: #2A9D8F;
            --warning-color: #E9C46A;
            --danger-color: #E76F51;
            --header-height: 60px;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F4F6F9;
            overflow-x: hidden;
            color: #264653;
        }

        .clinic-header {
            height: var(--header-height);
            background-color: white;
            border-bottom: 1px solid #E2E8F0;
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .brand-name {
            display: flex;
            align-items: center;
        }

        .brand-name img {
            width: 30px;
            height: 30px;
            margin-right: 8px;
        }

        .brand-name h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0;
            font-size: 1.1rem;
        }

        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            height: calc(100vh - var(--header-height));
            width: var(--sidebar-width);
            background-color: white;
            transition: all 0.3s ease;
            z-index: 1020;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
            padding-top: 1rem;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar .nav-link {
            color: #4A5568;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin: 0.3rem 0.8rem;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.75rem;
            margin: 0.3rem auto;
            width: 48px;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            margin-top: var(--header-height);
            transition: all 0.3s ease;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            background-color: white;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            color: var(--dark-color);
            font-weight: 600;
            font-size: 1.1rem;
        }

        .card-subtitle {
            color: #4A5568;
            font-weight: 500;
        }

        .btn-primary, .btn-info, .btn-secondary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-info {
            background: linear-gradient(135deg, #17A2B8, #138496);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #718096, #4A5568);
        }

        .btn-primary:hover, .btn-info:hover, .btn-secondary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #138496, #17A2B8);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #4A5568, #718096);
        }

        .btn-outline-secondary {
            border-color: #718096;
            color: #718096;
        }

        .btn-outline-secondary:hover {
            background-color: var(--light-color);
            border-color: #4A5568;
            color: #4A5568;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .sidebar-toggle:hover {
            color: var(--secondary-color);
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background-color: #F8FAFC;
            color: #4A5568;
            font-weight: 500;
            padding: 0.75rem;
            border-bottom: 1px solid #E2E8F0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #E2E8F0;
        }

        .table-hover tbody tr:hover {
            background-color: var(--light-color);
        }

        .form-control, .form-select {
            border: 1px solid #E2E8F0;
            border-radius: 6px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.1);
            outline: none;
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .success-icon {
            color: var(--success-color);
        }

        .warning-icon {
            color: var(--warning-color);
        }

        .danger-icon {
            color: var(--danger-color);
        }

        .pagination .page-link {
            color: var(--primary-color);
            border: 1px solid #E2E8F0;
            margin: 0 2px;
            border-radius: 4px;
        }

        .pagination .page-link:hover {
            background-color: var(--light-color);
            border-color: var(--primary-color);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            color: #6B7280;
            border-color: #E2E8F0;
            background-color: #F8FAFC;
        }

        .modal-content {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-bottom: none;
            padding: 1rem;
        }

        .modal-body {
            padding: 1.25rem;
            background-color: #F8FAFC;
        }

        .modal-body .mb-3 {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background-color: white;
            border-radius: 6px;
            border: 1px solid #E2E8F0;
        }

        .modal-body .mb-3 strong {
            color: var(--dark-color);
            font-weight: 500;
            min-width: 120px;
        }

        .modal-footer {
            border-top: none;
            padding: 1rem;
            background-color: #F8FAFC;
        }

        @media (max-width: 767.98px) {
            :root {
                --sidebar-width: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .sidebar.collapsed {
                width: 0;
            }

            .mobile-overlay {
                position: fixed;
                top: var(--header-height);
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1019;
                display: none;
            }

            .mobile-overlay.show {
                display: block;
            }

            .table th, .table td {
                font-size: 0.85rem;
                padding: 0.5rem;
            }

            .form-control, .form-select {
                font-size: 0.9rem;
            }

            .btn-sm {
                font-size: 0.8rem;
            }

            .stat-card h2 {
                font-size: 1.5rem;
            }

            .stat-icon {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 575.98px) {
            .clinic-header {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .brand-name img {
                width: 24px;
                height: 24px;
            }

            .brand-name h5 {
                font-size: 1rem;
            }

            .card-title {
                font-size: 0.9rem;
            }

            .card-subtitle {
                font-size: 0.85rem;
            }

            .table th, .table td {
                font-size: 0.75rem;
                padding: 0.3rem;
            }

            .pagination-sm .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .stat-card h2 {
                font-size: 1.25rem;
            }

            .stat-icon {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <header class="clinic-header fixed-top d-flex align-items-center px-3">
        <div class="d-flex align-items-center">
            <button id="sidebarToggle" class="sidebar-toggle me-2">
                <i class="bi bi-list"></i>
            </button>
            <div class="brand-name">
                <img src="Image/clinic.gif" alt="USTP Clinic Logo" class="logo" />
                <h5>USTP CLINIC</h5>
            </div>
        </div>
    </header>

    <nav class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="admin.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="patient.php">
                    <i class="bi bi-people"></i>
                    <span>Patients</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_appointment.php">
                    <i class="bi bi-calendar-check"></i>
                    <span>Appointments</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="inventory.php">
                    <i class="bi bi-box-seam"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="reports.php">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-content" id="mainContent">
        <div class="container-fluid p-0">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-0">Reports</h5>
                            <p class="text-muted small mb-0">Generate and manage clinic reports</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3">Filter Reports</h6>
                            <form method="POST" action="">
                                <div class="row g-3">
                                    <div class="col-md-3 col-sm-6">
                                        <select class="form-select" name="reportType" id="reportType">
                                            <option value="All Report Types" <?php echo isset($_POST['reportType']) && $_POST['reportType'] === 'All Report Types' ? 'selected' : ''; ?>>All Report Types</option>
                                            <option value="Patients Report" <?php echo isset($_POST['reportType']) && $_POST['reportType'] === 'Patients Report' ? 'selected' : ''; ?>>Patients Report</option>
                                            <option value="Appointments Report" <?php echo isset($_POST['reportType']) && $_POST['reportType'] === 'Appointments Report' ? 'selected' : ''; ?>>Appointments Report</option>
                                            <option value="Inventory Report" <?php echo isset($_POST['reportType']) && $_POST['reportType'] === 'Inventory Report' ? 'selected' : ''; ?>>Inventory Report</option>
                                            <option value="Monthly Statistic Treatment Report" <?php echo isset($_POST['reportType']) && $_POST['reportType'] === 'Monthly Statistic Treatment Report' ? 'selected' : ''; ?>>Monthly Statistic Treatment Report</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <select class="form-select" name="status" id="statusFilter">
                                        </select>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-sm btn-primary">Generate Report</button>
                                    <button type="reset" class="btn btn-sm btn-outline-secondary ms-2" onclick="resetForm()">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-subtitle mb-0">Report Results</h6>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-secondary" onclick="exportCSV()">
                                        <i class="bi bi-download me-1"></i><span class="d-none d-sm-inline">Export</span>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                        <i class="bi bi-printer me-1"></i><span class="d-none d-sm-inline">Print</span>
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Status</th>
                                            <th scope="col">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if (!empty($reports)) {
                                            $start = ($page - 1) * $limit + 1;
                                            foreach ($reports as $index => $report) {
                                                echo "<tr>";
                                                echo "<td>" . ($start + $index) . "</td>";
                                                echo "<td>" . htmlspecialchars($report['name']) . "</td>";
                                                echo "<td>" . htmlspecialchars($report['date']) . "</td>";
                                                echo "<td>" . htmlspecialchars($report['type']) . "</td>";
                                                echo "<td>" . htmlspecialchars($report['status']) . "</td>";
                                                echo "<td>";
                                                echo "<button class='btn btn-sm btn-info view-report-btn' 
                                                            data-id='" . htmlspecialchars($report['id'] ?? '') . "'
                                                            data-name='" . htmlspecialchars($report['name']) . "'
                                                            data-date='" . htmlspecialchars($report['date']) . "'
                                                            data-type='" . htmlspecialchars($report['type']) . "'
                                                            data-status='" . htmlspecialchars($report['status']) . "'
                                                            data-student-id='" . htmlspecialchars($report['student_id'] ?? '') . "'
                                                            data-reason='" . htmlspecialchars($report['reason'] ?? '') . "'
                                                            data-symptoms='" . htmlspecialchars($report['symptoms'] ?? '') . "'
                                                            data-decline-reason='" . htmlspecialchars($report['decline_reason'] ?? '') . "'
                                                            data-quantity='" . htmlspecialchars($report['quantity'] ?? '') . "'
                                                            data-completed-count='" . htmlspecialchars($report['completed_count'] ?? '') . "'
                                                            data-accepted-count='" . htmlspecialchars($report['accepted_count'] ?? '') . "'
                                                            data-declined-count='" . htmlspecialchars($report['declined_count'] ?? '') . "'
                                                            data-bs-toggle='modal'
                                                            data-bs-target='#reportModal'>";
                                                echo "<i class='bi bi-eye me-1'></i> View</button>";
                                                echo "</td>";
                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center text-muted'>No reports available</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Showing <span class="fw-semibold"><?php echo count($reports) > 0 ? ($page - 1) * $limit + 1 : 0; ?></span> to 
                                    <span class="fw-semibold"><?php echo min($page * $limit, $total); ?></span> of 
                                    <span class="fw-semibold"><?php echo $total; ?></span> entries
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&reportType=<?php echo urlencode($reportType ?? ''); ?>&status=<?php echo urlencode($status ?? ''); ?>" aria-label="Previous">
                                                <span aria-hidden="true">«</span>
                                            </a>
                                        </li>
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&reportType=<?php echo urlencode($reportType ?? ''); ?>&status=<?php echo urlencode($status ?? ''); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&reportType=<?php echo urlencode($reportType ?? ''); ?>&status=<?php echo urlencode($status ?? ''); ?>" aria-label="Next">
                                                <span aria-hidden="true">»</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-4">
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted">Total Records</h6>
                                    <h2 class="mb-0"><?php echo $total; ?></h2>
                                </div>
                                <div class="stat-icon">
                                    <i class="bi bi-file-text"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted">Completed</h6>
                                    <h2 class="mb-0"><?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Complete')); ?></h2>
                                </div>
                                <div class="stat-icon success-icon">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted">Accepted</h6>
                                    <h2 class="mb-0"><?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Accepted')); ?></h2>
                                </div>
                                <div class="stat-icon warning-icon">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted">Declined</h6>
                                    <h2 class="mb-0"><?php echo count(array_filter($reports, fn($r) => $r['status'] === 'Declined')); ?></h2>
                                </div>
                                <div class="stat-icon danger-icon">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="reportModalLabel">Report Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Name</strong>
                                <span id="modal-name"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Date</strong>
                                <span id="modal-date"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Type</strong>
                                <span id="modal-type"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Status</strong>
                                <span id="modal-status"></span>
                            </div>
                            <div class="mb-3" id="modal-student-id" style="display: none;">
                                <strong>Student ID</strong>
                                <span id="modal-student-id-value"></span>
                            </div>
                            <div class="mb-3" id="modal-reason" style="display: none;">
                                <strong>Reason</strong>
                                <span id="modal-reason-value"></span>
                            </div>
                            <div class="mb-3" id="modal-symptoms" style="display: none;">
                                <strong>Symptoms</strong>
                                <span id="modal-symptoms-value"></span>
                            </div>
                            <div class="mb-3" id="modal-decline-reason" style="display: none;">
                                <strong>Decline Reason</strong>
                                <span id="modal-decline-reason-value"></span>
                            </div>
                            <div class="mb-3" id="modal-quantity" style="display: none;">
                                <strong>Quantity</strong>
                                <span id="modal-quantity-value"></span>
                            </div>
                            <div class="mb-3" id="modal-completed-count" style="display: none;">
                                <strong>Completed Count</strong>
                                <span id="modal-completed-count-value"></span>
                            </div>
                            <div class="mb-3" id="modal-accepted-count" style="display: none;">
                                <strong>Accepted Count</strong>
                                <span id="modal-accepted-count-value"></span>
                            </div>
                            <div class="mb-3" id="modal-declined-count" style="display: none;">
                                <strong>Declined Count</strong>
                                <span id="modal-declined-count-value"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const reportTypeSelect = document.getElementById('reportType');
            const statusFilterSelect = document.getElementById('statusFilter');

            const statusOptions = {
                'All Report Types': [
                    { value: 'All Status', text: 'All Status' },
                    { value: 'Accepted', text: 'Accepted' },
                    { value: 'Declined', text: 'Declined' },
                    { value: 'In Stock', text: 'In Stock' },
                    { value: 'Low Stock', text: 'Low Stock' },
                    { value: 'Out of Stock', text: 'Out of Stock' }
                ],
                'Patients Report': [
                    { value: 'All Status', text: 'All Status' },
                    { value: 'Accepted', text: 'Accepted' },
                    { value: 'Declined', text: 'Declined' }
                ],
                'Appointments Report': [
                    { value: 'All Status', text: 'All Status' },
                    { value: 'Accepted', text: 'Accepted' },
                    { value: 'Declined', text: 'Declined' }
                ],
                'Inventory Report': [
                    { value: 'All Status', text: 'All Status' },
                    { value: 'In Stock', text: 'In Stock' },
                    { value: 'Low Stock', text: 'Low Stock' },
                    { value: 'Out of Stock', text: 'Out of Stock' }
                ],
                'Monthly Statistic Treatment Report': [
                    { value: 'All Status', text: 'All Status' }
                ]
            };

            function updateStatusFilter(reportType) {
                const options = statusOptions[reportType] || statusOptions['All Report Types'];
                statusFilterSelect.innerHTML = '';
                options.forEach(option => {
                    const opt = document.createElement('option');
                    opt.value = option.value;
                    opt.textContent = option.text;
                    if (option.value === '<?php echo isset($_POST['status']) ? addslashes($_POST['status']) : 'All Status'; ?>') {
                        opt.selected = true;
                    }
                    statusFilterSelect.appendChild(opt);
                });
            }

            updateStatusFilter(reportTypeSelect.value);

            reportTypeSelect.addEventListener('change', function() {
                updateStatusFilter(this.value);
            });

            window.resetForm = function() {
                reportTypeSelect.value = 'All Report Types';
                updateStatusFilter('All Report Types');
            };

            function handleViewportChange() {
                if (window.innerWidth < 768) {
                    sidebar.classList.remove('collapsed');
                    sidebar.classList.remove('show');
                    mainContent.classList.add('expanded');
                } else {
                    mobileOverlay.classList.remove('show');
                    if (sidebar.classList.contains('collapsed')) {
                        mainContent.classList.add('expanded');
                    } else {
                        mainContent.classList.remove('expanded');
                    }
                }
            }

            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    sidebar.classList.toggle('show');
                    mobileOverlay.classList.toggle('show');
                } else {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                }
            });

            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                mobileOverlay.classList.remove('show');
            });

            document.querySelectorAll('.view-report-btn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('modal-name').textContent = this.dataset.name;
                    document.getElementById('modal-date').textContent = this.dataset.date;
                    document.getElementById('modal-type').textContent = this.dataset.type;
                    document.getElementById('modal-status').textContent = this.dataset.status;

                    const isAppointment = this.dataset.type === 'Appointments';
                    document.getElementById('modal-student-id').style.display = isAppointment && this.dataset.studentId ? 'flex' : 'none';
                    document.getElementById('modal-student-id-value').textContent = this.dataset.studentId || '';
                    document.getElementById('modal-reason').style.display = isAppointment && this.dataset.reason ? 'flex' : 'none';
                    document.getElementById('modal-reason-value').textContent = this.dataset.reason || '';
                    document.getElementById('modal-symptoms').style.display = isAppointment && this.dataset.symptoms ? 'flex' : 'none';
                    document.getElementById('modal-symptoms-value').textContent = this.dataset.symptoms || '';
                    document.getElementById('modal-decline-reason').style.display = isAppointment && this.dataset.declineReason ? 'flex' : 'none';
                    document.getElementById('modal-decline-reason-value').textContent = this.dataset.declineReason || '';

                    document.getElementById('modal-quantity').style.display = this.dataset.type !== 'Appointments' && this.dataset.quantity ? 'flex' : 'none';
                    document.getElementById('modal-quantity-value').textContent = this.dataset.quantity || '';

                    const isMonthlyStats = this.dataset.type === 'Monthly Statistics';
                    document.getElementById('modal-completed-count').style.display = isMonthlyStats ? 'flex' : 'none';
                    document.getElementById('modal-completed-count-value').textContent = this.dataset.completedCount || '';
                    document.getElementById('modal-accepted-count').style.display = isMonthlyStats ? 'flex' : 'none';
                    document.getElementById('modal-accepted-count-value').textContent = this.dataset.acceptedCount || '';
                    document.getElementById('modal-declined-count').style.display = isMonthlyStats ? 'flex' : 'none';
                    document.getElementById('modal-declined-count-value').textContent = this.dataset.declinedCount || '';
                });
            });

            window.exportCSV = function() {
                const reports = <?php echo json_encode($reports); ?>;
                let csvContent = 'ID,Name,Date,Type,Status,Student ID,Reason,Symptoms,Decline Reason,Quantity,Completed Count,Accepted Count,Declined Count\n';
                
                reports.forEach(report => {
                    csvContent += `"${report.id || ''}","${report.name.replace(/"/g, '""')}","${report.date}","${report.type}","${report.status}",` +
                                  `"${(report.student_id || '').replace(/"/g, '""')}","${(report.reason || '').replace(/"/g, '""')}",` +
                                  `"${(report.symptoms || '').replace(/"/g, '""')}","${(report.decline_reason || '').replace(/"/g, '""')}",` +
                                  `"${report.quantity || ''}","${report.completed_count || ''}","${report.accepted_count || ''}",` +
                                  `"${report.declined_count || ''}"\n`;
                });
                
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'reports_export.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            };

            handleViewportChange();
            window.addEventListener('resize', handleViewportChange);
        });
    </script>
</body>
</html>