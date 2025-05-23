<?php
include 'connect.php';

if (!isset($_GET['id'])) {
    die("No application ID provided.");
}

$id = intval($_GET['id']);

// Handle approval/decline actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];
    if (in_array($action, ['Approved', 'Declined'])) {
        $stmt = $conn->prepare("UPDATE scholarship_applications SET status = ? WHERE id = ?");
        if (!$stmt) {
            die("Failed to prepare SQL statement for approval/decline action.");
        }
        $stmt->bind_param("si", $action, $id);
        $stmt->execute();
        $stmt->close();
        header("Location: sch_admin.php");
        exit();
    }
}

// Fetch application data with scholarship title
$stmt = $conn->prepare("
    SELECT sa.*, s.title AS scholarship_title
    FROM scholarship_applications sa
    LEFT JOIN scholarships s ON sa.scholarship_id = s.id
    WHERE sa.id = ?
");
if (!$stmt) {
    die("Failed to prepare SQL statement for fetching application data.");
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$applicant = $result->fetch_assoc();
$stmt->close();

if (!$applicant) {
    die("Application not found.");
}

// Fetch uploaded documents from the scholarship_documents table
$docs = [];
$doc_stmt = $conn->prepare("SELECT document_name FROM scholarship_documents WHERE application_id = ?");
if (!$doc_stmt) {
    die("Failed to prepare SQL statement for fetching documents: " . $conn->error);
}

$doc_stmt->bind_param("i", $id);
$doc_stmt->execute();
$doc_result = $doc_stmt->get_result();
while ($row = $doc_result->fetch_assoc()) {
    $docs[] = $row['document_name'];
}
$doc_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applicant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            --accent-color: #2c9c9c;
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
            max-width: 800px;
            margin: 0 auto;
            flex-grow: 1;
            padding: 30px;
        }

        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 24px;
            border: 1px solid var(--border-color);
        }

        h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: rgba(22, 2, 67, 0.9);
        }

        .btn-accent {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
        }

        .btn-accent:hover {
            background-color: #247a7a;
        }

        .btn-danger, .btn-secondary {
            border-radius: 8px;
            font-size: 14px;
        }

        .icon {
            margin-right: 8px;
        }

        .nav-tabs {
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 24px;
        }

        .nav-tabs .nav-link {
            color: var(--text-color);
            padding: 10px 20px;
            border: none;
            border-radius: 8px 8px 0 0;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.3s, color 0.3s;
        }

        .nav-tabs .nav-link:hover {
            background-color: var(--border-color);
        }

        .nav-tabs .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .tab-content {
            padding: 16px 0;
        }

        .list-group-item {
            border-radius: 8px;
            margin-bottom: 8px;
            border: 1px solid var(--border-color);
        }

        .detail-item {
            margin-bottom: 16px;
            font-size: 14px;
        }

        .detail-item strong {
            color: var(--primary-color);
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
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

        .marg {
            margin-top: 40px;
        }

        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .card {
                padding: 16px;
            }

            .btn {
                width: 100%;
            }

            .nav-tabs .nav-link {
                padding: 8px 12px;
                font-size: 12px;
            }

            .action-buttons {
                flex-direction: column;
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
        <a href="sch_admin.php" class="active"><i class="fas fa-book"></i> Manage Scholarship</a>
        <a href="reports.php"><i class="fas fa-chart-line"></i> Generate Reports</a>
        <a href="#"><i class="bi bi-box-arrow-in-right"></i> Log out</a>
    </div>

    <div class="container">
        <div class="card">
            <h2><i class="fas fa-user-graduate icon"></i>Applicant Details</h2>
            <ul class="nav nav-tabs" id="applicantTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true"><i class="fas fa-info-circle icon"></i>Details</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab" aria-controls="documents" aria-selected="false"><i class="fas fa-file-alt icon"></i>Documents</button>
                </li>
            </ul>
            <div class="tab-content" id="applicantTabsContent">
                <!-- Applicant Details Tab -->
                <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                    <div class="detail-item"><strong>Student ID:</strong> <?= htmlspecialchars($applicant['student_id']) ?></div>
                    <div class="detail-item"><strong>Fullname:</strong> <?= htmlspecialchars($applicant['fullname']) ?></div>
                    <div class="detail-item"><strong>Email:</strong> <?= htmlspecialchars($applicant['email']) ?></div>
                    <div class="detail-item"><strong>Course:</strong> <?= htmlspecialchars($applicant['course']) ?></div>
                    <div class="detail-item"><strong>Year Level:</strong> <?= htmlspecialchars($applicant['year_level']) ?></div>
                    <div class="detail-item"><strong>Date Applied:</strong> <?= htmlspecialchars($applicant['applied_at']) ?></div>
                    <div class="detail-item"><strong>Scholarship Applied:</strong> <?= htmlspecialchars($applicant['scholarship_title']) ?></div>
                    <div class="detail-item"><strong>Status:</strong> <?= htmlspecialchars($applicant['status']) ?></div>
                </div>
                <!-- Uploaded Documents Tab -->
                <div class="tab-pane fade" id="documents" role="tabpanel" aria-labelledby="documents-tab">
                    <?php if (!empty($docs)): ?>
                        <strong>Uploaded Documents:</strong>
                        <ul class="list-group mt-2">
                            <?php foreach ($docs as $file): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($file) ?>
                                    <a href="Uploads/<?= urlencode($file) ?>" download class="btn btn-sm btn-accent"><i class="fas fa-download icon"></i>Download</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No documents uploaded.</p>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Action Buttons -->
            <form method="POST" class="mt-4">
                <div class="action-buttons">
                    <button type="submit" name="action" value="Approved" class="btn btn-accent"><i class="fas fa-check icon"></i>Approve</button>
                    <button type="submit" name="action" value="Declined" class="btn btn-danger"><i class="fas fa-times icon"></i>Decline</button>
                    <a href="sch_admin.php" class="btn btn-secondary"><i class="fas fa-arrow-left icon"></i>Back</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>