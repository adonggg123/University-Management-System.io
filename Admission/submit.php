<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search and filter values
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$examStatusFilter = isset($_GET['exam_status']) ? trim($_GET['exam_status']) : '';

// Start query
$sql = "SELECT * FROM student_applications WHERE 1";
$params = [];
$types = "";

// Search filter
if (!empty($search)) {
    $sql .= " AND (full_name LIKE ? OR id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

// Status filter
if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
    $types .= "s";
}

// Exam status filter (including Not Scheduled)
if ($examStatusFilter === 'not_scheduled') {
    // Check for both NULL and empty string for "Not Scheduled"
    $sql .= " AND (exam_status IS NULL OR exam_status = '')";
} elseif (!empty($examStatusFilter)) {
    $sql .= " AND exam_status = ?";
    $params[] = $examStatusFilter;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Admissions</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    .status-btn.pending { background-color: #f8d7da; color: #721c24; }
    .status-btn.approved { background-color: #d4edda; color: #155724; }
    .status-btn.declined { background-color: #f5c6cb; color: #721c24; }
    .status-btn.scheduled { background-color: #cce5ff; color: #004085; }
    .status-btn.passed { background-color: #d4edda; color: #155724; }
    .status-btn.failed { background-color: #f8d7da; color: #721c24; }
    .status-btn.not_scheduled { background-color: #e2e3e5; color: #6c757d; }
    .table-actions button { margin-right: 5px; }
    .table td, .table th { vertical-align: middle; }
  </style>
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4">Manage Admissions</h2>

  <!-- Filter Form -->
  <form method="GET">
    <div class="row mb-3">
      <div class="col-md-4">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by name or ID...">
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">Filter by Status</option>
          <option value="approved" <?= $statusFilter == 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="declined" <?= $statusFilter == 'declined' ? 'selected' : '' ?>>Declined</option>
        </select>
      </div>
      <div class="col-md-3">
        <select name="exam_status" class="form-select">
          <option value="">Exam Status</option>
          <option value="scheduled" <?= $examStatusFilter == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
          <option value="passed" <?= $examStatusFilter == 'passed' ? 'selected' : '' ?>>Passed</option>
          <option value="failed" <?= $examStatusFilter == 'failed' ? 'selected' : '' ?>>Failed</option>
          <option value="not_scheduled" <?= $examStatusFilter == 'not_scheduled' ? 'selected' : '' ?>>Not Scheduled</option>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Apply</button>
      </div>
    </div>
  </form>

  <!-- Applicants Table -->
  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th><input type="checkbox"></th>
        <th>Full Name</th>
        <th>Date Applied</th>
        <th>Status</th>
        <th>Exam Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $statusClass = strtolower($row['status']);
              $examStatus = $row['exam_status'];
              $examStatusDisplay = $examStatus;
              $examStatusClass = strtolower($examStatus);

              if (is_null($examStatus) || $examStatus === '') {
                  $examStatusDisplay = 'Not Scheduled';
                  $examStatusClass = 'not_scheduled';
              }

              echo "<tr>
                      <td><input type='checkbox'></td>
                      <td>" . htmlspecialchars($row['full_name']) . "</td>
                      <td>" . htmlspecialchars($row['date_applied']) . "</td>
                      <td><span class='badge status-btn {$statusClass}'>" . ucfirst($row['status']) . "</span></td>
                      <td><span class='badge status-btn {$examStatusClass}'>" . ucfirst($examStatusDisplay) . "</span></td>
                      <td class='table-actions'>
                        <a href='view_application.php?id=" . urlencode($row['id']) . "' class='btn btn-sm btn-info'>View</a>
                        <button class='btn btn-sm btn-danger'>Delete</button>
                      </td>
                    </tr>";
          }
      } else {
          echo "<tr><td colspan='6' class='text-center'>No applicants found</td></tr>";
      }
      ?>
    </tbody>
  </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
