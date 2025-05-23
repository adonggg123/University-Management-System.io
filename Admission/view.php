<?php
session_start();
include('connect.php');

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// After submission, this page should be redirected to with an ID
if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>No application specified.</div>";
    exit();
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null) {
    echo "<div class='alert alert-danger'>Invalid application ID.</div>";
    exit();
}

// Fetch application data
$stmt = $conn->prepare("SELECT * FROM student_applications WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows == 0) {
    echo "<div class='alert alert-warning'>Application not found.</div>";
    $stmt->close();
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

// Fetch notification data
$stmt = $conn->prepare("SELECT message FROM admi_notifications WHERE student_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$notif_result = $stmt->get_result();
$notif_message = "";

if ($notif_result && $notif_result->num_rows > 0) {
    $notif_row = $notif_result->fetch_assoc();
    $notif_message = $notif_row['message'];
}
$stmt->close();

// Delete functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<div class='alert alert-danger'>Invalid CSRF token.</div>";
        exit();
    }

    try {
        $conn->begin_transaction();

        // Delete related notifications
        $stmt_notifications = $conn->prepare("DELETE FROM admi_notifications WHERE student_id = ?");
        if (!$stmt_notifications) {
            throw new Exception("Prepare notifications delete failed: " . $conn->error); 
        }
        $stmt_notifications->bind_param("i", $id);
        if (!$stmt_notifications->execute()) {
            throw new Exception("Execute notifications delete failed: " . $stmt_notifications->error);
        }
        $stmt_notifications->close();

        // Delete student application
        $stmt_application = $conn->prepare("DELETE FROM student_applications WHERE id = ?");
        if (!$stmt_application) {
            throw new Exception("Prepare application delete failed: " . $conn->error);
        }
        $stmt_application->bind_param("i", $id);
        if (!$stmt_application->execute()) {
            throw new Exception("Execute application delete failed: " . $stmt_application->error);
        }
        $stmt_application->close();

        $conn->commit();
        header("Location: index.php?success=Application deleted successfully");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Delete error: " . $e->getMessage());
        echo "<div class='alert alert-danger'>Error deleting application: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Fetch notification count
$stmt = $conn->prepare("SELECT COUNT(*) as new_count FROM admi_notifications WHERE student_id = ? AND is_read = FALSE");
$stmt->bind_param("i", $id);
$stmt->execute();
$count_result = $stmt->get_result();
$notif_count = 0;

if ($count_result) {
    $count_row = $count_result->fetch_assoc();
    $notif_count = $count_row['new_count'];
}
$stmt->close();

// Mark notifications as read
$stmt = $conn->prepare("UPDATE admi_notifications SET is_read = TRUE WHERE student_id = ? AND is_read = FALSE");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Submitted Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        footer {
            background-color: rgba(22, 2, 67);
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .status-btn {
            padding: 10px 16px;
            font-weight: bold;
            border-radius: 12px;
            text-align: center;
        }

        .status-btn.pending {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-btn.approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-btn.scheduled {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-btn.passed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-btn.failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-btn.declined {
            background-color: red;
            color: white;
            padding: 4px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .application-card {
            margin-top: 20px;
        }

        .delete-btn {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #dc3545;
            border: none;
            color: white;
            cursor: pointer;
            border-radius: 12px;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .text-center {
            text-align: center;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        thead th {
            background-color: rgba(22, 2, 67, 1) !important;
            color: white !important;
            border-color: #ddd !important;
        }

        .section-title {
            background-color: #160243;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
        }

        .form-table {
            table-layout: fixed;
            width: 100%;
        }

        .form-table th {
            width: 30%;
            white-space: nowrap;
        }

        .form-table td {
            width: 70%;
            word-wrap: break-word;
        }
    </style>
</head>

<body class="bg-light">
    <div class="container mt-3 d-flex justify-content-end">
        <button class="btn text-white position-relative" style="background-color: rgba(22,2,67,1);" data-bs-toggle="modal" data-bs-target="#notificationsModal">
            <i class="bi bi-bell-fill"></i> Notifications
            <?php if ($notif_count > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= $notif_count ?>
                </span>
            <?php endif; ?>
        </button>
        <a href="logout.php" class="btn text-white ms-2" style="background-color: rgba(22,2,67,1);">
            <i class="bi bi-box-arrow-right"></i>Logout
        </a>
    </div>

    <!-- Notifications Modal -->
    <div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-white" style="background-color: rgba(22,2,67,1);">
                    <h5 class="modal-title" id="notificationsModalLabel">Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group">
                        <li class="list-group-item"><?= !empty($notif_message) ? htmlspecialchars($notif_message) : 'No notifications yet.' ?></li>
                        <li class="list-group-item">Status: <strong><?= htmlspecialchars($row['status']) ?></strong></li>
                        <li class="list-group-item">You may be contacted at <strong><?= htmlspecialchars($row['email']) ?></strong></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="mb-4 fw-bold" style="color: rgba(22,2,67,1);">
                    <i class="bi bi-bar-chart-fill me-2"></i>Application Status
                </h2>

                <div class="table-responsive">
                    <table class="table table-bordered bg-white">
                        <thead style="background-color: rgba(22,2,67,1); color: white;">
                            <tr>
                                <th>Full Name</th>
                                <th>Date Applied</th>
                                <th>Admission Status</th>
                                <th>Entrance Exam Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $conn->prepare("SELECT * FROM student_applications WHERE id = ?");
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $status_result = $stmt->get_result();
                            while ($status_row = $status_result->fetch_assoc()) {
                                $status = strtolower($status_row['status']);
                                switch ($status) {
                                    case 'approved':
                                        $admission_class = 'approved';
                                        break;
                                    case 'declined':
                                        $admission_class = 'declined';
                                        break;
                                    case 'pending':
                                    default:
                                        $admission_class = 'pending';
                                        break;
                                }

                                $exam_status = !empty($status_row['exam_status']) ? $status_row['exam_status'] : 'not scheduled';
                            ?>
                                <tr>
                                    <td class="p-3"><?= htmlspecialchars($row['full_name']) ?></td>
                                    <td class="p-3"><?= htmlspecialchars($row['date_applied']) ?></td>
                                    <td class="text-center p-3">
                                        <span class="status-btn <?= $admission_class ?>"><?= htmlspecialchars($status_row['status']) ?></span>
                                    </td>
                                    <td class="text-center p-3">
                                        <?php
                                        $exam_class = 'pending';
                                        if ($exam_status == 'passed') {
                                            $exam_class = 'passed';
                                        } elseif ($exam_status == 'failed') {
                                            $exam_class = 'failed';
                                        } elseif ($exam_status == 'scheduled') {
                                            $exam_class = 'scheduled';
                                        }
                                        ?>
                                        <span class="status-btn <?= $exam_class ?>"><?= htmlspecialchars(ucwords($exam_status)) ?></span>
                                    </td>
                                    <td class="text-center p-3">
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete your application and its related notifications?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" class="delete-btn"><i class="bi bi-trash3-fill"></i> Delete Application</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php }
                            $stmt->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Submitted Application Section -->
    <div class="container mt-5">
        <div class="card shadow p-4 bg-white" id="form-preview">
            <h2 class="mb-4 fw-bold text-dark" style="font-size: 30px;">
                <i class="bi bi-file-text-fill me-2"></i>Your Submitted Application
            </h2>
            <!-- Personal Info -->
            <div class="section-title h5 mb-3">Personal Information</div>
            <div class="mb-4">
                <table class="table table-bordered form-table">
                    <tr>
                        <th>Full Name</th>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                    </tr>
                    <tr>
                        <th>Contact</th>
                        <td><?= htmlspecialchars($row['contact']) ?></td>
                    </tr>
                    <tr>
                        <th>Gender</th>
                        <td><?= htmlspecialchars($row['gender']) ?></td>
                    </tr>
                    <tr>
                        <th>Address</th>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                    </tr>
                    <tr>
                        <th>Birthdate</th>
                        <td><?= htmlspecialchars($row['birthdate']) ?></td>
                    </tr>
                    <tr>
                        <th>Citizenship</th>
                        <td><?= htmlspecialchars($row['citizenship']) ?></td>
                    </tr>
                </table>
            </div>

            <!-- Educational Background -->
            <div class="section-title h5 mb-3">Educational Background</div>
            <div class="mb-4">
                <table class="table table-bordered form-table">
                    <tr>
                        <th>Last School Attended</th>
                        <td><?= htmlspecialchars($row['last_school']) ?></td>
                    </tr>
                    <tr>
                        <th>School Address</th>
                        <td><?= htmlspecialchars($row['school_address']) ?></td>
                    </tr>
                    <tr>
                        <th>Year Graduated</th>
                        <td><?= htmlspecialchars($row['year_graduated']) ?></td>
                    </tr>
                    <tr>
                        <th>GPA</th>
                        <td><?= htmlspecialchars($row['gpa']) ?></td>
                    </tr>
                </table>
            </div>

            <!-- Course Preferences -->
            <div class="section-title h5 mb-3">Course Preferences</div>
            <div class="mb-4">
                <table class="table table-bordered form-table">
                    <tr>
                        <th>First Choice</th>
                        <td><?= htmlspecialchars($row['course_choice_1']) ?></td>
                    </tr>
                    <tr>
                        <th>Second Choice</th>
                        <td><?= htmlspecialchars($row['course_choice_2']) ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                    </tr>
                    <tr>
                        <th>Date Applied</th>
                        <td><?= htmlspecialchars($row['date_applied']) ?></td>
                    </tr>
                    <tr>
                        <th>Time Submitted</th>
                        <td><?= htmlspecialchars($row['time_submitted']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="container no-print mt-4">
        <div class="card shadow p-3 bg-white" style="width: 100%;">
            <div class="d-flex justify-content-end">
                <button id="download-btn" class="btn me-2" style="background-color: rgba(22,2,67,1); color: white;">
                    <i class="bi bi-download"></i> Download application
                </button>
            </div>
        </div>
    </div>

    <footer class="mt-5">
        © 2025 USTP Admission | All Rights Reserved
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script>
        document.getElementById("download-btn").addEventListener("click", function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();
            const element = document.getElementById("form-preview");

            html2canvas(element).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                const imgProps = doc.getImageProperties(imgData);
                const pdfWidth = doc.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                doc.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                doc.save("<?= htmlspecialchars($row['full_name']) ?>_application.pdf");
            });
        });
    </script>
</body>

</html>