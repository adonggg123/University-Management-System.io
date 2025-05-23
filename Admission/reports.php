<?php
// Include database connection
include('connect.php');

// Handle Export to Excel for Admissions
if (isset($_GET['export_excel']) && ($_GET['tab'] ?? '') === 'report') {
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $status = $_GET['status'] ?? '';
    $course = $_GET['course'] ?? '';

    // Building query with prepared statement
    $sql = "SELECT id, full_name, gender, email, contact, address, birthdate, course_choice_1, course_choice_2, status, date_applied FROM student_applications WHERE 1=1";
    $params = [];
    $types = '';

    if ($start_date) {
        $sql .= " AND date_applied >= ?";
        $params[] = $start_date;
        $types .= 's';
    }

    if ($end_date) {
        $sql .= " AND date_applied <= ?";
        $params[] = $end_date;
        $types .= 's';
    }

    if ($status) {
        $sql .= " AND status = ?";
        $params[] = $status;
        $types .= 's';
    }

    if ($course) {
        $sql .= " AND course_choice_1 = ?";
        $params[] = $course;
        $types .= 's';
    }

    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare Excel export
    $filename = "filtered_admissions.xlsx";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Full Name', 'Gender', 'Email', 'Phone', 'Address', 'Birthdate', 'Course 1', 'Course 2', 'Status', 'Date Applied']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['full_name'],
            $row['gender'],
            $row['email'],
            $row['contact'],
            $row['address'],
            $row['birthdate'],
            $row['course_choice_1'],
            $row['course_choice_2'],
            $row['status'],
            $row['date_applied']
        ]);
    }

    fclose($output);
    $stmt->close();
    exit();
}

// Handle Export to Excel for Scholarships
if (isset($_GET['export_excel']) && ($_GET['tab'] ?? '') === 'scholarship') {
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $status = $_GET['status'] ?? '';
    $scholarship = $_GET['scholarship'] ?? '';

    // Building query with prepared statement
    $sql = "SELECT sa.id, sa.student_id, sa.fullname, sa.email, sa.course, sa.year_level, sa.family_income, s.title AS scholarship_title, sa.status, sa.applied_at 
            FROM scholarship_applications sa 
            JOIN scholarships s ON sa.scholarship_id = s.id 
            WHERE 1=1";
    $params = [];
    $types = '';

    if ($start_date) {
        $sql .= " AND sa.applied_at >= ?";
        $params[] = $start_date;
        $types .= 's';
    }

    if ($end_date) {
        $sql .= " AND sa.applied_at <= ?";
        $params[] = $end_date;
        $types .= 's';
    }

    if ($status) {
        $sql .= " AND sa.status = ?";
        $params[] = $status;
        $types .= 's';
    }

    if ($scholarship) {
        $sql .= " AND s.title = ?";
        $params[] = $scholarship;
        $types .= 's';
    }

    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare Excel export
    $filename = "filtered_scholarships.xlsx";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Student ID', 'Full Name', 'Email', 'Course', 'Year Level', 'Family Income', 'Scholarship Program', 'Status', 'Applied At']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['student_id'],
            $row['fullname'],
            $row['email'],
            $row['course'],
            $row['year_level'],
            $row['family_income'],
            $row['scholarship_title'],
            $row['status'],
            $row['applied_at']
        ]);
    }

    fclose($output);
    $stmt->close();
    exit();
}

// Chart 1: Total Applications
$admissions_result = $conn->query("SELECT COUNT(*) AS total FROM student_applications");
if (!$admissions_result) {
    die("Error in query: " . $conn->error);
}
$admissions_total = $admissions_result->fetch_assoc()['total'] ?? 0;
error_log("Admissions total: $admissions_total");

$scholarships_result = $conn->query("SELECT COUNT(*) AS total FROM scholarship_applications");
if (!$scholarships_result) {
    die("Error in query: " . $conn->error);
}
$scholarships_total = $scholarships_result->fetch_assoc()['total'] ?? 0;
error_log("Scholarships total: $scholarships_total");

// Chart 2: Status Breakdown
$adm_status = ['Pending' => 0, 'Approved' => 0, 'Declined' => 0];
foreach ($adm_status as $status => &$count) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM student_applications WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['total'] ?? 0;
    $stmt->close();
}
error_log("Admissions status: " . json_encode($adm_status));

