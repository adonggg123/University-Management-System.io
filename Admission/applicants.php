<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_management_system";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate 'id' parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || intval($_GET['id']) <= 0) {
    echo "Invalid ID";
    exit;
}

$id = intval($_GET['id']);

// Prepare and execute query safely
$sql = "SELECT * FROM applicants WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Applicant not found.";
    exit;
}

$applicant = $result->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .container {
            max-width: 900px;
            margin: 0 auto;
            flex-grow: 1;
            padding: 30px;
        }

        .profile-header {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }

        .profile-img-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #eee;
            border: 2px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 12px;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        /* Tab Styling */
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

        /* Card-Based Data Styling */
        .data-card {
            display: flex;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 8px;
            transition: background-color 0.2s;
        }

        .data-card:hover {
            background-color: var(--secondary-color);
        }

        .data-label,
        .data-value {
            padding: 12px;
            font-size: 14px;
        }

        .data-label {
            width: 200px;
            font-weight: 600;
            color: var(--primary-color);
            border-right: 1px solid var(--border-color);
        }

        .data-value {
            flex: 1;
            display: flex;
            align-items: center;
        }

        /* Action Cards */
        .action-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* Uploaded Files Table */
        .files-table {
            border-collapse: collapse;
            width: 100%;
        }

        .files-table th {
            background: #ffffff;
            color: var(--primary-color);
            padding: 12px;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 2px solid var(--border-color);
            border-right: 1px solid var(--border-color);
        }

        .files-table th:last-child {
            border-right: none;
        }

        .files-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            border-right: 1px solid var(--border-color);
            font-size: 14px;
        }

        .files-table td:last-child {
            border-right: none;
        }

        .files-table tr:hover {
            background-color: var(--secondary-color);
        }

        .files-table .action-cell {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 150px;
        }

        .no-files {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 14px;
        }

        .badge {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 80px;
            height: 24px;
            text-align: center;
            box-sizing: border-box;
        }

        .badge-pending {
            background-color: #ffc107;
            color: #333;
        }

        .badge-approved {
            background-color: #28a745;
            color: white;
        }

        .badge-declined {
            background-color: #dc3545;
            color: white;
        }

        .badge-scheduled {
            background-color: #007bff;
            color: white;
        }

        .badge-passed {
            background-color: #28a745;
            color: white;
        }

        .badge-failed {
            background-color: #dc3545;
            color: white;
        }

        .badge-not_scheduled {
            width: 100px;
            background-color: #6c757d;
            color: white;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: rgba(22, 2, 67, 0.9);
        }

        .btn-accent {
            background-color: #2c9c9c;
            border-color: #2c9c9c;
            color: white;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-accent:hover {
            background-color: #247a7a;
        }

        .btn-success,
        .btn-danger,
        .btn-warning,
        .btn-secondary {
            border-radius: 8px;
            font-size: 14px;
            padding: 8px 16px;
        }

        .icon {
            margin-right: 6px;
        }

        .sidebar {
            min-width: 250px;
            background-color: var(--primary-color);
            color: white;
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
            margin-top: 20px;
            display: flex;
            align-items: center;
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .navbar-brand img {
            transform: scale(1.2);
            height: 40px;
            max-height: 40px;
            margin-right: 10px;
            margin-left: 20px;
        }

        .marg {
            margin-top: 40px;
        }

        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: bold;
        }

        .fa-graduation-cap {
            width: 25px;
        }

        .fa-exclamation {
            width: 20px;
        }

        .fa-arrow-left {
            width: 15px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                max-width: 100%;
            }

            .profile-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }

            .profile-img,
            .profile-img-placeholder {
                width: 80px;
                height: 80px;
            }

            .section-title {
                font-size: 1.1rem;
            }

            .nav-tabs .nav-link {
                font-size: 13px;
                padding: 8px 16px;
            }

            .data-card {
                flex-direction: column;
            }

            .data-label,
            .data-value {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }

            .data-label {
                padding-bottom: 8px;
            }

            .data-value {
                padding-top: 8px;
            }

            .files-table th,
            .files-table td {
                padding: 8px;
                font-size: 12px;
            }

            .files-table .action-cell {
                width: auto;
            }

            .badge {
                width: 70px;
                height: 20px;
                font-size: 11px;
            }

            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
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
        <a href="manage_admissions.php" class="active"><i class="fas fa-user-graduate"></i> Manage Admissions</a>
        <a href="sch_admin.php"><i class="fas fa-book"></i> Manage Scholarship</a>
        <a href="reports.php"><i class="fas fa-chart-line"></i> Generate Reports</a>
        <a href="#"><i class="bi bi-box-arrow-in-right"></i> Log out</a>
    </div>

    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <?php if (!empty($data['picture'])): ?>
                <img src="Uploads/<?= htmlspecialchars(basename($data['picture'])) ?>" alt="Profile Picture" class="profile-img">
            <?php else: ?>
                <div class="profile-img-placeholder">No Image</div>
            <?php endif; ?>
            <div>
                <h3 class="mb-1"><?= htmlspecialchars($data['full_name']) ?></h3>
                <p class="mb-0">ID: <?= $data['id'] ?></p>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="applicationTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="true">
                    <i class="fas fa-info-circle icon"></i>Applicant Information
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files" type="button" role="tab" aria-controls="files" aria-selected="false">
                    <i class="fas fa-file-alt icon"></i>Uploaded Files
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="applicationTabContent">
            <!-- Tab 1: Applicant Information and Actions -->
            <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                <!-- Personal Information -->
                <div class="mb-4">
                    <div class="section-title">Personal Information</div>
                    <div class="data-card">
                        <div class="data-label">Full Name</div>
                        <div class="data-value"><?= htmlspecialchars($data['full_name']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">Birthdate</div>
                        <div class="data-value"><?= htmlspecialchars($data['birthdate']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">Address</div>
                        <div class="data-value"><?= htmlspecialchars($data['address']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">Email</div>
                        <div class="data-value"><?= htmlspecialchars($data['email']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">Phone</div>
                        <div class="data-value"><?= htmlspecialchars($data['contact']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">Date Applied</div>
                        <div class="data-value"><?= htmlspecialchars($data['date_applied']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">Time Submitted</div>
                        <div class="data-value"><?= htmlspecialchars($data['time_submitted']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">Status</div>
                        <div class="data-value">
                            <span class="badge badge-<?= strtolower($data['status'] ?: 'pending') ?>">
                                <?= ucfirst($data['status'] ?: 'Pending') ?>
                            </span>
                        </div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">Exam Status</div>
                        <div class="data-value">
                            <?php
                            $examStatusRaw = trim($data['exam_status']) ?: 'not_scheduled';
                            $examStatus = str_replace(' ', '_', strtolower($examStatusRaw));
                            $examStatusDisplay = ucwords(str_replace('_', ' ', $examStatusRaw));
                            ?>
                            <span class="badge badge-<?= $examStatus ?>">
                                <?= htmlspecialchars($examStatusDisplay) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Educational Background -->
                <div class="mb-4">
                    <div class="section-title">Educational Background</div>
                    <div class="data-card">
                        <div class="data-label">Last School Attended</div>
                        <div class="data-value"><?= htmlspecialchars($data['last_school']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">School Address</div>
                        <div class="data-value"><?= htmlspecialchars($data['school_address']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">Year Graduated</div>
                        <div class="data-value"><?= htmlspecialchars($data['year_graduated']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">GPA</div>
                        <div class="data-value"><?= htmlspecialchars($data['gpa']) ?></div>
                    </div>
                </div>

                <!-- Course Preferences -->
                <div class="mb-4">
                    <div class="section-title">Course Preferences</div>
                    <div class="data-card">
                        <div class="data-label">First Choice</div>
                        <div class="data-value"><?= htmlspecialchars($data['course_choice_1']) ?></div>
                    </div>
                    <div class="data-card">
                        <div class="data-label">Second Choice</div>
                        <div class="data-value"><?= htmlspecialchars($data['course_choice_2']) ?></div>
                    </div>
                </div>

                <!-- Admission Status -->
                <div class="mb-4">
                    <div class="section-title">Admission Status</div>
                    <div class="action-card">
                        <form method="post" action="update.php" class="action-buttons">
                            <input type="hidden" name="id" value="<?= $data['id'] ?>">
                            <button type="submit" name="action" value="approved" class="btn btn-success">
                                <i class="fas fa-check icon"></i>Approve
                            </button>
                            <button type="submit" name="action" value="decline" class="btn btn-danger">
                                <i class="fas fa-times icon"></i>Decline
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Exam Status -->
                <div class="mb-4">
                    <div class="section-title">Exam Status</div>
                    <div class="action-card">
                        <form method="post" action="update.php" class="action-buttons">
                            <input type="hidden" name="id" value="<?= $data['id'] ?>">
                            <button type="submit" name="exam_action" value="passed" class="btn btn-primary">
                                <i class="fas fa-graduation-cap icon"></i>Passed
                            </button>
                            <button type="submit" name="exam_action" value="failed" class="btn btn-warning">
                                <i class="fas fa-exclamation icon"></i>Failed
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Back Button -->
                <div>
                    <a href="manage_admissions.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left icon"></i>Back to Manage Admissions
                    </a>
                </div>
            </div>

            <!-- Tab 2: Uploaded Files -->
            <div class="tab-pane fade" id="files" role="tabpanel" aria-labelledby="files-tab">
                <div class="section-title">Uploaded Files</div>
                <table class="files-table">
                    <thead>
                        <tr>
                            <th>File Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $file_fields = ['transcript', 'good_moral', 'birth_certificate', 'picture', 'valid_id'];
                        foreach ($file_fields as $field) {
                            if (!empty($data[$field])) {
                                $filename = basename($data[$field]);
                                echo "<tr>
                                    <td>" . ucfirst(str_replace('_', ' ', $field)) . "</td>
                                    <td class='action-cell'>
                                        <a href='dl.php?file=" . urlencode($filename) . "' class='btn btn-primary btn-sm'>
                                            <i class='fas fa-download icon'></i>Download
                                        </a>
                                    </td>
                                </tr>";
                            }
                        }
                        ?>
                        <?php if (empty($data['transcript']) && empty($data['good_moral']) && empty($data['birth_certificate']) && empty($data['picture']) && empty($data['valid_id'])): ?>
                            <tr>
                                <td colspan="2" class="no-files">No files uploaded</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php $conn->close(); ?>