<?php
$host = "localhost";
$user = "root";
$pass = "quest4inno@server";
$dbname = "university_management_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM student_applications";
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$totalApplicants = 0;
$admissionApplicants = 0;
$scholarshipApplicants = 0;
$pendingApplications = 0;
$applications = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Exclude applicants who have passed the exam
        if (strtolower($row['exam_status']) === 'passed') {
            continue;
        }

        $applications[] = $row;
        $totalApplicants++;

        $status = strtolower($row['status']);
        if ($status === 'pending') {
            $pendingApplications++;
            $admissionApplicants++;
        } else if ($status === 'approved') {
            $admissionApplicants++;
        } elseif ($status === 'admission') {
            $admissionApplicants++;
        } elseif ($status === 'scholarship') {
            $scholarshipApplicants++;
        }
    }
}

$sql = "SELECT * FROM scholarship_applications";
$queryResult = $conn->query($sql);

if (!$queryResult) {
    die("Query failed: " . $conn->error);
}

$totalApplicationsCount = 0;
$scholarshipCount = 0;
$pendingCount = 0;
$filteredApplications = [];

if ($queryResult->num_rows > 0) {
    while ($application = $queryResult->fetch_assoc()) {
        // Skip students who already passed the exam
        if (strtolower($application['status']) === 'approved' || ($application['status']) === 'Declined') {
            continue;
        }

        $filteredApplications[] = $application;
        $totalApplicants++;

        $applicationStatus = strtolower($application['status']);
        if ($applicationStatus === 'pending') {
            $pendingApplications++;
            $scholarshipApplicants++;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Admission System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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

        .sidebar {
            min-width: 250px;
            background-color: var(--primary-color);
            color: white;
            padding-top: 20px;
            transition: transform 0.3s ease;
        }

        .sidebar h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
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

        .marg {
            margin-top: 40px;
        }

        .main-content {
            flex-grow: 1;
            padding: 30px;
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .input-group {
            max-width: 100%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .input-group:hover,
        .input-group:focus-within {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .input-group-text {
            background-color: white;
            border: none;
            padding: 10px 12px;
        }

        .form-control {
            border: none;
            padding: 10px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }

        .card-box {
            margin-bottom: 20px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeInUp 0.5s ease-in;
        }

        .card-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card .card-body {
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .card-text {
            font-size: 2.5rem;
            font-weight: 700;
        }

        .nav-tabs {
            border-bottom: 2px solid var(--border-color);
            margin-top: 20px;
        }

        .nav-tabs .nav-link {
            color: var(--primary-color);
            font-size: 1rem;
            font-weight: 500;
            padding: 10px 20px;
            border: none;
            border-radius: 8px 8px 0 0;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            background-color: var(--border-color);
            color: var(--primary-color);
        }

        .nav-tabs .nav-link.active {
            background-color: var(--primary-color);
            color: white;
        }

        .tab-content {
            margin-top: 20px;
            animation: fadeIn 0.5s ease-in;
        }

        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .table {
            margin-bottom: 0;
            background-color: white;
        }

        .table thead {
            position: sticky;
            top: 0;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            z-index: 1;
        }

        .table th {
            font-weight: 600;
            font-size: 0.95rem;
            padding: 12px;
            color: var(--primary-color);
        }

        .table td {
            font-size: 0.9rem;
            padding: 12px;
            vertical-align: middle;
        }

        .table tr:hover {
            background-color: var(--secondary-color);
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            min-width: 80px;
        }

        .status-pending {
            background-color: #ffc107;
            color: #333;
        }

        .status-approved {
            background-color: #28a745;
            color: white;
        }

        .status-admission {
            background-color: #007bff;
            color: white;
        }

        .btn-info {
            background-color: var(--primary-color);
            border: none;
            color: white;
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 6px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-info:hover {
            background-color: rgba(22, 2, 67, 0.9);
            transform: translateY(-2px);
        }

        .calendar-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        #calendar {
            font-size: 0.9rem;
        }

        .fc-event {
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .fc-event:hover {
            background-color: rgba(22, 2, 67, 0.8) !important;
        }

        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-width: 200px;
            }

            .main-content {
                padding: 20px;
            }

            .input-group {
                max-width: 100%;
            }

            .card-box {
                margin-bottom: 15px;
            }

            .card-title {
                font-size: 1rem;
            }

            .card-text {
                font-size: 2rem;
            }

            .nav-tabs .nav-link {
                font-size: 0.9rem;
                padding: 8px 12px;
            }

            .table th,
            .table td {
                font-size: 0.85rem;
                padding: 8px;
            }

            .status-badge {
                min-width: 70px;
                font-size: 0.8rem;
            }
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .navbar-brand img {
            max-height: 40px;
            margin-right: 10px;
            margin-left: 20px;
            transform: scale(1.2);
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
        <a href="admin.php" aria-current="page" class="marg active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="manage_admissions.php"><i class="fas fa-user-graduate"></i> Manage Admissions</a>
        <a href="sch_admin.php"><i class="fas fa-book"></i> Manage Scholarship</a>
        <a href="reports.php"><i class="fas fa-chart-line"></i> Generate Reports</a>
        <a href="../UMS.php"><i class="bi bi-box-arrow-in-right"></i> Log out</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="mb-4">
            <div class="input-group" role="search">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted" aria-hidden="true"></i>
                </span>
                <input type="text" class="form-control border-start-0" placeholder="Search applicants..." id="searchInput" aria-label="Search applicants">
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="card text-bg-warning card-box">
                    <div class="card-body">
                        <h5 class="card-title">Total Applicants</h5>
                        <p class="card-text display-6"><?= $totalApplicants ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-warning card-box">
                    <div class="card-body">
                        <h5 class="card-title">Admission Applicants</h5>
                        <p class="card-text display-6"><?= $admissionApplicants ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-warning card-box">
                    <div class="card-body">
                        <h5 class="card-title">Scholarship Applicants</h5>
                        <p class="card-text display-6"><?= $scholarshipApplicants ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-bg-warning card-box">
                    <div class="card-body">
                        <h5 class="card-title">Pending Applications</h5>
                        <p class="card-text display-6"><?= $pendingApplications ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mt-4" id="applicantTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="admission-tab" data-bs-toggle="tab" data-bs-target="#admission" type="button" role="tab" aria-controls="admission" aria-selected="true">Admission Applicants</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="scholarship-tab" data-bs-toggle="tab" data-bs-target="#scholarship" type="button" role="tab" aria-controls="scholarship" aria-selected="false">Scholarship Applicants</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-page" type="button" role="tab" aria-controls="calendar-page" aria-selected="false">Calendar View</button>
            </li>
        </ul>

        <div class="tab-content mt-3" id="applicantTabsContent">
            <!-- Admission Applicants -->
            <div class="tab-pane fade show active" id="admission" role="tabpanel" aria-labelledby="admission-tab">
                <h5>Admission Applicants</h5>
                <div class="table-responsive">
                    <table class="table table-striped" aria-describedby="admission-applicants-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Full Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Status</th>
                                <th scope="col">Date Applied</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <?php if (strtolower($app['status']) === 'admission' || strtolower($app['status']) === 'pending' || strtolower($app['status']) === 'approved'): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($app['full_name']) ?></td>
                                        <td><?= htmlspecialchars($app['email']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($app['status']); ?>">
                                                <?= htmlspecialchars($app['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($app['date_applied']) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Scholarship Applicants -->
            <div class="tab-pane fade" id="scholarship" role="tabpanel" aria-labelledby="scholarship-tab">
                <h5>Scholarship Applicants</h5>
                <div class="table-responsive">
                    <table class="table table-striped" aria-describedby="scholarship-applicants-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Full Name</th>
                                <th scope="col">Email</th>
                                <th scope="col">Status</th>
                                <th scope="col">Date Applied</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredApplications as $application): ?>
                                <?php if (strtolower($application['status']) === 'pending'): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($application['fullname']) ?></td>
                                        <td><?= htmlspecialchars($application['email']) ?></td>
                                        <td>
                                            <span class="status-badge status-pending">
                                                <?= htmlspecialchars($application['status']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($application['applied_at']) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Calendar -->
            <div class="tab-pane fade" id="calendar-page" role="tabpanel" aria-labelledby="calendar-tab">
                <h5>Calendar View</h5>
                <div class="calendar-container">
                    <div id="calendar" aria-label="Event calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script>
        let calendar;
        document.addEventListener('DOMContentLoaded', function() {
            const calendarTab = document.getElementById('calendar-tab');
            calendarTab.addEventListener('shown.bs.tab', function() {
                const calendarEl = document.getElementById('calendar');
                if (calendar) calendar.destroy();
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    height: 500,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                    },
                    events: [{
                            title: 'Admission Deadline',
                            start: '2025-05-15'
                        },
                        {
                            title: 'Scholarship Interview',
                            start: '2025-05-20'
                        },
                        {
                            title: 'Orientation',
                            start: '2025-06-01',
                            end: '2025-06-03'
                        },
                        {
                            title: 'Scholarship Results',
                            start: '2025-06-10'
                        }
                    ]
                });
                calendar.render();
            });
        });

        document.getElementById("searchInput").addEventListener("input", function() {
            const filter = this.value.toLowerCase();
            const activeTab = document.querySelector(".tab-content .tab-pane.active");

            if (!activeTab) return;

            const rows = activeTab.querySelectorAll("tbody tr");

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? "" : "none";
            });
        });
    </script>
</body>

</html>