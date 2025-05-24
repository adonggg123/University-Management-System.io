<?php
session_start();

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "quest4inno@server";
$dbname = "university_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: " . $_SERVER['PHP_SELF'] . "?error=Invalid CSRF token");
    exit();
  }

  $deleteId = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);
  if ($deleteId === false || $deleteId === null) {
    header("Location: " . $_SERVER['PHP_SELF'] . "?error=Invalid ID");
    exit();
  }

  try {
    $conn->begin_transaction();

    $stmt_notifications = $conn->prepare("DELETE FROM notifications WHERE student_id = ?");
    if (!$stmt_notifications) {
      throw new Exception("Prepare notifications delete failed: " . $conn->error);
    }
    $stmt_notifications->bind_param("i", $deleteId);
    if (!$stmt_notifications->execute()) {
      throw new Exception("Execute notifications delete failed: " . $stmt_notifications->error);
    }
    $stmt_notifications->close();

    $stmt_application = $conn->prepare("DELETE FROM student_applications WHERE id = ?");
    if (!$stmt_application) {
      throw new Exception("Prepare application delete failed: " . $conn->error);
    }
    $stmt_application->bind_param("i", $deleteId);
    if (!$stmt_application->execute()) {
      throw new Exception("Execute application delete failed: " . $stmt_application->error);
    }
    $stmt_application->close();

    $conn->commit();
    header("Location: " . $_SERVER['PHP_SELF'] . "?success=Record deleted successfully");
    exit();
  } catch (Exception $e) {
    $conn->rollback();
    error_log("Delete error: " . $e->getMessage());
    header("Location: " . $_SERVER['PHP_SELF'] . "?error=Failed to delete record: " . urlencode($e->getMessage()));
    exit();
  }
}

// Handle bulk delete for failed applicants
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_all_failed') {
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: " . $_SERVER['PHP_SELF'] . "?error=Invalid CSRF token");
    exit(); 
  }

  try {
    $conn->begin_transaction();

    $failedQuery = "SELECT id FROM student_applications WHERE LOWER(exam_status) = 'failed'";
    $failedResult = $conn->query($failedQuery);
    $failedIds = [];
    while ($row = $failedResult->fetch_assoc()) {
      $failedIds[] = $row['id'];
    }

    foreach ($failedIds as $id) {
      $stmt_notifications = $conn->prepare("DELETE FROM notifications WHERE student_id = ?");
      $stmt_notifications->bind_param("i", $id);
      $stmt_notifications->execute();
      $stmt_notifications->close();

      $stmt_application = $conn->prepare("DELETE FROM student_applications WHERE id = ?");
      $stmt_application->bind_param("i", $id);
      $stmt_application->execute();
      $stmt_application->close();
    }

    $conn->commit();
    header("Location: " . $_SERVER['PHP_SELF'] . "?success=All failed applicants deleted successfully");
    exit();
  } catch (Exception $e) {
    $conn->rollback();
    error_log("Bulk delete error: " . $e->getMessage());
    header("Location: " . $_SERVER['PHP_SELF'] . "?error=Failed to delete all failed applicants");
    exit();
  }
}

// Get filters
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM student_applications WHERE 1=1";
$params = [];
$types = "";

if (!empty($startDate)) {
  $sql .= " AND date_applied >= ?";
  $params[] = $startDate;
  $types .= "s";
}
if (!empty($endDate)) {
  $sql .= " AND date_applied <= ?";
  $params[] = $endDate;
  $types .= "s";
}

if (!empty($statusFilter)) {
  $sql .= " AND status = ?";
  $params[] = $statusFilter;
  $types .= "s";
}

$stmt = $conn->prepare($sql);
if (!$stmt) {
  die("Query preparation failed: " . $conn->error);
}
if (!empty($params)) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$mainRows = [];
$failedRows = [];

