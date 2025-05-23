<?php
include 'connect.php';

$title = $description = $category = $eligibility = $deadline = $benefits = "";
$success = "";

$messages = $conn->query("SELECT * FROM contact_messages1 ORDER BY submitted_at DESC");

// Handle new scholarship submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $eligibility = $_POST['eligibility'];
    $deadline = $_POST['deadline'];
    $benefits = $_POST['benefits'];

    $sql = "INSERT INTO scholarships (title, description, category, eligibility, deadline, benefits) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }

    $stmt->bind_param("ssssss", $title, $description, $category, $eligibility, $deadline, $benefits);

    if ($stmt->execute()) {
        $success = "Scholarship added successfully!";
        $title = $description = $category = $eligibility = $deadline = $benefits = "";
    } else {
        $success = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Delete all declined applications
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_all_declined'])) {
    $sql = "DELETE FROM scholarship_applications WHERE status = 'Declined'";
    if ($conn->query($sql) === TRUE) {
        echo '<div class="alert alert-success">All declined applicants have been deleted.</div>';
    } else {
        echo '<div class="alert alert-danger">Error deleting records: ' . $conn->error . '</div>';
    }
}

// Fetch applications
$applications = $conn->query("SELECT sa.*, s.title AS scholarship_title 
                              FROM scholarship_applications sa 
                              LEFT JOIN scholarships s ON sa.scholarship_id = s.id 
                              ORDER BY sa.id DESC");

$declined = $conn->query("SELECT sa.*, s.title AS scholarship_title 
                          FROM scholarship_applications sa 
                          LEFT JOIN scholarships s ON sa.scholarship_id = s.id 
                          WHERE sa.status = 'Declined' 
                          ORDER BY sa.id DESC");

$scholarships = $conn->query("SELECT DISTINCT title FROM scholarships ORDER BY title ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
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

        .card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 24px;
            border: 1px solid var(--border-color);
        }

        h2,
        h4 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 10px;
            font-size: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(22, 2, 67, 0.25);
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
            background-color: #2c9c9c;
            border-color: #2c9c9c;
            color: white;
            border-radius: 8px;
            padding: 4px 10px;
            font-size: 14px;
        }

        .btn-accent:hover {
            background-color: #247a7a;
        }

        .btn-danger {
            border-radius: 8px;
            font-size: 14px;
        }

        .table {
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 24px;
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

        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }

        .table-responsive::-webkit-scrollbar {
            width: 8px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #2c9c9c;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: var(--border-color);
        }

        .search-bar {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 24px;
            align-items: center;
        }

        .search-bar .input-group {
            flex: 1;
            min-width: 200px;
        }

        .alert {
            border-radius: 8px;
            padding: 12px;
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

        .badge {
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 12px;
            display: inline-block;
            width: 80px;
            text-align: center;
            line-height: 1.5;
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
            margin-top: 40px;
        }

        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.2);
            font-weight: bold;
        }

        .marg {
            margin-top: 28px;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            .card {
                padding: 16px;
            }

            .search-bar {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }

            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 8px;
            }

            .nav-tabs .nav-link {
                padding: 8px 12px;
                font-size: 12px;
            }

            .table-responsive {
                max-height: 300px;
            }

            .badge {
                width: 70px;
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
            <h2>MANAGE SCHOLARSHIPS</h2>
            <ul class="nav nav-tabs" id="adminTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="add-scholarship-tab" data-bs-toggle="tab" data-bs-target="#add-scholarship" type="button" role="tab" aria-controls="add-scholarship" aria-selected="true"><i class="fas fa-plus-circle icon"></i>Add Scholarship</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="applicants-tab" data-bs-toggle="tab" data-bs-target="#applicants" type="button" role="tab" aria-controls="applicants" aria-selected="false"><i class="fas fa-users icon"></i>Applicants</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="declined-tab" data-bs-toggle="tab" data-bs-target="#declined" type="button" role="tab" aria-controls="declined" aria-selected="false"><i class="fas fa-times-circle icon"></i>Declined Applicants</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="messages-tab" data-bs-toggle="tab" data-bs-target="#messages" type="button" role="tab" aria-controls="messages" aria-selected="false"><i class="fas fa-envelope icon"></i>Student Messages</button>
                </li>
            </ul>
            <div class="tab-content" id="adminTabsContent">
                <!-- Add Scholarship Tab -->
                <div class="tab-pane fade show active" id="add-scholarship" role="tabpanel" aria-labelledby="add-scholarship-tab">
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="title">Scholarship Title</label>
                            <input type="text" name="title" id="title" class="form-control" required value="<?php echo htmlspecialchars($title); ?>">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" required><?php echo htmlspecialchars($description); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-select" required>
                                <option value="" disabled <?php if (!$category) echo "selected"; ?>>Select a category</option>
                                <option value="Academic" <?php if ($category == "Academic") echo "selected"; ?>>Academic</option>
                                <option value="Financial Need" <?php if ($category == "Financial Need") echo "selected"; ?>>Financial Need</option>
                                <option value="Special Talent" <?php if ($category == "Special Talent") echo "selected"; ?>>Special Talent</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="eligibility">Eligibility</label>
                            <textarea name="eligibility" id="eligibility" class="form-control" required><?php echo htmlspecialchars($eligibility); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="deadline">Deadline</label>
                            <input type="date" name="deadline" id="deadline" class="form-control" required value="<?php echo htmlspecialchars($deadline); ?>">
                        </div>
                        <div class="form-group">
                            <label for="benefits">Benefits</label>
                            <textarea name="benefits" id="benefits" class="form-control" required><?php echo htmlspecialchars($benefits); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus icon"></i>Add Scholarship</button>
                    </form>
                </div>

                <!-- Applicants Tab -->
                <div class="tab-pane fade" id="applicants" role="tabpanel" aria-labelledby="applicants-tab">
                    <div class="search-bar">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search applicants...">
                        </div>
                        <select id="filterStatus" class="form-select">
                            <option value="">All Status</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Declined">Declined</option>
                        </select>
                        <select id="filterScholarship" class="form-select">
                            <option value="">All Scholarships</option>
                            <?php while ($sch = $scholarships->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($sch['title']); ?>"><?php echo htmlspecialchars($sch['title']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="table" id="applicantTable">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Fullname</th>
                                    <th>Email</th>
                                    <th>Scholarship Program</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Reset the applications result pointer to the beginning
                                $applications->data_seek(0);
                                while ($row = $applications->fetch_assoc()): ?>
                                    <tr class="applicant-row" data-status="<?php echo $row['status']; ?>" data-scholarship="<?php echo htmlspecialchars($row['scholarship_title']); ?>">
                                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['scholarship_title'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $status = htmlspecialchars($row['status']);
                                            $badgeClass = match ($status) {
                                                'Pending' => 'badge badge-pending',
                                                'Approved' => 'badge badge-approved',
                                                'Declined' => 'badge badge-declined',
                                                default => 'badge bg-secondary',
                                            };
                                            echo "<span class='$badgeClass'>$status</span>";
                                            ?>
                                        </td>
                                        <td>
                                            <a href="sch_applicant.php?id=<?php echo $row['id']; ?>" class="btn btn-accent btn-sm"><i class="fas fa-eye"></i> View</a>
                                            <a href="sch_delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Declined Applicants Tab -->
                <div class="tab-pane fade" id="declined" role="tabpanel" aria-labelledby="declined-tab">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Fullname</th>
                                    <th>Email</th>
                                    <th>Scholarship Program</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $declined->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['scholarship_title'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $status = htmlspecialchars($row['status']);
                                            $badgeClass = match ($status) {
                                                'Pending' => 'badge badge-pending',
                                                'Approved' => 'badge badge-approved',
                                                'Declined' => 'badge badge-declined',
                                                default => 'badge bg-secondary',
                                            };
                                            echo "<span class='$badgeClass'>$status</span>";
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <form method="POST">
                        <button type="submit" name="delete_all_declined" class="btn btn-danger"><i class="fas fa-trash-alt icon"></i>Delete All Declined Applicants</button>
                    </form>
                </div>

                <!-- Student Messages Tab -->
                <div class="tab-pane fade" id="messages" role="tabpanel" aria-labelledby="messages-tab">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Fullname</th>
                                    <th>Email</th>
                                    <th>Message</th>
                                    <th>Submitted At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($msg = $messages->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($msg['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['fullname']); ?></td>
                                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                                        <td><?php echo htmlspecialchars($msg['submitted_at']); ?></td>
                                        <td>
                                            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo $msg['email']; ?>&su=Regarding%20Your%20Application"
                                                target="_blank" class="btn btn-primary btn-sm">
                                                <i class="fas fa-envelope"></i> Message
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function applyFilter() {
            const searchValue = $('#searchInput').val().toLowerCase();
            const statusFilter = $('#filterStatus').val();
            const scholarshipFilter = $('#filterScholarship').val().toLowerCase();

            $('#applicantTable tbody tr').each(function() {
                const row = $(this);
                const rowText = row.text().toLowerCase();
                const rowStatus = row.data('status');
                const rowScholarship = row.data('scholarship')?.toLowerCase() || '';

                const matchesSearch = rowText.includes(searchValue);
                const matchesStatus = !statusFilter || rowStatus === statusFilter;
                const matchesScholarship = !scholarshipFilter || rowScholarship === scholarshipFilter;

                row.toggle(matchesSearch && matchesStatus && matchesScholarship);
            });
        }

        $('#searchInput, #filterStatus, #filterScholarship').on('input change', applyFilter);

        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(alert => alert.classList.add('fade-out'));
            setTimeout(() => document.querySelectorAll('.alert').forEach(alert => alert.remove()), 500);
        }, 3000);

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>

</html>