<?php
require_once __DIR__ . '/db_conn.php';

        // Handle add supply
        if (isset($_POST['add_supply'])) {
            $item_name = $_POST['item_name'];
            $quantity = $_POST['quantity'];
            $stmt = $conn->prepare("INSERT INTO supplies (item_name, quantity) VALUES (?, ?)");
            $stmt->bind_param("si", $item_name, $quantity);
            $stmt->execute();
            $note = "New supply added: $item_name ($quantity pcs)";
            $conn->query("INSERT INTO notifications (message, status) VALUES ('$note', 'unread')");
            header('Location: supply_admin.php');
            exit;
        }

        // Handle purchase in/out
        if (isset($_POST['transaction'])) {
            $supply_id = $_POST['supply_id'];
            $quantity = $_POST['quantity'];
            $action = $_POST['action'];
            $adjustment = ($action === 'purchase_in') ? '+' : '-';
            $conn->query("UPDATE supplies SET quantity = quantity $adjustment $quantity WHERE id = $supply_id");
            $row = $conn->query("SELECT item_name FROM supplies WHERE id = $supply_id")->fetch_assoc();
            $note = ucfirst(str_replace('_', ' ', $action)) . ": {$row['item_name']} ($quantity pcs)";
            $conn->query("INSERT INTO notifications (message, status) VALUES ('$note', 'unread')");
            header('Location: index.php');
            exit;
        }

        // Handle borrow request
        if (isset($_POST['borrow_request'])) {
            $item_name = $_POST['item_name'];
            $quantity = $_POST['quantity'];
            $conn->query("INSERT INTO borrow_requests (item_name, quantity) VALUES ('$item_name', '$quantity')");
            $note = "Borrow request: $item_name ($quantity pcs)";
            $conn->query("INSERT INTO notifications (message, status) VALUES ('$note', 'unread')");
            header('Location: index.php');
            exit;
        }

        if (isset($_POST['delete_supply'])) {
            $delete_id = (int)$_POST['delete_id'];

            $check = $conn->query("SELECT quantity FROM supplies WHERE id = $delete_id LIMIT 1");

            if ($check && $check->num_rows > 0) {
                $row = $check->fetch_assoc();
                $quantity = (int)$row['quantity'];

                if ($quantity >= 0) {
                    $conn->query("DELETE FROM supplies WHERE id = $delete_id");
                    echo "<script>alert('Supply deleted successfully.'); window.location.href='supply_admin.php';</script>";
                } else {
                    echo "<script>alert('Cannot delete. Quantity must be zero. Current: $quantity'); window.location.href='index.php';</script>";
                }
            } else {
                echo "<script>alert('Supply not found.'); window.location.href='index.php';</script>";
            }
        }       

            $borrow_list = $conn->query("SELECT * FROM borrow_requests WHERE status = 'pending' ORDER BY created_at DESC");
            $unread_count = $borrow_list->num_rows;
        
        
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['id'])) {
                $id = intval($_POST['id']);
                $action = $_POST['action'];
                if ($action === 'approve') {
                    $status = 'approved';
                    $user_notified = 0;
                } else if ($action === 'disapprove') {
                    $status = 'disapproved';
                    $user_notified = 1; 
                } else {
                    // Invalid action
                   // header("Location: supply_admin.php");
                   // exit;
                }
                
                // Update the request status
               $sql = "UPDATE borrow_requests SET status = '$status', user_notified = $user_notified, updated_at = NOW() WHERE id = $id";
                
                if ($conn->query($sql)) {
                    echo "<script>alert('Request has been $status successfully!'); window.location.href = 'supply_admin.php';</script>";
                } else {
                    echo "<script>alert('Error updating request: " . $conn->error . "'); window.location.href = 'supply_admin.php';</script>";
                }
            } else {
                // Redirect if accessed directly without proper parameters
               // header("Location: supply_admin.php");
               // exit;
            }
