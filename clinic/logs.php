<?php
$servername = "localhost";
$username = "root";
$password = "quest4inno@server";
$dbname = "university_management_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Initialize variables to prevent undefined errors
$logs = [];
$total_logs = 0;
$total_pages = 1;

// Handle filters and pagination
$log_type = isset($_GET['log_type']) ? $_GET['log_type'] : 'All Log Types';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$query = "SELECT id, action, module, date_time, status FROM logs WHERE 1=1";
$count_query = "SELECT COUNT(*) FROM logs WHERE 1=1";
$params = [];

if ($log_type !== 'All Log Types') {
    $query .= " AND module = ?";
    $count_query .= " AND module = ?";
    $params[] = $log_type;
}

if ($from_date) {
    $query .= " AND date_time >= ?";
    $count_query .= " AND date_time >= ?";
    $params[] = $from_date . ' 00:00:00';
}

// Append LIMIT and OFFSET directly to avoid binding issues
$query .= " ORDER BY date_time DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

try {
    // Execute main query
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Execute count query
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->execute($params);
    $total_logs = $count_stmt->fetchColumn();
    $total_pages = ceil($total_logs / $limit);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $logs = []; // Ensure $logs is defined even on error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>USTP Clinic - System Logs</title>
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

        .btn-primary, .btn-secondary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #718096, #4A5568);
        }

        .btn-primary:hover, .btn-secondary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
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

        .badge.bg-success {
            background-color: var(--success-color) !important;
        }

        .badge.bg-danger {
            background-color: var(--danger-color) !important;
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

        .empty-state {
            color: #4A5568;
        }

        .empty-icon {
            font-size: 3rem;
            color: #6B7280;
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
        }
    </style>
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Header -->
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

    <!-- Sidebar -->
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
                <a class="nav-link active" href="logs.php">
                    <i class="bi bi-journal-text"></i>
                    <span>Logs</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="container-fluid p-0">
            <!-- Error Message -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Page Title -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-0">System Logs</h5>
                            <p class="text-muted small mb-0">Track all system activities</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-subtitle mb-3">Filter Logs</h6>
                            <form method="GET" action="">
                                <div class="row g-3">
                                    <div class="col-md-6 col-sm-6">
                                        <select class="form-select" name="log_type">
                                            <option value="All Log Types" <?php echo $log_type === 'All Log Types' ? 'selected' : ''; ?>>All Log Types</option>
                                            <option value="Appointments" <?php echo $log_type === 'Appointments' ? 'selected' : ''; ?>>Appointment Actions</option>
                                            <option value="Inventory" <?php echo $log_type === 'Inventory' ? 'selected' : ''; ?>>Inventory Actions</option>
                                            <option value="Reports" <?php echo $log_type === 'Reports' ? 'selected' : ''; ?>>Report Generation</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <input type="date" class="form-control" name="from_date" value="<?php echo htmlspecialchars($from_date); ?>" placeholder="From Date">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-sm btn-primary">Apply Filter</button>
                                    <button type="reset" class="btn btn-sm btn-outline-secondary ms-2" onclick="window.location.href='logs.php'">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-subtitle mb-0">Log Results</h6>
                                <button class="btn btn-sm btn-outline-secondary" onclick="exportCSV()">
                                    <i class="bi bi-download me-1"></i><span class="d-none d-sm-inline">Export</span>
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Action</th>
                                            <th scope="col">Module</th>
                                            <th scope="col">Date & Time</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($logs)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <div class="empty-state">
                                                        <i class="bi bi-exclamation-circle empty-icon"></i>
                                                        <p class="mb-0">No logs available at the moment</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $start = ($page - 1) * $limit + 1; ?>
                                            <?php foreach ($logs as $index => $log): ?>
                                                <tr>
                                                    <td><?php echo $start + $index; ?></td>
                                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['module']); ?></td>
                                                    <td><?php echo htmlspecialchars($log['date_time']); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $log['status'] === 'Success' ? 'bg-success' : 'bg-danger'; ?>">
                                                            <?php echo htmlspecialchars($log['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    Showing <span class="fw-semibold"><?php echo !empty($logs) ? ($page - 1) * $limit + 1 : 0; ?></span> to 
                                    <span class="fw-semibold"><?php echo !empty($logs) ? min($page * $limit, $total_logs) : 0; ?></span> of 
                                    <span class="fw-semibold"><?php echo $total_logs; ?></span> entries
                                </div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&log_type=<?php echo urlencode($log_type); ?>&from_date=<?php echo urlencode($from_date); ?>" aria-label="Previous">
                                                <span aria-hidden="true">«</span>
                                            </a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&log_type=<?php echo urlencode($log_type); ?>&from_date=<?php echo urlencode($from_date); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&log_type=<?php echo urlencode($log_type); ?>&from_date=<?php echo urlencode($from_date); ?>" aria-label="Next">
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
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileOverlay = document.getElementById('mobileOverlay');

            function handleViewportChange() {
                if (window.innerWidth < 768) {
                    sidebarCanontext.classList.remove('collapsed');
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

            window.exportCSV = function() {
                const logs = <?php echo json_encode($logs); ?>;
                let csvContent = 'Action,Module,Date Time,Status\n';
                
                logs.forEach(log => {
                    csvContent += `"${log.action.replace(/"/g, '""')}","${log.module}","${log.date_time}","${log.status}"\n`;
                });
                
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'logs_export.csv');
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
<?php $conn = null; ?>