while ($row = $result->fetch_assoc()) {
  if (strtolower($row['exam_status']) === 'failed') {
    $failedRows[] = $row;
  } else {
    $mainRows[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Admissions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
      padding: 13px;
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
      background-color: #6c757d;
      width: 100px;
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
      display: flex;
      align-items: center;
      color: white;
      font-size: 1.2rem;
      font-weight: 600;
    }

    .navbar-brand img {
      transform: scale(1.2);
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

    @media (max-width: 768px) {
      body {
        padding: 20px;
      }

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
        height: 20px;
        font-size: 11px;
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
    <div class="card">
      <h2>MANAGE ADMISSIONS</h2>
      <ul class="nav nav-tabs" id="adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="main-applicants-tab" data-bs-toggle="tab" data-bs-target="#main-applicants" type="button" role="tab" aria-controls="main-applicants" aria-selected="true"><i class="fas fa-users icon"></i>Main Applicants</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="failed-applicants-tab" data-bs-toggle="tab" data-bs-target="#failed-applicants" type="button" role="tab" aria-controls="failed-applicants" aria-selected="false"><i class="fas fa-times-circle icon"></i>Failed Applicants</button>
        </li>
      </ul>
      <div class="tab-content" id="adminTabsContent">
        <!-- Main Applicants Tab -->
        <div class="tab-pane fade show active" id="main-applicants" role="tabpanel" aria-labelledby="main-applicants-tab">
          <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle icon"></i><?= htmlspecialchars($_GET['success']) ?></div>
          <?php endif; ?>
          <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><i class="fas fa-exclamation-circle icon"></i><?= htmlspecialchars($_GET['error']) ?></div>
          <?php endif; ?>
          <div class="search-bar">
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0">
                <i class="fas fa-search text-muted"></i>
              </span>
              <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Search by name or ID...">
            </div>
            <input type="date" id="start_date" name="start_date" class="form-control" placeholder="From Date" value="<?= htmlspecialchars($startDate); ?>">
            <input type="date" id="end_date" name="end_date" class="form-control" placeholder="To Date" value="<?= htmlspecialchars($endDate); ?>">
            <select id="status" name="status" class="form-select">
              <option value="">All Status</option>
              <option value="pending" <?= $statusFilter == 'pending' ? 'selected' : '' ?>>Pending</option>
              <option value="approved" <?= $statusFilter == 'approved' ? 'selected' : '' ?>>Approved</option>
              <option value="declined" <?= $statusFilter == 'declined' ? 'selected' : '' ?>>Declined</option>
            </select>
            <select id="exam_status" name="exam_status" class="form-select">
              <option value="">All Exam Status</option>
              <option value="scheduled">Scheduled</option>
              <option value="passed">Passed</option>
              <option value="not_scheduled">Not Scheduled</option>
            </select>
          </div>
          <div class="table-responsive">
            <table class="table" id="applicationsTable">
              <thead>
                <tr>
                  <th>Full Name</th>
                  <th>Date Applied</th>
                  <th>Status</th>
                  <th>Exam Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($mainRows)) {
                  foreach ($mainRows as $row) {
                    $statusClass = strtolower(trim($row['status'])) ?: 'pending';
                    $examStatusRaw = trim($row['exam_status']) ?: 'not_scheduled';
                    $examStatus = str_replace(' ', '_', strtolower($examStatusRaw));
                    $examStatusDisplay = ucwords(str_replace('_', ' ', $examStatusRaw));
                    $formattedDate = date('M d, Y', strtotime($row['date_applied']));
                    echo "<tr class='applicant-row' data-status='$statusClass' data-exam-status='$examStatus'>
                            <td class='full-name'>" . htmlspecialchars($row['full_name']) . "</td>
                            <td class='date-applied'>" . htmlspecialchars($formattedDate) . "</td>
                            <td><span class='badge badge-$statusClass'>" . ucfirst($statusClass) . "</span></td>
                            <td><span class='badge badge-$examStatus'>" . htmlspecialchars($examStatusDisplay) . "</span></td>
                            <td>
                                <a href='applicants.php?id=" . urlencode($row['id']) . "' class='btn btn-accent btn-sm'><i class='fas fa-eye icon'></i>View</a>
                                <form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this record and its related notifications?\")'>
                                    <input type='hidden' name='action' value='delete'>
                                    <input type='hidden' name='delete_id' value='" . $row['id'] . "'>
                                    <input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>
                                    <button type='submit' class='btn btn-danger btn-sm'><i class='fas fa-trash icon'></i>Delete</button>
                                </form>
                            </td>
                        </tr>";
                  }
                } else {
                  echo "<tr><td colspan='5' class='text-center'>No applicants found</td></tr>";
                } ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Failed Applicants Tab -->
        <div class="tab-pane fade" id="failed-applicants" role="tabpanel" aria-labelledby="failed-applicants-tab">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Full Name</th>
                  <th>Date Applied</th>
                  <th>Status</th>
                  <th>Exam Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($failedRows)) {
                  foreach ($failedRows as $row) {
                    $statusClass = strtolower(trim($row['status'])) ?: 'pending';
                    $formattedDate = date('M d, Y', strtotime($row['date_applied']));
                    echo "<tr>
                            <td>" . htmlspecialchars($row['full_name']) . "</td>
                            <td>" . htmlspecialchars($formattedDate) . "</td>
                            <td><span class='badge badge-$statusClass'>" . ucfirst($statusClass) . "</span></td>
                            <td><span class='badge badge-failed'>Failed</span></td>
                            <td>
                                <a href='applicants.php?id=" . urlencode($row['id']) . "' class='btn btn-accent btn-sm'><i class='fas fa-eye icon'></i>View</a>
                                <form method='POST' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this record and its related notifications?\")'>
                                    <input type='hidden' name='action' value='delete'>
                                    <input type='hidden' name='delete_id' value='" . $row['id'] . "'>
                                    <input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "'>
                                    <button type='submit' class='btn btn-danger btn-sm'><i class='fas fa-trash icon'></i>Delete</button>
                                </form>
                            </td>
                        </tr>";
                  }
                } else {
                  echo "<tr><td colspan='5' class='text-center'>No failed applicants found</td></tr>";
                } ?>
              </tbody>
            </table>
          </div>
          <?php if (!empty($failedRows)): ?>
            <form method="POST" onsubmit="return confirm('Are you sure you want to delete all failed applicants and their notifications?')">
              <input type="hidden" name="action" value="delete_all_failed">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
              <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt icon"></i>Delete All Failed Applicants</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function applyFilter() {
      const searchValue = $('#searchInput').val().toLowerCase();
      const statusFilter = $('#status').val().toLowerCase();
      const examStatusFilter = $('#exam_status').val().toLowerCase();
      const startDate = $('#start_date').val();
      const endDate = $('#end_date').val();

      $('#applicationsTable tbody tr').each(function() {
        const row = $(this);
        const name = row.find('.full-name').text().toLowerCase();
        const date = row.find('.date-applied').text();
        const rowStatus = row.data('status').toLowerCase();
        const rowExamStatus = row.data('exam-status').toLowerCase();
        const rowDate = new Date(date);

        const matchesSearch = name.includes(searchValue);
        const matchesStatus = !statusFilter || rowStatus === statusFilter;
        const matchesExamStatus = !examStatusFilter || rowExamStatus === examStatusFilter;
        let matchesDate = true;

        if (startDate && endDate) {
          const start = new Date(startDate);
          const end = new Date(endDate);
          if (start > end) {
            alert('End date must be after start date.');
            return;
          }
          matchesDate = rowDate >= start && rowDate <= end;
        } else if (startDate) {
          matchesDate = rowDate >= new Date(startDate);
        } else if (endDate) {
          matchesDate = rowDate <= new Date(endDate);
        }

        row.toggle(matchesSearch && matchesStatus && matchesExamStatus && matchesDate);
      });
    }

    function applyFilters() {
      applyFilter();
      const url = new URL(window.location);
      url.searchParams.set('start_date', $('#start_date').val());
      url.searchParams.set('end_date', $('#end_date').val());
      url.searchParams.set('status', $('#status').val());
      window.history.replaceState(null, null, url);
    }

    function resetFilters() {
      $('#searchInput').val('');
      $('#start_date').val('');
      $('#end_date').val('');
      $('#status').val('');
      $('#exam_status').val('');
      applyFilters();
    }

    $('#searchInput, #status, #exam_status, #start_date, #end_date').on('input change', applyFilter);

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

<?php
$stmt->close();
$conn->close();
?>