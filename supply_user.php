<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>supply_user</title>
        <link rel="stylesheet" href="supply.css?v=<?php echo time(); ?>">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJtJ7tJkPmcV9f9fGvGkUuJkqMX6IQWuK/4hDh3KpWwW9Dptf4U/JpP4OmVZ" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0v8FqI1P+8zUuK0CptlX+u0xP1z5DiH1ua7Tgpm2U4B7w+My" crossorigin="anonymous"></script>
<?php 
session_start(); 
require_once __DIR__ . '/db_conn.php';

// Initialize variables
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$item_name = '';
$quantity = 0;

// Form handling - there were two separate handlers which is causing issues
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_request'])) {
    // Get values from the form
    $user_name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $quantity = intval($_POST['quantity']);
    $user_type = mysqli_real_escape_string($conn, $_POST['user_type']);
    
    // Insert into database with proper field names
    $sql = "INSERT INTO borrow_requests (user_name, item_name, quantity, user_type, status, created_at) 
            VALUES ('$user_name', '$item_name', $quantity, '$user_type', 'pending', NOW())";
    
    if ($conn->query($sql)) {
        // Insert notification for borrow
        $note = "New borrow request: $item_name ($quantity pcs)";
        $conn->query("INSERT INTO notifications (message, status, type) VALUES ('$note', 'unread', 'borrow')");
        
        echo "<script>alert('Request submitted successfully!');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

    // Handle approve/disapprove actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['action'])) {
        $id = intval($_POST['id']);
        $status = $_POST['action'] === 'approve' ? 'approved' : 'disapproved';
        $notified = $status === 'approved' ? 0 : 1; // notify only for approved
        $conn->query("UPDATE borrow_requests SET status = '$status', notified = $notified WHERE id = $id");
        header("Location: supply_admin.php");
        exit;
    }

    // Get notifications - modified to not use user_id since it doesn't exist in your table
    $approved_notifications = $conn->query("SELECT * FROM borrow_requests WHERE status = 'approved' AND notified = 0");
    $notif_count = $approved_notifications ? $approved_notifications->num_rows : 0;

    // Mark notifications as read - modified to not use user_id
    if (isset($_GET['view_notifications'])) {
        $conn->query("UPDATE borrow_requests SET notified = 1 WHERE status = 'approved'");
    }

    // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Determine which page to show based on GET parameter
        $page = isset($_GET['page']) ? $_GET['page'] : 'home';

        // Initialize notification count
        $unread_count = 0;
        if (isset($conn)) {
            // Count unread notifications
            $notification_query = $conn->query("SELECT COUNT(*) as count FROM borrow_requests WHERE (status = 'approved' OR status = 'disapproved') AND user_notified = 0");
            if ($notification_query) {
                $unread_count = $notification_query->fetch_assoc()['count'];
            }
}
    ?>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="?page=home">
            <i class="fas fa-boxes-stacked me-2"></i>
            Supply Management System
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($page === 'home') ? 'active' : ''; ?>" href="?page=home">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($page === 'inventory') ? 'active' : ''; ?>" href="?page=inventory">Inventory</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($page === 'profile') ? 'active' : ''; ?>" href="?page=profile">User Profile</a>
                </li>
                <li class="nav-item">
                    <div class="notification-wrapper ms-3 mt-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#userNotificationModal">
                            <i class="fas fa-bell" style="color: white;"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="badge" id="notification-badge"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="forms.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container mt-4">
    <?php if ($page === 'home'): ?>
        <!-- Header Section -->
        <div class="header-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1><i class="fas fa-dolly-flatbed user-header-icon"></i> Inventory Management System</h1>
                        <p class="lead">Efficiently request and track supplies with our streamlined system</p>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-cubes" style="font-size: 6rem; opacity: 0.8;"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Dashboard Summary Cards -->
        <div class="row g-4 mt-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-boxes-stacked mb-3" style="font-size: 3rem;"></i>
                        <h5 class="card-title">Total Inventory Items</h5>
                        <h3>
                            <?php
                            $total_items = $conn->query("SELECT COUNT(*) as count FROM supplies")->fetch_assoc()['count'] ?? 0;
                            echo $total_items;
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle mb-3" style="font-size: 3rem;"></i>
                        <h5 class="card-title">Approved Requests</h5>
                        <h3>
                            <?php
                            $approved = $conn->query("SELECT COUNT(*) as count FROM borrow_requests WHERE status = 'approved'")->fetch_assoc()['count'] ?? 0;
                            echo $approved;
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <i class="fas fa-clock mb-3" style="font-size: 3rem;"></i>
                        <h5 class="card-title">Pending Requests</h5>
                        <h3>
                            <?php
                            $pending = $conn->query("SELECT COUNT(*) as count FROM borrow_requests WHERE status = 'pending'")->fetch_assoc()['count'] ?? 0;
                            echo $pending;
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card shadow mt-4">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-history me-2"></i>
                <h4 class="mb-0">Recent Activity</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>User</th>
                                <th>Item</th>
                                <th>Action</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = $conn->query("SELECT * FROM borrow_requests ORDER BY created_at DESC LIMIT 5");
                            if ($recent && $recent->num_rows > 0) {
                                while ($row = $recent->fetch_assoc()) {
                                    $status_class = '';
                                    switch($row['status']) {
                                        case 'approved': $status_class = 'bg-success'; break;
                                        case 'disapproved': $status_class = 'bg-danger'; break;
                                        default: $status_class = 'bg-warning text-dark'; break;
                                    }
                                    echo "<tr>
                                            <td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>
                                            <td>{$row['user_name']}</td>
                                            <td>{$row['item_name']}</td>
                                            <td>Requested {$row['quantity']} unit(s)</td>
                                            <td><span class='badge {$status_class}'>" . ucfirst($row['status']) . "</span></td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No recent activity</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php elseif ($page === 'inventory'): ?>
        <div class="row g-4">
            <!-- Request Form with Logo -->
            <div class="col-lg-6">
                <div class="card shadow form-card">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-clipboard-list me-2"></i>
                        <h4 class="mb-0">Borrow Request Form</h4>
                    </div>
                    <div class="card-body position-relative">
                        <i class="fas fa-boxes supply-icon-bg"></i>
                        <div class="row">
                            <div class="col-md-4 logo-container">
                                <svg class="supply-logo" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="#3a72c9" d="M501.1 402.7c-8-20.8-31.5-31.5-53.1-25.3l-8.4 2.2V80c0-26.5-21.5-48-48-48H48C21.5 32 0 53.5 0 80v352c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.8l28.3-7.5c21.5-6.1 35.1-29 27.8-50zM400 432c0 8.8-7.2 16-16 16H48c-8.8 0-16-7.2-16-16V80c0-8.8 7.2-16 16-16h336c8.8 0 16 7.2 16 16v352zm-34.9-110.9l-8.3 2.2-75.2 20.4-45.5-72.2 22.7-22.7c3.9-3.9 3.9-10.2 0-14.1l-48.9-49c-3.9-3.9-10.2-3.9-14.1 0l-48.9 49c-3.9 3.9-3.9 10.2 0 14.1l22.7 22.7-72.2 45.5 20.5 75.4 2.2 8.2c2.9 11 1.7 23.5-5.9 31.1l28.4-7.7 80.2-21.8 22.5-22.5c3.9-3.9 3.9-10.2 0-14.1L199 244.2c-3.9-3.9-10.2-3.9-14.1 0l-22.5 22.5-21.4-78.9 106.2-67 67 106.2-78.9-21.4 22.5-22.5c3.9-3.9 3.9-10.2 0-14.1l-48.9-49c-3.9-3.9-10.2-3.9-14.1 0l-49 48.9c-3.9 3.9-3.9 10.2 0 14.1l22.7 22.7-72.2 45.5 20.5 75.4 2.2 8.2c2.8 10.8 1.8 22.5-4.4 30.1l-7.4 2 29.8-8.1 80.4-21.8 22.5-22.5c3.9-3.9 3.9-10.2 0-14.1L168.5 273c-3.9-3.9-10.2-3.9-14.1 0l-22.5 22.5-18.9-69.6 99.7-63 63 99.6-69.6-18.9 22.7-22.7c3.9-3.9 3.9-10.2 0-14.1l-48.9-49c-3.9-3.9-10.2-3.9-14.1 0l-48.9 49c-3.9 3.9-3.9 10.2 0 14.1l22.7 22.7-72.2 45.5 20.4 75.2 2.2 8.3c2.9 10.8 1.6 23.4-6.2 30.9l30.1-8.2 80.4-21.8 22.5-22.5c3.9-3.9 3.9-10.2 0-14.1l-49-48.9c-3.9-3.9-10.2-3.9-14.1 0l-22.5 22.5-19.7-72.6 106.2-67 67 106.2-72.6-19.7 22.5-22.5c3.9-3.9 3.9-10.2 0-14.1l-49-48.9c-3.9-3.9-10.2-3.9-14.1 0l-48.9 49c-3.9 3.9-3.9 10.2 0 14.1l22.7 22.7-72.2 45.5 20.5 75.4 2.2 8.2c3 11 1.7 23.4-5.7 31.1l28.4-7.7 65.5-17.8c-1-1.5-2-3.1-2.8-4.7z"/>
                                </svg>
                            </div>
                            <div class="col-md-8">
                                <form action="supply_user.php" method="POST">
                                    <div class="mb-3">
                                        <label for="user_name" class="form-label">Your Name</label>
                                        <input type="text" name="user_name" id="user_name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="item_name" class="form-label">Item Name</label>
                                        <input type="text" name="item_name" id="item_name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" name="quantity" id="quantity" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                       <label for="user_type" class="form-label">User Type</label>
                                        <select name="user_type" id="user_type" class="form-control" required>
                                            <option value="" disabled selected hidden>Select your type</option>
                                            <option value="Faculty">Faculty</option>
                                            <option value="USG Officer">USG Officer</option>
                                            <option value="SITE Officer">SITE Officer</option>
                                            <option value="PAFE Officer">PAFE Officer</option>
                                            <option value="APROTECHS Officer">APROTECHS Officer</option>
                                            <option value="Student">Student</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="borrow_request" class="btn btn-primary w-100">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplies Table -->
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header d-flex align-items-center">
                        <i class="fas fa-boxes-stacked me-2"></i>
                        <h4 class="mb-0">Available Supplies</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-striped">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>ID</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $supply_list = $conn->query("SELECT * FROM supplies");
                                    if ($supply_list) {
                                        while ($row = $supply_list->fetch_assoc()) {
                                            echo "<tr>
                                                    <td>{$row['id']}</td>
                                                    <td>{$row['item_name']}</td>
                                                    <td>{$row['quantity']}</td>
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='3' class='text-center'>No supplies available</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($page === 'profile'): ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header">
                        <h4><i class="fas fa-user me-2"></i>User Profile</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle" style="font-size: 100px; color: #3a72c9;"></i>
                        </div>
                        <h4>
                            <?php
                            // Get user information from session
                            echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User Name';
                            ?>
                        </h4>
                        <p class="text-muted">
                            <?php
                            echo isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'User Type';
                            ?>
                        </p>
                        <div class="mt-4">
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h4><i class="fas fa-history me-2"></i>Request History</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Get user name from session if available
                                    $user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
                                    
                                    // First check if user_id column exists
                                    $result = null;
                                    if ($user_name != '') {
                                        // Check if we need to query by user_name instead of user_id
                                        $check_columns = $conn->query("SHOW COLUMNS FROM borrow_requests LIKE 'user_id'");
                                        if ($check_columns && $check_columns->num_rows > 0) {
                                            // user_id column exists
                                            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                                            $stmt = $conn->prepare("SELECT * FROM borrow_requests WHERE user_id = ? ORDER BY created_at DESC");
                                            $stmt->bind_param("i", $user_id);
                                        } else {
                                            // user_id column doesn't exist, use user_name instead
                                            $stmt = $conn->prepare("SELECT * FROM borrow_requests WHERE user_name = ? ORDER BY created_at DESC");
                                            $stmt->bind_param("s", $user_name);
                                        }
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                    }
                                    
                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            $status_class = '';
                                            switch($row['status']) {
                                                case 'approved': $status_class = 'bg-success'; break;
                                                case 'disapproved': $status_class = 'bg-danger'; break;
                                                default: $status_class = 'bg-warning text-dark'; break;
                                            }
                                            echo "<tr>
                                                    <td>{$row['item_name']}</td>
                                                    <td>{$row['quantity']}</td>
                                                    <td>" . date('M d, Y', strtotime($row['created_at'])) . "</td>
                                                    <td><span class='badge {$status_class}'>" . ucfirst($row['status']) . "</span></td>
                                                </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center'>No request history found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Edit Profile Modal -->
        <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="update_profile.php" method="POST">
                            <div class="mb-3">
                                <label for="edit_name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="edit_name" name="edit_name" value="<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="edit_email" name="edit_email" value="<?php echo isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="edit_user_type" class="form-label">User Type</label>
                                <select class="form-control" id="edit_user_type" name="edit_user_type">
                                    <option value="Faculty" <?php echo (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'Faculty') ? 'selected' : ''; ?>>Faculty</option>
                                    <option value="USG Officer" <?php echo (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'USG Officer') ? 'selected' : ''; ?>>USG Officer</option>
                                    <option value="SITE Officer" <?php echo (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'SITE Officer') ? 'selected' : ''; ?>>SITE Officer</option>
                                    <option value="PAFE Officer" <?php echo (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'PAFE Officer') ? 'selected' : ''; ?>>PAFE Officer</option>
                                    <option value="APROTECHS Officer" <?php echo (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'APROTECHS Officer') ? 'selected' : ''; ?>>APROTECHS Officer</option>
                                    <option value="Student" <?php echo (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'Student') ? 'selected' : ''; ?>>Student</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Notification Modal -->
<div class="modal fade" id="userNotificationModal" tabindex="-1" aria-labelledby="userNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="userNotificationModalLabel">
                    <i class="fas fa-bell me-2"></i>Approved Requests
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                <?php
                $notifications = $conn->query("SELECT * FROM borrow_requests WHERE (status = 'approved' OR status = 'disapproved') AND user_notified = 0");
                if ($notifications && $notifications->num_rows > 0) {
                    while ($req = $notifications->fetch_assoc()):
                        $icon = $req['status'] === 'approved' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
                        $statusLabel = $req['status'] === 'approved' ? 'Approved' : 'Disapproved';
                ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas <?= $icon ?> me-2"></i>
                            <strong><?= htmlspecialchars($req['item_name']) ?></strong> 
                            <span class="badge bg-secondary rounded-pill ms-2"><?= $statusLabel ?></span>
                        </div>
                        <small class="text-muted"><?= htmlspecialchars($req['updated_at'] ?? date('Y-m-d H:i:s')) ?></small>
                    </li>
                <?php
                    endwhile;
                } else {
                    echo '<li class="list-group-item text-center">No new notifications</li>';
                }
                ?>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y') ?> Supply Management System. All rights reserved.</p>
    </div>
</footer>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Font Awesome -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    
<!-- Notification Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const userModal = document.getElementById('userNotificationModal');
    if (userModal) {
        userModal.addEventListener('shown.bs.modal', function () {
            fetch('mark_user_notifications_read.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const badge = document.getElementById('notification-badge');
                        if (badge) badge.remove();
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                });
        });
    }
});
</script>
</div>

</body>
</html>