?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>University Management System</title>
        <link rel="stylesheet" href="style1.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="supply.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="supply_user.css?v=<?php echo time(); ?>">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJtJ7tJkPmcV9f9fGvGkUuJkqMX6IQWuK/4hDh3KpWwW9Dptf4U/JpP4OmVZ" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0v8FqI1P+8zUuK0CptlX+u0xP1z5DiH1ua7Tgpm2U4B7w+My" crossorigin="anonymous"></script>

  <style>
    .content { display: none; padding: 20px; }
    .content.active { display: block; }
  </style>

</head>
<body>
  <div class="sidebar">
    <div class="logo">
      <img src="USTP-logo-circle.png" alt="">
    </div>
    <div class="logo1">
      <ul class="menu">
        <li class="active"><a href="#dashboard"><i class='bx bxs-dashboard'></i><span>Dashboard Content</span></a></li>
        <li><a href="#profile"><i class='bx bxs-user'></i><span>Profile</span></a></li>
        <li><a href="#alumni"><i class='bx bxs-school'></i><span>Alumni Office</span></a></li>
        <li><a href="#security"><i class="fas fa-lock"></i><span>Security</span></a></li>
        <li><a href="#admin"><i class="fas fa-graduation-cap"></i><span>Administration & Scholarship</span></a></li>
        <li><a href="#cashier"><i class="fas fa-cash-register"></i><span>Cashier</span></a></li>
        <li><a href="#supply"><i class="fas fa-boxes"></i><span>Supply</span></a></li>
        <li><a href="#library"><i class='bx bx-library'></i><span>Library</span></a></li>
        <li><a href="#clinic"><i class='bx bxs-clinic'></i><span>Clinic</span></a></li>
        <li><a href="#canteen"><i class="fas fa-utensils"></i><span>Canteen</span></a></li>
        <li class="logout"><a href="#logout"><i class='bx bx-log-out'></i><span>Log-out</span></a></li>
      </ul>
    </div>
  </div>

    <div class="main--content">
    <div class="header--wrapper">
        <div class="header--title">
        <h2 id="current-title">University Management System</h2>
        </div>
        <div class="user--info"> <!-- fixed missing '<' -->
        <div class="search--box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search">
        </div>
        <img src="jude.jpg" alt="">
        </div>
    </div>

        <div id="dashboard" class="content active">
            <h3>Dashboard Content</h3>
            <div class="card--container">
                <h3 class="main--title">
                    <span>Today's Data</span>
                </h3>
                <div class="card--wrapper">
                    <div class="payment--card light-blue">
                        <div class="card--header">
                            <div class="amount">
                                <span class="title1">Sample</span>
                                <span class="amount--value">$000.000</span>
                            </div>
                            <i class="fas fa-dollar-sign icon dark-blue"></i>
                        </div>
                        <span class="card--detail">**** **** **** 3484</span>
                    </div>
                    <div class="payment--card light-purple">
                        <div class="card--header">
                            <div class="amount">
                                <span class="title1">Sample</span>
                                <span class="amount--value">$000.000</span>
                            </div>
                            <i class="fas fa-list icon dark-purple"></i>
                        </div>
                        <span class="card--detail">**** **** **** 5542</span>
                    </div>
                    <div class="payment--card light-red">
                        <div class="card--header">
                            <div class="amount">
                                <span class="title1">Sample</span>
                                <span class="amount--value">$000.000</span>
                            </div>
                            <i class="fas fa-users icon dark-red"></i>
                        </div>
                        <span class="card--detail">**** **** **** 8896</span>
                    </div>
                    <div class="payment--card light-green">
                        <div class="card--header">
                            <div class="amount">
                                <span class="title1">Sample</span>
                                <span class="amount--value">$000.000</span>
                            </div>
                            <i class="fas fa-check icon dark-green"></i>
                        </div>
                        <span class="card--detail">**** **** **** 7745</span>
                    </div>
                </div>
            </div>
            <div class="tabular--wrapper">
                <h3 class="main--title">Finance Data</h3>
                <div class="table--container">
                    <table class="sample-table">
                        <thead>
                            <tr>
                                <th>Sample</th>
                                <th>Sample</th>
                                <th>Sample</th>
                                <th>Sample</th>
                                <th>Sample</th>
                            </tr>
                            <tbody>
                                <tr>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                </tr>
                                <tr>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                </tr>
                                <tr>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                    <td>Sample</td>
                                </tr>                                                                                                                                                                                                                                                                                                                   
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7">Total: $000.000</td>
                                </tr>
                            </tfoot>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    <div id="profile" class="content">
      <h3>Profile</h3>
      <p>User profile info here.</p>
    </div>
    <div id="alumni" class="content">
      <h3>Alumni Office</h3>
      <p>Alumni office content.</p>
    </div>
    <div id="security" class="content">
      <h3>Security</h3>
      <p>Security department details.</p>
    </div>
    <div id="admin" class="content">
      <h3>Administration & Scholarship</h3>
      <p>Admin content here.</p>
    </div>
    <div id="cashier" class="content">
      <h3>Cashier</h3>
      <p>Cashier section content.</p>
    </div>

    <div id="supply" class="content">
    <div class="container-fluid py-4">
        <!-- Header Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-gradient p-3 rounded-circle me-3">
                                    <i class="fas fa-dolly-flatbed fa-2x text-white"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold">Inventory Management</h3>
                                    <p class="text-muted mb-0">Manage your supplies efficiently</p>
                                </div>
                            </div>
                            <div class="notification-wrapper">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#borrowRequestModal" class="position-relative">
                                    <div class="p-2 bg-light rounded-circle">
                                        <i class="fas fa-bell fs-4 text-secondary"></i>
                                        <?php if ($unread_count > 0): ?>
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notification-badge">
                                                <?php echo $unread_count; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="row g-4">
            <!-- Add New Supply Form -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow h-100 border-0 card-add-supply">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2 text-success"></i>Add New Supply</h5>
                    </div>
                    <div class="card-body">
                        <form action="supply_admin.php" method="POST" class="custom-form">
                            <div class="mb-3">
                                <label for="item_name" class="form-label">Item Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-box text-primary"></i></span>
                                    <input type="text" name="item_name" id="item_name" class="form-control" placeholder="Enter item name" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-hashtag text-primary"></i></span>
                                    <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Enter quantity" required>
                                </div>
                            </div>
                            <button type="submit" name="add_supply" class="btn btn-success w-100">
                                <i class="fas fa-save me-2"></i>Add Supply
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Purchase In/Out Form -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow h-100 border-0 card-transaction">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0"><i class="fas fa-exchange-alt me-2 text-primary"></i>Purchase In/Out</h5>
                    </div>
                    <div class="card-body">
                        <form class="custom-form" action="supply_admin.php" method="POST">
                            <div class="mb-3">
                                <label for="supply_id" class="form-label">Select Item</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-list text-primary"></i></span>
                                    <select name="supply_id" class="form-select" required>
                                        <?php
                                        $result = $conn->query("SELECT * FROM supplies");
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='{$row['id']}'>{$row['item_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="quantity2" class="form-label">Quantity</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-hashtag text-primary"></i></span>
                                    <input type="number" name="quantity" class="form-control" placeholder="Enter quantity" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="action" class="form-label">Action</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><i class="fas fa-arrows-up-down text-primary"></i></span>
                                    <select name="action" class="form-select" required>
                                        <option value="purchase_in">Purchase In</option>
                                        <option value="purchase_out">Purchase Out</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" name="transaction" class="btn btn-primary w-100">
                                <i class="fas fa-check-circle me-2"></i>Submit Transaction
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Supply Status -->
            <div class="col-lg-4 d-flex">
                <div class="card shadow w-100 border-0 card-status">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="card-title mb-0"><i class="fas fa-chart-pie me-2 text-warning"></i>Supply Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center">
                            <div class="status-icon mx-auto mb-3">
                                <i class="fas fa-boxes-stacked fa-3x text-primary"></i>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <h4 class="fw-bold mb-1">
                                        <?php
                                        $total_items = $conn->query("SELECT COUNT(*) as count FROM supplies")->fetch_assoc()['count'];
                                        echo $total_items;
                                        ?>
                                    </h4>
                                    <p class="text-muted mb-0"><i class="fas fa-box me-1"></i>Total Items</p>
                                </div>
                                <div class="col-6">
                                    <h4 class="fw-bold mb-1">
                                        <?php
                                        $total_quantity = $conn->query("SELECT SUM(quantity) as total FROM supplies")->fetch_assoc()['total'] ?? 0;
                                        echo $total_quantity;
                                        ?>
                                    </h4>
                                    <p class="text-muted mb-0"><i class="fas fa-cubes me-1"></i>Total Quantity</p>
                                </div>
                                <div class="col-6">
                                    <h4 class="fw-bold mb-1">
                                        <?php
                                        $low_stock = $conn->query("SELECT COUNT(*) as count FROM supplies WHERE quantity <= 5")->fetch_assoc()['count'];
                                        echo $low_stock;
                                        ?>
                                        <?php if ($low_stock > 0): ?>
                                            <span class="badge bg-warning ms-1"><i class="fas fa-exclamation-triangle"></i></span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="text-muted mb-0"><i class="fas fa-exclamation-circle me-1"></i>Low Stock</p>
                                </div>
                                <div class="col-6">
                                    <h4 class="fw-bold mb-1">
                                        <?php
                                        $out_of_stock = $conn->query("SELECT COUNT(*) as count FROM supplies WHERE quantity = 0")->fetch_assoc()['count'];
                                        echo $out_of_stock;
                                        ?>
                                        <?php if ($out_of_stock > 0): ?>
                                            <span class="badge bg-danger ms-1"><i class="fas fa-times-circle"></i></span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="text-muted mb-0"><i class="fas fa-ban me-1"></i>Out of Stock</p>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="d-grid gap-2">
                                <a href="supply_admin.php?export=1" class="btn btn-outline-primary">
                                    <i class="fas fa-file-export me-2"></i>Export Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow border-0 card-recent-transactions">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-history me-2 text-success"></i>Recent Transactions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2 text-primary"></i>ID</th>
                                        <th><i class="fas fa-info-circle me-2 text-primary"></i>Transaction Details</th>
                                        <th><i class="fas fa-user me-2 text-primary"></i>User</th>
                                        <th><i class="fas fa-cogs me-2 text-primary"></i>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $transaction_list = $conn->query("SELECT * FROM notifications WHERE message LIKE 'Purchase%' ORDER BY id DESC LIMIT 10");
                                    while ($row = $transaction_list->fetch_assoc()) {
                                        $message = trim($row['message']);
                                        $type = (stripos($message, 'Purchase In') === 0) ? 'Purchase In' : 'Purchase Out';
                                        $icon = ($type === 'Purchase In') ? 'fa-arrow-down text-success' : 'fa-arrow-up text-danger';
                                        $user_name = isset($row['user_name']) ? htmlspecialchars($row['user_name']) : 'Unknown';
                                        error_log("Transaction Message: $message, Type: $type, User: $user_name");
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td>" . htmlspecialchars($message) . "</td>
                                                <td>$user_name</td>
                                                <td><i class='fas $icon me-2'></i>$type</td>
                                            </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Supplies Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow border-0 card-all-supplies">
                    <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0"><i class="fas fa-boxes-stacked me-2 text-info"></i>All Supplies</h5>
                        <div class="input-group" style="max-width: 300px;">
                            <input type="text" class="form-control" id="searchSupplies" placeholder="Search items...">
                            <span class="input-group-text bg-primary">
                                <i class="fas fa-search text-white"></i>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-2 text-primary"></i>ID</th>
                                        <th><i class="fas fa-box me-2 text-primary"></i>Item Name</th>
                                        <th><i class="fas fa-cubes me-2 text-primary"></i>Quantity</th>
                                        <th class="text-center"><i class="fas fa-cogs me-2 text-primary"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $supply_list = $conn->query("SELECT * FROM supplies");
                                    while ($row = $supply_list->fetch_assoc()) {
                                        $quantity = $row['quantity'];
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td><span class='fw-medium'>{$row['item_name']}</span></td>
                                                <td>
                                                    <span class='badge1 bg-" .
                                                        ($quantity > 10 ? 'success' : ($quantity > 5 ? 'warning' : 'danger')) .
                                                        " rounded-pill px-3 py-2'>{$quantity}</span>
                                                    " .
                                                    ($quantity <= 5 ? "<small class='text-danger ms-2'><i class='fas fa-exclamation-triangle'></i> Low stock</small>" : "") . "
                                                </td>
                                                <td class='text-center'>
                                                    <form method='POST' action='supply_admin.php' onsubmit='return confirm(\"Are you sure you want to delete this item?\")' class='d-inline-block'>
                                                        <input type='hidden' name='delete_id' value='{$row['id']}'>
                                                        <button type='submit' name='delete_supply' class='btn btn-danger btn-sm'>
                                                            <i class='fas fa-trash-can'></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Borrow Requests -->
        <div class="modal fade" id="borrowRequestModal" tabindex="-1" aria-labelledby="borrowRequestModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title" id="borrowRequestModalLabel">
                            <i class="fas fa-hand-holding me-2 text-warning"></i>Pending Borrow Requests
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>User Type</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $borrow_list = $conn->query("SELECT * FROM borrow_requests WHERE status = 'pending' ORDER BY id DESC");
                                    while ($row = $borrow_list->fetch_assoc()): ?>
                                        <tr class="fade-request" data-id="<?= $row['id'] ?>">
                                            <td><?= $row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($row['user_type']) ?></span></td>
                                            <td><?= htmlspecialchars($row['item_name']) ?></td>
                                            <td><?= $row['quantity'] ?></td>
                                            <td><small class="text-muted"><?= $row['created_at'] ?></small></td>
                                            <td class="text-center">
                                                <form method="POST" action="supply_admin.php" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                    <button name="action" value="approve" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check me-1"></i> Approve
                                                    </button>
                                                    <button name="action" value="disapprove" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-times me-1"></i> Reject
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Notification handling
        const borrowModal = document.getElementById('borrowRequestModal');
        if (borrowModal) {
            borrowModal.addEventListener('shown.bs.modal', function () {
                console.log('Modal opened, marking notifications as read...');
                fetch('mark_notifications_read.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log(data);
                        if (data.status === 'success') {
                            const badge = document.getElementById('notification-badge');
                            if (badge) badge.remove();
                        } else {
                            console.error('Error in marking notifications as read');
                        }
                    })
                    .catch(error => {
                        console.error('Error marking notifications as read:', error);
                    });
            });
        }

        // Auto-hide requests after 60 seconds
        setTimeout(() => {
            document.querySelectorAll('.fade-request').forEach(row => {
                row.style.transition = 'opacity 1s ease';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 1000);
            });
        }, 60000);

        // Search functionality
        const searchInput = document.getElementById('searchSupplies');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const searchString = this.value.toLowerCase();
                const rows = document.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const itemName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    if (itemName.includes(searchString)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }
    });
    </script>
