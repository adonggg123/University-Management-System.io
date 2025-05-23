<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'db_conn.php';

// Handle add student
$add_error = '';
$add_success = ''; // For success message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $student_no = trim($_POST['student_no']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $middle_name = trim($_POST['middle_name']);
    $course = trim($_POST['course']);
    $year_level = intval($_POST['year_level']);

    if ($student_no && $first_name && $last_name && $course && $year_level) {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (student_no, first_name, last_name, middle_name, course, year_level) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$student_no, $first_name, $last_name, $middle_name, $course, $year_level]);
            // Set success message for display after redirect (or directly if not redirecting)
            $add_success = "Student added successfully."; 
            // header('Location: students.php?success=1'); // Can remove redirect if displaying message directly
            // exit;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Check for duplicate entry
                 $add_error = "Student number already exists.";
            } else {
                 $add_error = "Error adding student. Please check data and try again.";
            }
        }
    } else {
        $add_error = "Please fill in all required fields.";
    }
}

// Handle search
$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_no LIKE ? OR first_name LIKE ? OR last_name LIKE ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM students1 ORDER BY created_at DESC LIMIT 20");
}
$students = $stmt->fetchAll();
if (isset($_GET['success']) && $_GET['success'] == '1' && !$add_success) { // From redirect
    $add_success = "Student added successfully.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - UMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="students.php">Students</a></li>
                    <?php if (in_array($_SESSION['role'], ['staff', 'admin'])): ?>
                        <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="payments.php">Payments</a></li>
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
        <h2 class="mb-4 text-center">Manage Students</h2>

        <!-- Search Form -->
        <form method="get" class="row g-3 mb-4 align-items-center justify-content-center">
            <div class="col-auto" style="flex-grow: 1; max-width: 400px;">
                <label for="search" class="visually-hidden">Search Student</label>
                <input type="text" name="search" id="search" class="form-control" placeholder="Search student no, name..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>

        <?php if ($add_success): ?>
            <div class="alert alert-success" role="alert"><?= htmlspecialchars($add_success) ?></div>
        <?php endif; ?>
        <?php if ($add_error): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($add_error) ?></div>
        <?php endif; ?>

        <!-- Add Student Form - Toggled by a button -->
        <div class="text-center mb-3">
            <button class="btn btn-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#addStudentForm" aria-expanded="false" aria-controls="addStudentForm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle-fill me-2" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v3h-3a.5.5 0 0 0 0 1h3v3a.5.5 0 0 0 1 0v-3h3a.5.5 0 0 0 0-1h-3v-3z"/></svg>
                Add New Student
            </button>
        </div>
        <div class="collapse mb-4" id="addStudentForm">
            <div class="card card-body shadow-sm">
                <h3 class="mb-3 text-center">Add Student Details</h3>
                <form method="post" action="students.php">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="student_no" class="form-label">Student No*</label>
                            <input type="text" name="student_no" id="student_no" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name*</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name*</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="middle_name" class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" id="middle_name" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label for="course" class="form-label">Course*</label>
                            <input type="text" name="course" id="course" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label for="year_level" class="form-label">Year Level*</label>
                            <input type="number" name="year_level" id="year_level" class="form-control" min="1" max="10" required>
                        </div>
                    </div>
                    <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                         <button type="button" class="btn btn-secondary me-md-2" data-bs-toggle="collapse" data-bs-target="#addStudentForm">Cancel</button>
                        <button type="submit" name="add_student" class="btn btn-primary">Add Student</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Students Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="mb-0">Student List</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student No</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($students): ?>
                            <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['student_no']) ?></td>
                                <td><?= htmlspecialchars($s['last_name'] . ', ' . $s['first_name'] . ' ' . $s['middle_name']) ?></td>
                                <td><?= htmlspecialchars($s['course']) ?></td>
                                <td><?= htmlspecialchars($s['year_level']) ?></td>
                                <td><?= htmlspecialchars(date('Y-m-d', strtotime($s['created_at']))) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center p-3">No students found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
             <?php if ($students): // Basic pagination placeholder/info ?>
                <div class="card-footer text-muted">
                    Displaying up to 20 students.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
