<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle appointment actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $appointment_id = $_POST['appointment_id'];
        $action = $_POST['action'];

        if ($action === 'accept') {
            $stmt = $conn->prepare("UPDATE appointments SET decision = 'accepted' WHERE id = ?");
            $stmt->execute([$appointment_id]);
        } elseif ($action === 'decline' && isset($_POST['decline_reason'])) {
            $decline_reason = $_POST['decline_reason'];
            $stmt = $conn->prepare("UPDATE appointments SET decision = 'declined', decline_reason = ? WHERE id = ?");
            $stmt->execute([$decline_reason, $appointment_id]);
        }
    }

    // Handle filter for appointments
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $appointment_query = "SELECT id, full_name, student_id, reason, symptoms, preferred_date, decision 
              FROM appointments 
              WHERE status = 'sent'";
    if ($filter === 'accepted') {
        $appointment_query .= " AND decision = 'accepted'";
    } elseif ($filter === 'declined') {
        $appointment_query .= " AND decision = 'declined'";
    } elseif ($filter === 'pending') {
        $appointment_query .= " AND decision IS NULL";
    }
    $appointment_query .= " ORDER BY preferred_date DESC";

    $stmt = $conn->prepare($appointment_query);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch notifications for admin
    $stmt = $conn->prepare("
        SELECT n.id, n.appointment_id, n.message, n.is_read, a.full_name, a.student_id, a.course, a.btled_specialization, 
               a.year_level, a.dob, a.sex, a.mobile_number, a.email, a.reason, a.medical_conditions, 
               a.symptoms, a.preferred_date, a.emergency_name, a.emergency_relationship, a.emergency_mobile, 
               a.address
        FROM notifications n
        JOIN appointments a ON n.appointment_id = a.id
        WHERE n.user_type = 'admin' AND n.is_read = 0
        ORDER BY n.created_at DESC
    ");
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch pending approvals count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE status = 'sent' AND decision IS NULL");
    $stmt->execute();
    $pending_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Fetch total accepted appointments count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE decision = 'accepted'
    ");
    $stmt->execute();
    $total_accepted_appointments = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Fetch total patients
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT student_id) as count 
        FROM appointments 
        WHERE decision = 'accepted'
    ");
    $stmt->execute();
    $total_patients = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Fetch appointments today count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM appointments 
        WHERE decision = 'accepted' AND DATE(preferred_date) = CURDATE()
    ");
    $stmt->execute();
    $appointments_today = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Fetch gender distribution
    $stmt = $conn->prepare("
        SELECT 
            CASE 
                WHEN a.sex = 'Male' THEN 'Male'
                WHEN a.sex = 'Female' THEN 'Female'
                ELSE 'Other'
            END as gender, 
            COUNT(DISTINCT a.student_id) as count 
        FROM appointments a
        WHERE a.decision = 'accepted'
        AND a.id = (
            SELECT MAX(id)
            FROM appointments
            WHERE student_id = a.student_id
            AND decision = 'accepted'
        )
        GROUP BY gender
    ");
    $stmt->execute();
    $gender_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $gender_counts = ['Male' => 0, 'Female' => 0, 'Other' => 0];
    foreach ($gender_data as $row) {
        $gender_counts[$row['gender']] = $row['count'];
    }

    // Fetch monthly treatment statistics for the selected year
    $selected_year = isset($_POST['year']) ? (int)$_POST['year'] : date('Y');
    $stmt = $conn->prepare("
        SELECT 
            MONTH(preferred_date) as month,
            COUNT(DISTINCT CASE WHEN decision = 'accepted' THEN student_id END) as done,
            COUNT(DISTINCT CASE WHEN decision = 'declined' THEN student_id END) as undone
        FROM appointments 
        WHERE YEAR(preferred_date) = :year 
        AND decision IN ('accepted', 'declined')
        AND preferred_date IS NOT NULL
        GROUP BY MONTH(preferred_date)
    ");
    $stmt->execute(['year' => $selected_year]);
    $treatment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $done_data = array_fill(1, 12, 0);
    $undone_data = array_fill(1, 12, 0);
    foreach ($treatment_data as $row) {
        $done_data[$row['month']] = $row['done'];
        $undone_data[$row['month']] = $row['undone'];
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>USTP Clinic - Admin Dashboard</title>
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

        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card .card-body {
            z-index: 1;
        }

        .stat-icon {
            position: absolute;
            bottom: -15px;
            right: -15px;
            font-size: 5rem;
            opacity: 0.1;
            color: var(--dark-color);
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

        .modal-body p {
            margin-bottom: 0.5rem;
        }

        .modal-body strong {
            color: var(--dark-color);
        }

        .modal-footer {
            border-top: none;
            padding: 1rem;
            background-color: #F8FAFC;
        }

        .btn-primary, .btn-success, .btn-danger, .btn-secondary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #2A7B6E);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #A43C3A);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #718096, #4A5568);
        }

        .btn-primary:hover, .btn-success:hover, .btn-danger:hover, .btn-secondary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #2A7B6E, var(--success-color));
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #A43C3A, var(--danger-color));
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #4A5568, #718096);
        }

        .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline-primary:hover {
            background-color: var(--light-color);
            border-color: var(--primary-color);
            color: var(--primary-color);
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

        .notification-panel {
            position: fixed;
            top: var(--header-height);
            right: -350px;
            width: 100%;
            max-width: 350px;
            height: calc(100vh - var(--header-height));
            background-color: white;
            z-index: 1040;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            padding: 1rem;
            overflow-y: auto;
        }

        .notification-panel.show {
            right: 0;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }

        .notification-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .notification-item {
            padding: 0.75rem;
            border-bottom: 1px solid #E2E8F0;
            cursor: pointer;
            transition: background-color 0.2s ease;
            display: flex;
            align-items: center;
        }

        .notification-item:hover {
            background-color: var(--light-color);
        }

        .notification-content {
            display: flex;
            align-items: flex-start;
            width: 100%;
            gap: 0.75rem;
        }

        .notification-icon {
            font-size: 1.2rem;
            color: var(--primary-color);
            flex-shrink: 0;
        }

        .notification-details {
            flex-grow: 1;
        }

        .notification-message {
            font-size: 0.9rem;
            color: #264653;
            margin: 0;
            line-height: 1.4;
        }

        .notification-dismiss {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            line-height: 1;
            border-radius: 50%;
            display: none;
        }

        .notification-item:hover .notification-dismiss {
            display: block;
        }

        .notification-item .text-muted {
            font-size: 0.75rem;
        }

        #calendar {
            background-color: white;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1rem;
        }

        #calendar .table {
            margin-bottom: 0;
            width: 100%;
            table-layout: fixed;
            border-collapse: separate;
            border-spacing: 0;
        }

        #calendar .table th {
            background-color: #F8FAFC;
            color: #4A5568;
            font-weight: 500;
            padding: 0.75rem;
            border-bottom: 1px solid #E2E8F0;
            position: sticky;
            top: 0;
            z-index: 10;
            text-align: center;
        }

        #calendar .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border: 1px solid #E2E8F0;
            text-align: center;
        }

        #calendar .table td.bg-primary {
            background-color: var(--primary-color);
            color: white;
            border-radius: 4px;
        }

        .card.h-100 {
            min-height: 350px;
            max-height: 400px;
            display: flex;
            flex-direction: column;
        }

        #calendar .table-responsive {
            flex-grow: 1;
            overflow: hidden;
        }

        #calendar .d-flex {
            margin-bottom: 0.5rem;
            flex-shrink: 0;
        }

        #calendarMonth {
            font-size: clamp(1rem, 2vw, 1.2rem);
        }

        .chart-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .chart-controls-buttons {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        #yearInput {
            border-radius: 6px;
            border: 1px solid #E2E8F0;
            padding: 0.25rem 0.5rem;
            width: 80px;
            height: 31px;
            font-size: 0.875rem;
            text-align: center;
            box-sizing: border-box;
        }

        #yearInput:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.1);
            outline: none;
        }

        .chart-container {
            position: relative;
            height: 40vh;
            width: 100%;
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

            .notification-panel {
                max-width: 100%;
                padding: 0.5rem;
            }

            .notification-item {
                padding: 0.5rem;
            }

            .notification-message {
                font-size: 0.85rem;
            }

            .notification-icon {
                font-size: 1rem;
            }

            #calendar {
                padding: 0.75rem;
            }

            #calendar .table td,
            #calendar .table th {
                padding: 0.5rem;
                font-size: clamp(0.65rem, 1.2vw, 0.8rem);
            }

            .card.h-100 {
                min-height: 300px;
                max-height: 350px;
            }

            #calendarMonth {
                font-size: clamp(0.9rem, 1.8vw, 1rem);
            }

            .chart-controls {
                flex-direction: column;
                align-items: flex-start;
            }

            .chart-controls-buttons {
                margin-top: 0.5rem;
                flex-wrap: wrap;
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

            #liveClock {
                font-size: 0.8rem;
            }

            .card-title {
                font-size: 0.9rem;
            }

            .display-6 {
                font-size: 1.2rem;
            }

            #calendar {
                padding: 0.5rem;
            }

            #calendar .table td,
            #calendar .table th {
                padding: 0.3rem;
                font-size: clamp(0.6rem, 1vw, 0.7rem);
            }

            .chart-controls-buttons .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            #yearInput {
                width: 70px;
                height: 28px;
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
        <div class="ms-auto d-flex align-items-center">
            <button id="notificationToggle" class="sidebar-toggle position-relative me-3">
                <i class="bi bi-bell"></i>
                <?php if ($pending_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">
                        <span class="visually-hidden">New notifications</span>
                    </span>
                <?php endif; ?>
            </button>
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="Image/man.gif" class="rounded-circle me-2" alt="Profile" width="30" height="30" />
                    <span class="d-none d-md-inline-block">Admin</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                    <li><hr class="dropdown-divider" /></li>
                    <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="admin.php">
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
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-2" id="greeting">Hello, Admin!</h5>
                            <p id="liveClock" class="text-muted mb-0">Loading time...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4 col-sm-6">
                    <div class="card stat-card bg-white">
                        <div class="card-body">
                            <h5 class="card-title">Appointments Today</h5>
                            <p class="card-text display-6"><?php echo htmlspecialchars($appointments_today); ?></p>
                            <div class="stat-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="card stat-card bg-white">
                        <div class="card-body">
                            <h5 class="card-title">Pending Approvals</h5>
                            <p class="card-text display-6"><?php echo htmlspecialchars($pending_count); ?></p>
                            <div class="stat-icon">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-12">
                    <div class="card stat-card bg-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Patients</h5>
                            <p class="card-text display-6"><?php echo htmlspecialchars($total_patients); ?></p>
                            <div class="stat-icon">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Calendar -->
            <div class="row g-3 mb-4">
                <div class="col-lg-6 col-md-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Gender Distribution</h5>
                            <canvas id="genderChart" style="max-height: 350px;"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12">
                    <div class="card h-100">
                        <div class="card-body">
                            <div id="calendar" class="border rounded p-2 p-sm-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <button id="prevMonth" class="btn btn-outline-primary btn-sm">❮</button>
                                    <h6 id="calendarMonth" class="text-center mb-0"></h6>
                                    <button id="nextMonth" class="btn btn-outline-primary btn-sm">❯</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered text-center mb-0">
                                        <thead>
                                            <tr>
                                                <th>Sun</th>
                                                <th>Mon</th>
                                                <th>Tue</th>
                                                <th>Wed</th>
                                                <th>Thu</th>
                                                <th>Fri</th>
                                                <th>Sat</th>
                                            </tr>
                                        </thead>
                                        <tbody id="calendarDays"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Treatment Statistics Chart -->
            <div class="row g-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="chart-controls">
                                <h5 class="card-title mb-0">Statistical Treatment</h5>
                                <div class="chart-controls-buttons">
                                    <input type="number" id="yearInput" class="form-control form-control-sm" value="<?php echo htmlspecialchars($selected_year); ?>" min="2000" max="2100" />
                                    <button class="btn btn-sm btn-outline-primary" onclick="changeChartType('bar')">Bar</button>
                                    <button class="btn btn-sm btn-outline-success" onclick="changeChartType('line')">Line</button>
                                </div>
                            </div>
                            <div class="chart-container">
                                <canvas id="treatmentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Notification Panel -->
    <div id="notificationPanel" class="notification-panel">
        <div class="notification-header">
            <h5>Notifications</h5>
            <button id="closeNotificationPanel" class="btn-close" aria-label="Close"></button>
        </div>
        <div id="noNotificationsMessage" class="<?php echo empty($notifications) ? '' : 'd-none'; ?>">
            <p class="text-muted text-center">No new notifications.</p>
        </div>
        <ul class="notification-list" id="notificationList">
            <?php foreach ($notifications as $index => $notification): ?>
                <li class="notification-item" 
                    data-bs-toggle="modal" 
                    data-bs-target="#appointmentModal<?php echo htmlspecialchars($notification['appointment_id']); ?>" 
                    data-notification-id="<?php echo htmlspecialchars($notification['id']); ?>">
                    <div class="notification-content">
                        <div class="notification-icon">
                            <i class="bi bi-bell-fill text-primary"></i>
                        </div>
                        <div class="notification-details">
                            <p class="notification-message mb-1">
                                <?php 
                                    $message = htmlspecialchars($notification['message']);
                                    echo strlen($message) > 60 ? substr($message, 0, 60) . '...' : $message;
                                ?>
                            </p>
                            <small class="text-muted">
                                From: <?php echo htmlspecialchars($notification['full_name']); ?> • 
                                <?php echo date('M d, H:i', strtotime('now')); ?>
                            </small>
                        </div>
                        <button class="notification-dismiss btn btn-sm btn-outline-secondary ms-auto" 
                                onclick="dismissNotification(event, '<?php echo htmlspecialchars($notification['id']); ?>')">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Appointment Modals -->
    <?php foreach ($notifications as $notification): ?>
        <div class="modal fade" id="appointmentModal<?php echo htmlspecialchars($notification['appointment_id']); ?>" tabindex="-1" aria-labelledby="appointmentModalLabel<?php echo htmlspecialchars($notification['appointment_id']); ?>" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="appointmentModalLabel<?php echo htmlspecialchars($notification['appointment_id']); ?>">Appointment Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Personal Details</h6>
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($notification['full_name']); ?></p>
                        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($notification['student_id']); ?></p>
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($notification['course']); ?>
                            <?php if ($notification['course'] === 'BTLED' && $notification['btled_specialization']): ?>
                                (<?php echo htmlspecialchars($notification['btled_specialization']); ?>)
                            <?php endif; ?>
                        </p>
                        <p><strong>Year Level:</strong> <?php echo htmlspecialchars($notification['year_level']); ?></p>
                        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($notification['dob']); ?></p>
                        <p><strong>Sex:</strong> <?php echo htmlspecialchars($notification['sex']); ?></p>
                        
                        <h6>Contact Information</h6>
                        <p><strong>Mobile Number:</strong> <?php echo htmlspecialchars($notification['mobile_number']); ?></p>
                        <?php if ($notification['email']): ?>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($notification['email']); ?></p>
                        <?php endif; ?>
                        
                        <h6>Health-Related Details</h6>
                        <p><strong>Reason:</strong> <?php echo htmlspecialchars($notification['reason']); ?></p>
                        <?php if ($notification['medical_conditions']): ?>
                            <p><strong>Medical Conditions:</strong> <?php echo htmlspecialchars($notification['medical_conditions']); ?></p>
                        <?php endif; ?>
                        <?php if ($notification['symptoms']): ?>
                            <p><strong>Symptoms:</strong> <?php echo htmlspecialchars($notification['symptoms']); ?></p>
                        <?php endif; ?>
                        
                        <h6>Appointment Details</h6>
                        <p><strong>Preferred Date:</strong> <?php echo htmlspecialchars($notification['preferred_date']); ?></p>
                        
                        <h6>Emergency Contact</h6>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($notification['emergency_name']); ?></p>
                        <p><strong>Relationship:</strong> <?php echo htmlspecialchars($notification['emergency_relationship']); ?></p>
                        <p><strong>Mobile Number:</strong> <?php echo htmlspecialchars($notification['emergency_mobile']); ?></p>
                        <?php if ($notification['address']): ?>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($notification['address']); ?></p>
                        <?php endif; ?>
                        
                        <div id="declineReasonGroup<?php echo htmlspecialchars($notification['appointment_id']); ?>" class="mt-3" style="display: none;">
                            <label for="declineReason<?php echo htmlspecialchars($notification['appointment_id']); ?>" class="form-label">Decline Reason:</label>
                            <textarea class="form-control" id="declineReason<?php echo htmlspecialchars($notification['appointment_id']); ?>" rows="4" placeholder="Enter reason for declining"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" onclick="handleDecision(<?php echo htmlspecialchars($notification['appointment_id']); ?>, 'accepted')">Accept</button>
                        <button type="button" class="btn btn-danger" onclick="toggleDeclineReason(<?php echo htmlspecialchars($notification['appointment_id']); ?>)">Decline</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Bootstrap JS and Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Sidebar and Mobile Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileOverlay = document.getElementById('mobileOverlay');
            
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
            
            handleViewportChange();
            window.addEventListener('resize', handleViewportChange);
        });

        // Notification Panel
        document.addEventListener('DOMContentLoaded', function() {
            const notificationToggle = document.getElementById('notificationToggle');
            const notificationPanel = document.getElementById('notificationPanel');
            const closeNotificationPanel = document.getElementById('closeNotificationPanel');
            const noNotificationsMessage = document.getElementById('noNotificationsMessage');
            const notificationList = document.getElementById('notificationList');

            notificationToggle.addEventListener('click', function(e) {
                notificationPanel.classList.toggle('show');
            });

            closeNotificationPanel.addEventListener('click', function() {
                notificationPanel.classList.remove('show');
            });

            document.addEventListener('click', function(e) {
                if (!notificationPanel.contains(e.target) && e.target !== notificationToggle && !notificationToggle.contains(e.target)) {
                    notificationPanel.classList.remove('show');
                }
            });

            notificationList.addEventListener('click', function(e) {
                const notificationItem = e.target.closest('.notification-item');
                if (notificationItem && !e.target.closest('.notification-dismiss')) {
                    const modalId = notificationItem.getAttribute('data-bs-target');
                    const modal = new bootstrap.Modal(document.querySelector(modalId));
                    modal.show();
                }
            });
        });

        // Client-side dismiss notification
        function dismissNotification(event, notificationId) {
            event.stopPropagation();
            const notificationItem = document.querySelector(`.notification-item[data-notification-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.style.opacity = '0';
                setTimeout(() => {
                    notificationItem.remove();
                    if (!document.getElementById('notificationList').children.length) {
                        document.getElementById('noNotificationsMessage').classList.remove('d-none');
                    }
                }, 300);
            }
        }

        // Update Clock, Date, and Greeting
        let currentDate = new Date();

        function updateClockAndGreeting() {
            const now = new Date();
            const hours = now.getHours();
            const timeString = now.toLocaleTimeString();
            const dateString = now.toLocaleDateString();

            let greeting = "Hello";

            if (hours >= 5 && hours < 12) {
                greeting = "Good Morning";
            } else if (hours >= 12 && hours < 18) {
                greeting = "Good Afternoon";
            } else {
                greeting = "Good Evening";
            }

            document.getElementById('greeting').textContent = `${greeting}, Admin!`;
            document.getElementById('liveClock').textContent = `🕒 ${dateString} ${timeString}`;
        }

        // Calendar Functions
        function generateCalendar() {
            const month = currentDate.getMonth();
            const year = currentDate.getFullYear();

            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const firstDayOfMonth = new Date(year, month, 1).getDay();
            const calendarContainer = document.getElementById('calendarDays');
            const calendarMonthLabel = document.getElementById('calendarMonth');

            calendarMonthLabel.textContent = `${currentDate.toLocaleString('default', { month: 'long' })} ${year}`;
            calendarContainer.innerHTML = '';

            let row = '<tr>';
            for (let i = 0; i < firstDayOfMonth; i++) {
                row += `<td></td>`;
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const today = new Date();
                const isToday = today.getDate() === day && today.getMonth() === month && today.getFullYear() === year;

                row += `
                    <td class="${isToday ? 'bg-primary text-white rounded' : ''}">
                        ${day}
                    </td>`;

                if ((firstDayOfMonth + day) % 7 === 0) {
                    row += '</tr>';
                    calendarContainer.innerHTML += row;
                    row = '<tr>';
                }
            }

            if (row !== '<tr>') {
                const lastRowCells = (firstDayOfMonth + daysInMonth) % 7;
                if (lastRowCells > 0) {
                    for (let i = 0; i < 7 - lastRowCells; i++) {
                        row += '<td></td>';
                    }
                }
                row += '</tr>';
                calendarContainer.innerHTML += row;
            }
        }

        document.getElementById('prevMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            generateCalendar();
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            generateCalendar();
        });

        // Treatment Chart
        let chartType = 'bar';
        const ctx = document.getElementById('treatmentChart').getContext('2d');
        const chartData = {
            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            datasets: [
                {
                    label: 'Done',
                    backgroundColor: '#2A9D8F',
                    borderColor: '#2A9D8F',
                    data: <?php echo json_encode(array_values($done_data)); ?>,
                    fill: false,
                },
                {
                    label: 'Undone',
                    backgroundColor: '#E9C46A',
                    borderColor: '#E9C46A',
                    data: <?php echo json_encode(array_values($undone_data)); ?>,
                    fill: false,
                }
            ]
        };

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        font: {
                            size: function() {
                                return window.innerWidth < 768 ? 10 : 12;
                            }
                        }
                    }
                },
                title: { 
                    display: true, 
                    text: 'Monthly Treatment Statistics',
                    font: {
                        size: function() {
                            return window.innerWidth < 768 ? 14 : 16;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        font: {
                            size: function() {
                                return window.innerWidth < 768 ? 8 : 12;
                            }
                        }
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: function() {
                                return window.innerWidth < 768 ? 8 : 12;
                            }
                        }
                    }
                }
            }
        };

        let treatmentChart = new Chart(ctx, {
            type: chartType,
            data: chartData,
            options: chartOptions
        });

        function changeChartType(type) {
            chartType = type;
            treatmentChart.destroy();
            treatmentChart = new Chart(ctx, {
                type: type,
                data: chartData,
                options: chartOptions
            });
        }

        // Update treatment chart when year changes
        document.getElementById('yearInput').addEventListener('change', async function() {
            const year = this.value;
            if (year < 2000 || year > 2100) {
                alert('Please enter a year between 2000 and 2100.');
                this.value = <?php echo date('Y'); ?>;
                return;
            }

            try {
                const response = await fetch('fetch_treatment_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ year })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    chartData.datasets[0].data = result.done_data;
                    chartData.datasets[1].data = result.undone_data;
                    treatmentChart.destroy();
                    treatmentChart = new Chart(ctx, {
                        type: chartType,
                        data: chartData,
                        options: chartOptions
                    });
                } else {
                    alert(result.message || 'Error fetching treatment data.');
                }
            } catch (error) {
                alert('Network error: ' + error.message);
            }
        });

        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderChart = new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: ['Male', 'Female', 'Others'],
                datasets: [{
                    data: [
                        <?php echo $gender_counts['Male']; ?>,
                        <?php echo $gender_counts['Female']; ?>,
                        <?php echo $gender_counts['Other']; ?>
                    ],
                    backgroundColor: ['#2A9D8F', '#E76F51', '#A3D5D1']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: function() {
                            return window.innerWidth < 768 ? 'bottom' : 'top';
                        },
                        labels: {
                            boxWidth: function() {
                                return window.innerWidth < 768 ? 10 : 12;
                            },
                            font: {
                                size: function() {
                                    return window.innerWidth < 768 ? 10 : 12;
                                }
                            }
                        }
                    },
                    title: { 
                        display: true, 
                        text: 'Patient Gender Breakdown',
                        font: {
                            size: function() {
                                return window.innerWidth < 768 ? 14 : 16;
                            }
                        }
                    }
                }
            }
        });

        // Handle window resize for charts
        window.addEventListener('resize', function() {
            treatmentChart.resize();
            genderChart.resize();
        });

        // Notification Handling
        async function toggleDeclineReason(appointmentId) {
            const declineReasonGroup = document.getElementById(`declineReasonGroup${appointmentId}`);
            declineReasonGroup.style.display = declineReasonGroup.style.display === 'none' ? 'block' : 'none';
            if (declineReasonGroup.style.display === 'block') {
                const declineButton = document.querySelector(`#appointmentModal${appointmentId} .btn-danger`);
                declineButton.textContent = 'Submit Decline';
                declineButton.onclick = () => handleDecision(appointmentId, 'declined');
            } else {
                const declineButton = document.querySelector(`#appointmentModal${appointmentId} .btn-danger`);
                declineButton.textContent = 'Decline';
                declineButton.onclick = () => toggleDeclineReason(appointmentId);
            }
        }

        async function handleDecision(appointmentId, decision) {
            const declineReason = decision === 'declined' ? document.getElementById(`declineReason${appointmentId}`).value : null;
            if (decision === 'declined' && !declineReason) {
                alert('Please provide a reason for declining.');
                return;
            }

            try {
                const response = await fetch('handle_decision.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ appointment_id: appointmentId, decision, decline_reason: declineReason })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    window.location.reload();
                } else {
                    alert(result.message || 'Error processing decision.');
                }
            } catch (error) {
                alert('Network error: ' + error.message);
            }
        }

        // Initialize everything
        generateCalendar();
        setInterval(updateClockAndGreeting, 1000);
        updateClockAndGreeting();
    </script>
</body>
</html>
<?php $conn = null; ?>