$sch_status = ['Pending' => 0, 'Approved' => 0, 'Declined' => 0];
foreach ($sch_status as $status => &$count) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM scholarship_applications WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['total'] ?? 0;
    $stmt->close();
}
error_log("Scholarships status: " . json_encode($sch_status));

// Chart 3: Applications by Course and Scholarship
$course_counts = [];
$result = $conn->query("SELECT course_choice_1, COUNT(*) AS total FROM student_applications GROUP BY course_choice_1");
if (!$result) {
    die("Error in query: " . $conn->error);
}
while ($row = $result->fetch_assoc()) {
    $course = $row['course_choice_1'] ?: 'Unknown';
    $course_counts[$course] = $row['total'];
}
error_log("Course counts: " . json_encode($course_counts));

$scholarship_counts = [];
$result = $conn->query("SELECT scholarships.title, COUNT(*) AS total 
                       FROM scholarship_applications 
                       JOIN scholarships ON scholarship_applications.scholarship_id = scholarships.id 
                       GROUP BY scholarships.title");
if (!$result) {
    die("Error in query: " . $conn->error);
}
while ($row = $result->fetch_assoc()) {
    $scholarship = $row['title'] ?: 'Unknown';
    $scholarship_counts[$scholarship] = $row['total'];
}
error_log("Scholarship counts: " . json_encode($scholarship_counts));

$labels = array_merge(array_keys($course_counts ?: []), array_keys($scholarship_counts ?: []));
$totals = array_merge(array_values($course_counts ?: []), array_values($scholarship_counts ?: []));
$labels_json = json_encode($labels);
$totals_json = json_encode($totals);
error_log("Labels JSON: $labels_json");
error_log("Totals JSON: $totals_json");

// Chart 4: Weekly Applicants
$weeks = [];
$adm_weekly = [];
$sch_weekly = [];

// Get the range of weeks (e.g., last 12 weeks)
$week_query = $conn->query("SELECT DISTINCT DATE_FORMAT(applied_date, '%Y-%U') AS week 
                            FROM (
                                SELECT date_applied AS applied_date FROM student_applications
                                UNION
                                SELECT applied_at AS applied_date FROM scholarship_applications
                            ) AS combined
                            ORDER BY week DESC LIMIT 12");
if (!$week_query) {
    die("Error in query: " . $conn->error);
}
while ($row = $week_query->fetch_assoc()) {
    $weeks[] = $row['week'];
}
error_log("Weeks: " . json_encode($weeks));

// Query admissions and scholarships per week
foreach ($weeks as $week) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM student_applications WHERE DATE_FORMAT(date_applied, '%Y-%U') = ?");
    $stmt->bind_param("s", $week);
    $stmt->execute();
    $result = $stmt->get_result();
    $adm_weekly[] = $result->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM scholarship_applications WHERE DATE_FORMAT(applied_at, '%Y-%U') = ?");
    $stmt->bind_param("s", $week);
    $stmt->execute();
    $result = $stmt->get_result();
    $sch_weekly[] = $result->fetch_assoc()['total'] ?? 0;
    $stmt->close();
}

$weeks_json = json_encode(array_reverse($weeks ?: ['No Data']));
$adm_weekly_json = json_encode(array_reverse($adm_weekly ?: [0]));
$sch_weekly_json = json_encode(array_reverse($sch_weekly ?: [0]));
error_log("Weeks JSON: $weeks_json");
error_log("Adm Weekly JSON: $adm_weekly_json");
error_log("Sch Weekly JSON: $sch_weekly_json");

// Filtered Report Queries for Admissions
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$status = $_GET['status'] ?? '';
$course = $_GET['course'] ?? '';

$sql = "SELECT id, full_name, gender, email, contact, address, birthdate, course_choice_1, course_choice_2, status, date_applied FROM student_applications WHERE 1=1";
$params = [];
$types = '';

if ($start_date) {
    $sql .= " AND date_applied >= ?";
    $params[] = $start_date;
    $types .= 's';
}

if ($end_date) {
    $sql .= " AND date_applied <= ?";
    $params[] = $end_date;
    $types .= 's';
}

if ($status) {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($course) {
    $sql .= " AND course_choice_1 = ?";
    $params[] = $course;
    $types .= 's';
}

$sql .= " ORDER BY full_name ASC";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$filtered_result = $stmt->get_result();
$stmt->close();

$courses_result = $conn->query("SELECT DISTINCT course_choice_1 FROM student_applications");
if (!$courses_result) {
    die("Error in query: " . $conn->error);
}

// Filtered Report Queries for Scholarships
$sch_start_date = $_GET['start_date'] ?? '';
$sch_end_date = $_GET['end_date'] ?? '';
$sch_status = $_GET['status'] ?? '';
$sch_scholarship = $_GET['scholarship'] ?? '';

$sch_sql = "SELECT sa.id, sa.student_id, sa.fullname, sa.email, sa.course, sa.year_level, sa.family_income, s.title AS scholarship_title, sa.status, sa.applied_at 
            FROM scholarship_applications sa 
            JOIN scholarships s ON sa.scholarship_id = s.id 
            WHERE 1=1";
$sch_params = [];
$sch_types = '';

if ($sch_start_date) {
    $sch_sql .= " AND sa.applied_at >= ?";
    $sch_params[] = $sch_start_date;
    $sch_types .= 's';
}

if ($sch_end_date) {
    $sch_sql .= " AND sa.applied_at <= ?";
    $sch_params[] = $sch_end_date;
    $sch_types .= 's';
}

if ($sch_status) {
    $sch_sql .= " AND sa.status = ?";
    $sch_params[] = $sch_status;
    $sch_types .= 's';
}

if ($sch_scholarship) {
    $sch_sql .= " AND s.title = ?";
    $sch_params[] = $sch_scholarship;
    $sch_types .= 's';
}

$sch_sql .= " ORDER BY sa.fullname ASC";
$sch_stmt = $conn->prepare($sch_sql);
if ($sch_params) {
    $sch_stmt->bind_param($sch_types, ...$sch_params);
}
$sch_stmt->execute();
$sch_filtered_result = $sch_stmt->get_result();
$sch_stmt->close();

$sch_scholarships_result = $conn->query("SELECT DISTINCT title FROM scholarships");
if (!$sch_scholarships_result) {
    die("Error in query: " . $conn->error);
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admissions Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --primary-color: rgba(22, 2, 67, 1);
            --secondary-color: #f8f9fa;
            --warning-color: #ffc107;
            --text-color: #333333;
            --border-color: #e0e0e0;
        }

        body {
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--secondary-color);
            color: var(--text-color);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            flex-grow: 1;
            padding: 30px;
        }

        .header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 1.75rem;
        }

        .nav-tabs {
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            color: var(--text-color);
            font-size: 14px;
            padding: 10px 20px;
            border: none;
            border-radius: 8px 8px 0 0;
            transition: background-color 0.3s;
        }

        .nav-tabs .nav-link:hover {
            background-color: var(--secondary-color);
        }

        .nav-tabs .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .tab-content {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 0 8px 8px 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .chart-container {
            background: #ffffff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            height: 350px;
            position: relative;
        }

        .chart-container canvas {
            display: block;
            width: 100% !important;
            height: 250px !important;
        }

        .chart-error {
            display: none;
            color: red;
            text-align: center;
            font-size: 14px;
            margin-top: 10px;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
        }

        .table {
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .table th {
            background: var(--primary-color);
            color: white;
            padding: 12px;
            font-weight: 600;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-primary:hover {
            background-color: rgba(22, 2, 67, 0.9);
        }

        .btn-success {
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            font-size: 14px;
        }

        .icon {
            margin-right: 6px;
        }

        .back-button {
            margin-bottom: 20px;
        }

        .sidebar {
            min-width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding-top: 20px;
            transition: transform 0.3s ease;
        }

        .sidebar a {
            color: white;
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            font-size: 1rem;
            transition: background-color 0.3s ease, padding-left 0.3s ease;
        }

        .sidebar a i {
            margin-right: 10px;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            padding-left: 25px;
        }

        .navbar-brand {
            margin-top: -12px;
            display: flex;
            align-items: center;
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
            padding: 12px 20px;
        }

        .navbar-brand img {
            max-height: 40px;
            margin-right: 10px;
            transform: scale(1.2);
        }

        .marg {
            margin-top: 28px;
        }

        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .nav-tabs .nav-link {
                font-size: 13px;
                padding: 8px 16px;
            }

            .chart-container {
                padding: 15px;
                height: 300px;
            }

            .chart-container canvas {
                height: 200px !important;
            }

            .section-title {
                font-size: 1rem;
            }

            .table {
                display: block;
                overflow-x: auto;
            }

            .table th,
            .table td {
                font-size: 12px;
                padding: 8px;
            }

            .form-control,
            .form-select {
                font-size: 13px;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }

            .sidebar {
                min-width: 200px;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" role="navigation" aria-label="Admin navigation">
        <div class="navbar-brand">
            <img src="Adm.png" alt="Logo" class="logo">
            <span>Admission & <br>Scholarhips Office</span>
        </div>
        <a href="admin.php" class="marg"><i class="fas fa-home"></i> Dashboard</a>
        <a href="manage_admissions.php"><i class="fas fa-user-graduate"></i> Manage Admissions</a>
        <a href="sch_admin.php"><i class="fas fa-book"></i> Manage Scholarship</a>
        <a href="reports.php" class="active"><i class="fas fa-chart-line"></i> Generate Reports</a>
        <a href="#"><i class="bi bi-box-arrow-in-right"></i> Log out</a>
    </div>

    <div class="container">
        <div class="header">
            <h1>Admissions and Scholarship Reports</h1>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="charts-tab" data-bs-toggle="tab" data-bs-target="#charts" type="button" role="tab" aria-controls="charts" aria-selected="true">
                    <i class="fas fa-chart-bar icon"></i>Application Charts
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="report-tab" data-bs-toggle="tab" data-bs-target="#report" type="button" role="tab" aria-controls="report" aria-selected="false">
                    <i class="fas fa-table icon"></i>Admissions Report
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="scholarship-tab" data-bs-toggle="tab" data-bs-target="#scholarship" type="button" role="tab" aria-controls="scholarship" aria-selected="false">
                    <i class="fas fa-table icon"></i>Scholarship Report
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="reportTabContent">
            <!-- Tab 1: Application Charts -->
            <div class="tab-pane fade show active" id="charts" role="tabpanel" aria-labelledby="charts-tab">
                <div class="row">
                    <!-- Chart 1: Total Applications -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="applicationsChart" width="400" height="250"></canvas>
                            <div class="chart-error" id="applicationsChartError">Failed to load chart</div>
                        </div>
                    </div>

                    <!-- Chart 2: Status Breakdown -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="statusChart" width="400" height="250"></canvas>
                            <div class="chart-error" id="statusChartError">Failed to load chart</div>
                        </div>
                    </div>

                    <!-- Chart 3: Applications by Course and Scholarship -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="programChart" width="400" height="250"></canvas>
                            <div class="chart-error" id="programChartError">Failed to load chart</div>
                        </div>
                    </div>

                    <!-- Chart 4: Weekly Applicants -->
                    <div class="col-md-6">
                        <div class="chart-container">
                            <canvas id="weeklyChart" width="400" height="250"></canvas>
                            <div class="chart-error" id="weeklyChartError">Failed to load chart</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Admissions Report -->
            <div class="tab-pane fade" id="report" role="tabpanel" aria-labelledby="report-tab">
                <h2 class="section-title">Filter Admissions</h2>

                <!-- Filter Form -->
                <form method="GET" action="reports.php">
                    <input type="hidden" name="tab" value="report">
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" id="start_date" value="<?= htmlspecialchars($start_date) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" id="end_date" value="<?= htmlspecialchars($end_date) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select" id="status">
                                <option value="">All</option>
                                <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Approved" <?= $status === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Declined" <?= $status === 'Declined' ? 'selected' : '' ?>>Declined</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="course" class="form-label">Course Program</label>
                            <select name="course" class="form-select" id="course">
                                <option value="">All</option>
                                <?php while ($row = $courses_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row['course_choice_1']) ?>" <?= $course === $row['course_choice_1'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['course_choice_1']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter icon"></i>Filter</button>
                </form>

                <!-- Export Button -->
                <?php if ($start_date || $end_date || $status || $course): ?>
                    <a href="reports.php?export_excel=true&tab=report&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&status=<?= urlencode($status) ?>&course=<?= urlencode($course) ?>" class="btn btn-success mt-3">
                        <i class="fas fa-file-excel icon"></i>Export to Excel
                    </a>
                <?php endif; ?>

                <!-- Filtered Data Table -->
                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Gender</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Birthdate</th>
                            <th>Course 1</th>
                            <th>Course 2</th>
                            <th>Status</th>
                            <th>Date Applied</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $filtered_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['gender']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['contact']) ?></td>
                                <td><?= htmlspecialchars($row['address']) ?></td>
                                <td><?= htmlspecialchars($row['birthdate']) ?></td>
                                <td><?= htmlspecialchars($row['course_choice_1']) ?></td>
                                <td><?= htmlspecialchars($row['course_choice_2']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                                <td><?= htmlspecialchars($row['date_applied']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tab 3: Scholarship Report -->
            <div class="tab-pane fade" id="scholarship" role="tabpanel" aria-labelledby="scholarship-tab">
                <h2 class="section-title">Filter Scholarships</h2>

                <!-- Filter Form -->
                <form method="GET" action="reports.php">
                    <input type="hidden" name="tab" value="scholarship">
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <label for="sch_start_date" class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" id="sch_start_date" value="<?= htmlspecialchars($sch_start_date) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="sch_end_date" class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" id="sch_end_date" value="<?= htmlspecialchars($sch_end_date) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="sch_status" class="form-label">Status</label>
                            <select name="status" class="form-select" id="sch_status">
                                <option value="">All</option>
                                <option value="Pending" <?= $sch_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Approved" <?= $sch_status === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Declined" <?= $sch_status === 'Declined' ? 'selected' : '' ?>>Declined</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="sch_scholarship" class="form-label">Scholarship Program</label>
                            <select name="scholarship" class="form-select" id="sch_scholarship">
                                <option value="">All</option>
                                <?php while ($row = $sch_scholarships_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($row['title']) ?>" <?= $sch_scholarship === $row['title'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($row['title']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter icon"></i>Filter</button>
                </form>

                <!-- Export Button -->
                <?php if ($sch_start_date || $sch_end_date || $sch_status || $sch_scholarship): ?>
                    <a href="reports.php?export_excel=true&tab=scholarship&start_date=<?= urlencode($sch_start_date) ?>&end_date=<?= urlencode($sch_end_date) ?>&status=<?= urlencode($sch_status) ?>&scholarship=<?= urlencode($sch_scholarship) ?>" class="btn btn-success mt-3">
                        <i class="fas fa-file-excel icon"></i>Export to Excel
                    </a>
                <?php endif; ?>

                <!-- Filtered Data Table -->
                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Year Level</th>
                            <th>Family Income</th>
                            <th>Scholarship Program</th>
                            <th>Status</th>
                            <th>Applied At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $sch_filtered_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['student_id']) ?></td>
                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['course']) ?></td>
                                <td><?= htmlspecialchars($row['year_level']) ?></td>
                                <td><?= htmlspecialchars(number_format($row['family_income'], 2)) ?></td>
                                <td><?= htmlspecialchars($row['scholarship_title']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                                <td><?= htmlspecialchars($row['applied_at']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Wait for both DOM and Chart.js to be fully loaded
        window.addEventListener('load', () => {
            console.log('Window loaded, initializing charts...');
            setTimeout(initializeCharts, 100); // Small delay to ensure Chart is available
        });

        function initializeCharts() {
            // Check if Chart is loaded
            if (typeof Chart === 'undefined') {
                console.error('Chart.js not loaded!');
                document.querySelectorAll('.chart-error').forEach(el => el.style.display = 'block');
                return;
            }

            // Chart 1: Total Applications
            try {
                const applicationsCanvas = document.getElementById('applicationsChart');
                if (!applicationsCanvas) throw new Error('Canvas not found');

                const admissionsTotal = <?= intval($admissions_total) ?>;
                const scholarshipsTotal = <?= intval($scholarships_total) ?>;

                console.log('Applications data:', {
                    admissions: admissionsTotal,
                    scholarships: scholarshipsTotal
                });

                new Chart(applicationsCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Admissions', 'Scholarships'],
                        datasets: [{
                            label: 'Total Applications',
                            data: [admissionsTotal, scholarshipsTotal],
                            backgroundColor: ['#4e73df', '#1cc88a'],
                            borderColor: ['#2e59d9', '#17a673'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'Total Applications by Type',
                                font: {
                                    size: 16
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
                console.log('Total Applications chart initialized');
            } catch (e) {
                console.error('Error initializing Total Applications chart:', e);
                document.getElementById('applicationsChartError').style.display = 'block';
            }

            // Chart 2: Status Breakdown
            try {
                const statusCanvas = document.getElementById('statusChart');
                if (!statusCanvas) throw new Error('Canvas not found');

                const admPending = <?= intval($adm_status['Pending'] ?? 0) ?>;
                const admApproved = <?= intval($adm_status['Approved'] ?? 0) ?>;
                const admDeclined = <?= intval($adm_status['Declined'] ?? 0) ?>;

                const schPending = <?= intval($sch_status['Pending'] ?? 0) ?>;
                const schApproved = <?= intval($sch_status['Approved'] ?? 0) ?>;
                const schDeclined = <?= intval($sch_status['Declined'] ?? 0) ?>;

                console.log('Status data:', {
                    admissions: [admPending, admApproved, admDeclined],
                    scholarships: [schPending, schApproved, schDeclined]
                });

                new Chart(statusCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['Pending', 'Approved', 'Declined'],
                        datasets: [{
                                label: 'Admissions',
                                data: [admPending, admApproved, admDeclined],
                                backgroundColor: '#4e73df'
                            },
                            {
                                label: 'Scholarships',
                                data: [schPending, schApproved, schDeclined],
                                backgroundColor: '#1cc88a'
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'Application Status Breakdown',
                                font: {
                                    size: 16
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
                console.log('Status Breakdown chart initialized');
            } catch (e) {
                console.error('Error initializing Status Breakdown chart:', e);
                document.getElementById('statusChartError').style.display = 'block';
            }

            // Chart 3: Applications by Course and Scholarship
            try {
                const programCanvas = document.getElementById('programChart');
                if (!programCanvas) throw new Error('Canvas not found');

                // Safely parse JSON with fallback values
                let labels, totals;
                try {
                    labels = JSON.parse('<?= str_replace("'", "\\'", $labels_json) ?>');
                    totals = JSON.parse('<?= str_replace("'", "\\'", $totals_json) ?>');
                } catch (e) {
                    console.error('Failed to parse program chart JSON:', e);
                    labels = ['No Data'];
                    totals = [0];
                }

                console.log('Program data:', {
                    labels,
                    totals
                });

                new Chart(programCanvas, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total Applications',
                            data: totals,
                            backgroundColor: '#f6c23e'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'Applications by Course and Scholarship',
                                font: {
                                    size: 16
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
                console.log('Applications by Course and Scholarship chart initialized');
            } catch (e) {
                console.error('Error initializing Applications by Course and Scholarship chart:', e);
                document.getElementById('programChartError').style.display = 'block';
            }

            // Chart 4: Weekly Applicants
            try {
                const weeklyCanvas = document.getElementById('weeklyChart');
                if (!weeklyCanvas) throw new Error('Canvas not found');

                // Safely parse JSON with fallback values
                let weeks, admWeekly, schWeekly;
                try {
                    weeks = JSON.parse('<?= str_replace("'", "\\'", $weeks_json) ?>');
                    admWeekly = JSON.parse('<?= str_replace("'", "\\'", $adm_weekly_json) ?>');
                    schWeekly = JSON.parse('<?= str_replace("'", "\\'", $sch_weekly_json) ?>');
                } catch (e) {
                    console.error('Failed to parse weekly chart JSON:', e);
                    weeks = ['No Data'];
                    admWeekly = [0];
                    schWeekly = [0];
                }

                console.log('Weekly data:', {
                    weeks,
                    admissions: admWeekly,
                    scholarships: schWeekly
                });

                new Chart(weeklyCanvas, {
                    type: 'line',
                    data: {
                        labels: weeks,
                        datasets: [{
                                label: 'Admissions',
                                data: admWeekly,
                                borderColor: '#4e73df',
                                backgroundColor: 'rgba(78, 115, 223, 0.2)',
                                fill: true,
                                tension: 0.4
                            },
                            {
                                label: 'Scholarships',
                                data: schWeekly,
                                borderColor: '#1cc88a',
                                backgroundColor: 'rgba(28, 200, 138, 0.2)',
                                fill: true,
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'Applicants per Week',
                                font: {
                                    size: 16
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Week (YYYY-WW)'
                                }
                            }
                        }
                    }
                });
                console.log('Weekly Applicants chart initialized');
            } catch (e) {
                console.error('Error initializing Weekly Applicants chart:', e);
                document.getElementById('weeklyChartError').style.display = 'block';
            }

            // Activate the correct tab based on URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            if (tab === 'report') {
                new bootstrap.Tab(document.querySelector('#report-tab')).show();
            } else if (tab === 'scholarship') {
                new bootstrap.Tab(document.querySelector('#scholarship-tab')).show();
            }
        }
    </script>
</body>

</html>