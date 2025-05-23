<?php
session_start();
include 'connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $course = trim($_POST['course']);
    $year_level = $_POST['year_level'];
    $family_income = $_POST['family_income'];
    $scholarship_id = $_POST['scholarship_id'];

    $check = $conn->prepare("SELECT id FROM scholarship_applications WHERE student_id = ? AND scholarship_id = ?");
    $check->bind_param("si", $student_id, $scholarship_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "You have already applied for this scholarship.";
    } else {
        $stmt = $conn->prepare("INSERT INTO scholarship_applications (student_id, fullname, email, course, year_level, family_income, scholarship_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $student_id, $fullname, $email, $course, $year_level, $family_income, $scholarship_id);
        $stmt->execute();

        $application_id = $stmt->insert_id;

        if (isset($_FILES['documents']) && count($_FILES['documents']['name']) > 0) {
            for ($i = 0; $i < count($_FILES['documents']['name']); $i++) {
                $document_name = $_FILES['documents']['name'][$i];
                $target = "Uploads/" . basename($document_name);

                if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $target)) {
                    $doc_stmt = $conn->prepare("INSERT INTO scholarship_documents (application_id, document_name, document_path) VALUES (?, ?, ?)");
                    $doc_stmt->bind_param("iss", $application_id, $document_name, $target);
                    $doc_stmt->execute();
                }
            }
        }

        $_SESSION['student_id'] = $student_id;
        $_SESSION['fullname'] = $fullname;
        header("Location: sch_status.php");
        exit;
    }
}

$scholarships = $conn->query("SELECT * FROM scholarships");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #160243;
            --accent-color: #2c9c9c;
            --background-color: #f0f4f8;
            --card-bg: #ffffff;
            --text-color: #2d2d2d;
            --border-color: #d8e0e8;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Roboto', 'Inter', sans-serif;
            margin: 0;
            padding: 24px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border-color);
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        h2, h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 10px;
            font-size: 14px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 156, 156, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-primary:hover {
            background-color: #0e0138;
            transform: scale(1.05);
        }

        .table {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 0;
        }

        .table th {
            background: var(--primary-color);
            color: white;
            padding: 12px;
            font-weight: 500;
        }

        .table td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .table tr {
            transition: background-color 0.2s ease;
        }

        .table tr:hover {
            background-color: #e8ecef;
            cursor: pointer;
        }

        .alert {
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            text-align: center;
            background: #e6f3f3;
            border: 1px solid var(--accent-color);
        }

        .icon {
            margin-right: 8px;
        }

        .modal-content {
            border-radius: 12px;
            border: none;
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .modal-body {
            padding: 20px;
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .card {
                padding: 16px;
            }

            .btn-primary {
                width: 100%;
            }

            .table {
                font-size: 12px;
            }

            .table th, .table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Available Scholarships Section -->
        <div class="card">
            <h3>AVAILABLE SCHOLARSHIPS</h3>
            <div class="table-responsive">
                <table class="table" id="scholarshipTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Eligibility</th>
                            <th>Deadline</th>
                            <th>Benefits</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $scholarships->fetch_assoc()) { ?>
                            <tr class="scholarship-row" data-bs-toggle="modal" data-bs-target="#modal-<?php echo $row['id']; ?>">
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><?php echo htmlspecialchars($row['eligibility']); ?></td>
                                <td><?php echo htmlspecialchars($row['deadline']); ?></td>
                                <td><?php echo htmlspecialchars($row['benefits']); ?></td>
                            </tr>

                            <div class="modal fade" id="modal-<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="modalLabel-<?php echo $row['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalLabel-<?php echo $row['id']; ?>">Scholarship Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
                                            <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                                            <p><strong>Category:</strong> <?php echo htmlspecialchars($row['category']); ?></p>
                                            <p><strong>Eligibility:</strong> <?php echo htmlspecialchars($row['eligibility']); ?></p>
                                            <p><strong>Deadline:</strong> <?php echo htmlspecialchars($row['deadline']); ?></p>
                                            <p><strong>Benefits:</strong> <?php echo htmlspecialchars($row['benefits']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Apply for Scholarship Section -->
        <div class="card">
            <h3>APPLY FOR SCHOLARSHIP</h3>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>{$error}</div>"; ?>
            <form action="sch_dashboard.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="scholarship_id">Scholarship Title</label>
                    <select name="scholarship_id" class="form-select" required>
                        <option value="" disabled selected>Select a Scholarship</option>
                        <?php
                        $scholarships->data_seek(0);
                        while ($row = $scholarships->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>" . htmlspecialchars($row['title']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="student_id">Student ID</label>
                    <input type="text" name="student_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" name="fullname" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="course">Course</label>
                    <input type="text" name="course" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="year_level">Year Level</label>
                    <select name="year_level" class="form-select" required>
                        <option value="" disabled selected>Select Year</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="family_income">Family Income</label>
                    <input type="number" name="family_income" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="documents">Upload Supporting Documents</label>
                    <input type="file" name="documents[]" class="form-control" multiple required>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane icon"></i>Submit Application</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Ensure modals are initialized correctly and prevent hover interference
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Bootstrap modals
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                new bootstrap.Modal(modal);
            });

            // Handle row clicks for modal trigger
            const rows = document.querySelectorAll('.scholarship-row');
            rows.forEach(row => {
                row.addEventListener('click', function (e) {
                    e.stopPropagation(); // Prevent event bubbling
                    const modalId = this.getAttribute('data-bs-target').substring(1); // Remove #
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                        bsModal.show();
                    }
                });
            });
        });
    </script>
</body>
</html>