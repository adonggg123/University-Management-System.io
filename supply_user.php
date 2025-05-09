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
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User</title>
        <link rel="stylesheet" href="supply.css?v=<?php echo time(); ?>">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJtJ7tJkPmcV9f9fGvGkUuJkqMX6IQWuK/4hDh3KpWwW9Dptf4U/JpP4OmVZ" crossorigin="anonymous">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0v8FqI1P+8zUuK0CptlX+u0xP1z5DiH1ua7Tgpm2U4B7w+My" crossorigin="anonymous"></script>
</head>
<body>

<?php
$unread_count = $conn->query("SELECT COUNT(*) AS count FROM borrow_requests WHERE status = 'approved' AND user_notified = 0")->fetch_assoc()['count'];
?>
<div class="container-fluid mt-4 p-4 rounded" style="background-color:rgb(216, 216, 216);">
    <div class="notification-wrapper text-end mb-2">
        <a href="#" data-bs-toggle="modal" data-bs-target="#userNotificationModal">
            <i class="fas fa-bell" style="color: black;"></i>
            <?php if ($unread_count > 0): ?>
                <span class="badge" id="notification-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

    <div class="d-flex align-items-center gap-2 mb-3 inventory-header">
        <i class="fas fa-dolly-flatbed fa-2x supply-icon"></i>
        <h2 class="supply supply-font">Inventory Management</h2>
    </div>

    <!-- Request -->
    <div class="container-fluid mt-4">
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="card-title mb-3"><i class="fas fa-clipboard-list me-2"></i>Borrow Request</h4>
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
                            <button type="submit" name="borrow_request" class="btn btn-primary w-100">Request Borrow</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Supplies Table -->
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h4 class="card-title mb-3"><i class="fas fa-boxes-stacked me-2"></i>All Supplies</h4>
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
                                    while ($row = $supply_list->fetch_assoc()) {
                                        echo "<tr>
                                                <td>{$row['id']}</td>
                                                <td>{$row['item_name']}</td>
                                                <td>{$row['quantity']}</td>
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
    </div>

    <div class="modal fade" id="userNotificationModal" tabindex="-1" aria-labelledby="userNotificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userNotificationModalLabel">Approved Requests</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                <?php
                $approved = $conn->query("SELECT * FROM borrow_requests WHERE status = 'approved' AND user_notified = 0");
                while ($req = $approved->fetch_assoc()):
                ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($req['item_name']) ?> approved on <?= htmlspecialchars($req['updated_at']) ?>
                    </li>
                <?php endwhile; ?>
                </ul>
            </div>
            </div>
        </div>
    </div>
</div>

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
                    } else {
                        console.error('Error marking user notifications as read');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                });
        });
    }
});
</script>


<script>
document.getElementById('userNotificationModal').addEventListener('shown.bs.modal', function () {
    fetch('mark_notifications_read.php');
});
</script>

<!-- Bootstrap Bundle JS (includes Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

</body>
</html>