</body>
</html>
        </div>

    <div id="library" class="content">
      <h3>Library</h3>
      <p>Library resources and tools.</p>
    </div>

    <div id="clinic" class="content">
      <h3>Clinic</h3>
      <p>Clinic services and health info.</p>
    </div>
    
    <div id="canteen" class="content">
            
            <div class="container">
                <div class="nav-bar">
                    
                    <h1>🍔Canteen</h1>
                    <nav>
                        <ul>
                            <li><a href="#home">Home</a></li>
                            <li><a href="#About">About</a></li>
                            <!--<li><a href="#category">Category</a></li>-->
                            <li><a href="#menu">Menu</a></li>
                            <li><a href="/services.html">Service</a></li>
                            <!--<li><a href="/streetFood.html"><img src="img2/cart-44-256.png"></a></li>-->
                            <li><button><a class="btn-contct" href="#contact">Contact Us</a></button> </li>
                        </ul>
                    </nav>
                    
                </div>
                <div class="row">
                    <div class="col">
                        <h1>Foodhub</h1>
                        <span>Make your day great with our special food!<br> <p>Welcome to our canteen where every bite tells a story and every bite sparks a joy </p></span>
                        
                        
                            <div class="btn">
                                <div class="order">
                                    <button><a href="streetFood.php">Order Now<i class="fa fa-cart-plus" aria-hidden="true"></i></a></button>
                                </div>
                            <div class="contact">
                            </div>
                        </div>
                    </div>
                    <div class="img">
                        <img src="img/filipino-street-food-kwek-kwek1.jpg">
                    </div>
                </div>
            </div>
            
        </section>

        <!--<section id="category">
            <div class="header">
                <div class="main">
                    <h1>Category</h1>
                    <div class="cards">
                        <div>
                            <button class="link1"><a href="/streetFood.html">Street Food</a></button>
                        </div>
                        <div>
                            <button class="link2"><a href="/drink.html">Drinks</a></button>
                        </div>
                        <div>
                            <button class="link3"><a href="">Fast Food</a></button>
                        </div>
                    </div>
                </div>
            </div>
        </section>-->

        <section id="About">
            <div class="about"> 
                <div class="about-left">
                    <h1>About Us</h1><hr>
                    <p>Welcome to FoodHub Your Go-To Canteen at USTP Oroquieta!
                        Located right in the heart of USTP Oroquieta, FoodHub is the ultimate hangout spot for students and staff looking for delicious, affordable meals. We serve a wide variety of favorites – from tasty street foods to refreshing drinks, and everything in between!
                        Whether you're in the mood for a quick bite between classes or just chilling with friends, FoodHub is here to satisfy your cravings without breaking the bank. Come for the food, stay for the vibes!
                        </p>
                </div>
                    <div class="about-right">
                        <img src="img2/canteen.jpg">
                    </div>
            </div>
            
        </seection>
        
        <section id="menu">
            <div class="menu-container menu-container2">
                <h1>MENU</h1>
                <ul class="menu-content">
                    <li class="item">
                        <img src="img/burger.png" alt="Burger">
                        <div class="description">
                            <h4>Burger</h4>
                            <p>Burger is a savory sandwich made with a meat patty, fresh veggies, and sauces, served in a soft bun.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    
                    <li class="item">
                        <img src="img/soft.png" alt="Soft drinks">
                        <div class="description">
                            <h4>Soft Drinks</h4>
                            <p>Soft drinks are carbonated, sweetened beverages available in various flavors, perfect for a refreshing treat.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/20347187.png" alt="Kikiam">
                        <div class="description">
                            <h4>Kikiam</h4>
                            <p>Kikiam is a popular Filipino street food made of seasoned meat or fish paste wrapped in bean curd skin, then deep-fried until crispy.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/images (1).png" alt="Fish flat">
                        <div class="description">
                            <h4>Fishflat</h4>
                            <p>Fish flat is a Filipino street food made of seasoned fish paste shaped flat, then deep-fried until golden and crispy.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/nuggets.png" alt="Nuggets">
                        <div class="description">
                            <h4>Nuggets</h4>
                            <p>Nuggets are bite-sized pieces of seasoned chicken, coated in breading and deep-fried, popular as a tasty and easy street food snack.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/shomai.png" alt="Shomai">
                        <div class="description">
                            <h4>Fried / Steam Siomai</h4>
                            <p>Fried/Steam Siomai is a Filipino street food of tasty pork or shrimp dumplings, either steamed or deep-fried, and served with soy sauce or chili sauce.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/tempura.png" alt="tempura">
                        <div class="description">
                            <h4>Tempura</h4>
                            <p>Tempura is a popular street food made of fish, shrimp, or vegetables coated in light batter and deep-fried until crispy and golden.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/squid-roll.png" alt="Squid roll">
                        <div class="description">
                            <h4>Squidroll</h4>
                            <p>Squid roll is a street food snack made of seasoned squid paste rolled in batter and deep-fried until crispy and golden.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item"> 
                        <img src="img/cheese-stick.png" alt="Cheese stick">
                        <div class="description">
                            <h4>cheese Stick</h4>
                            <p>Cheese stick is a street food snack made of cheese wrapped in lumpia wrapper, then deep-fried until crispy and golden.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        
                            <img src="img/squid-ball.png" alt="Squid ball">
                        <div class="description">
                            <h4>Squid Ball</h4>
                            <p>Squid balls are bite-sized, seasoned squid meat balls, battered and deep-fried until crispy, often served with dipping sauces.</p>
                            <span>&#8369; 10</span>
                        </div>
                        
                    </li>
                    <li class="item">
                        <img src="img/kwek-kwek.png" alt="Kwek-kwek">
                        <div class="description">
                            <h4>Kwek-Kwek</h4>
                            <p>Kwek-kwek is a Filipino street food made of quail eggs coated in orange batter and deep-fried, usually served with vinegar or spicy sauce.</p>
                            <span>&#8369; 10</span>
                        </div>
                    </li>
                    <li class="item">
                        <img src="img/lemon.png" alt="Lemonade">
                        <div class="description">
                            <h4>Blue Lemonade</h4>
                            <p>Blue lemonade is a tangy, refreshing drink with a vibrant blue twist.</p>
                            <span>&#8369; 10</span>
                        </div>
                    <li class="item">
                        <img src="img/cocumber.png" alt="Cocumber juice">
                        <div class="description">
                            <h4>Cocumber Juice</h4>
                            <p>Cucumber juice is a cool, refreshing drink made from fresh cucumber, offering a light and hydrating flavor.</p>
                            <span>&#8369; 10</span>
                        </div>
                        <li class="item">
                            <img src="img/TASTY-MEATY-HOTDOG-WITH-CHEESE-JUMBO-500G-2.png" alt="Jumbo hotdog">
                            <div class="description">
                                <h4>Hotdog Jumbo</h4>
                                <p>Hotdog Jumbo is a large, skewered hotdog coated in batter and deep-fried for a crispy, tasty snack.</p>
                                <span>&#8369; 10</span>
                            </div>
                        </li>
                    
                    <li class="item">
                        <img src="img/hotdog-balls.png" alt="Hotdog balls">
                        <div class="description">
                            <h4>Hotdog balls</h4>
                            <p>Hotdog balls are bite-sized hotdogs coated in batter and deep-fried until crispy, perfect for snacks or dipping.</p>
                            <span>&#8369; 10</span>
                            
                        </div>
                    </li>
                </ul>
            </div>
        </section>
   <section id="contact">
       <div class="box-container">
           <form action="https://api.web3forms.com/submit" method="POST" class="contact-left" >
               
               
                   <div class="title"><h1>Contact Us</h1><hr></div>
                   <input type="hidden" name="access_key" value="767d8709-aba6-424a-ab34-009f6b75ec72">
                   <input type="text" name="name" placeholder="Your name" required class="input"><br>
                   <input type="email" name="email" placeholder="Your email" required class="input"><br>
                   <textarea name="message"placeholder="Your message" required class="input"></textarea><br>
                   <button type="submit">Send Message<i class="fa fa-arrow-circle-right" aria-hidden="true"></i></button>
              
               
           </form>
           <div class="contact-right">
               <img src="img/74a6b3f3514e6b37aa1baf5b8d42c493.png">
           </div>
       </div>
   </section>
   <footer>
       <div class="heading">   
           <div class="text">
               <p>USTP Mobod, Oroquieta City</p>
               <span>Philippines</span>
               <ul>
                   <li>
                       <span><i class="fa fa-facebook-official" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-instagram" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-google" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-instagram" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-facebook-official" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-twitter" aria-hidden="true"></i></span>
                   </li>
                   <li>
                       <span><i class="fa fa-envelope-o" aria-hidden="true"></i>
                       </span>
                       
                   </li>
               </ul>
               <p>Copyright &copy; All Right Reserved</p>
               <a href="#home">Bact to top</a>
           </div>
       </div>
   </footer>
   <script src="app.js"></script>
    </div>
    <div id="logout" class="content">
      <h3>Logged Out</h3>
      <p>You have logged out successfully.</p>
    </div>
  </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const links = document.querySelectorAll('.menu a');
        const contents = document.querySelectorAll('.content');

        links.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const target = link.getAttribute('href').substring(1);

            contents.forEach(c => c.classList.remove('active'));
            document.getElementById(target).classList.add('active');
        });
        });
    });
    </script>


</body>
</html>
