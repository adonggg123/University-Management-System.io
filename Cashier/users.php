<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php'); // Redirect to dashboard if not admin
    exit;
}
require_once 'utils/db.php';

// Handle delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id !== $_SESSION['user_id']) { // Prevent self-delete via GET
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            header('Location: users.php?delete_success=1');
            exit;
        } catch (PDOException $e) {
            // Log error or set a message
        }
    }
}

$edit_user_data = null; // Renamed from $edit_user
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $pdo->prepare("SELECT id, username, full_name, role FROM users WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user_data = $stmt->fetch();
}

$message = '';
$message_type = ''; // success or danger

if(isset($_GET['delete_success'])){
    $message = "User deleted successfully.";
    $message_type = 'success';
}

// Handle update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id_update = intval($_POST['user_id']);
    $username_update = trim($_POST['username']);
    $full_name_update = trim($_POST['full_name']);
    $role_update = $_POST['role'];
    $password_update = $_POST['password'];

    if ($username_update && $full_name_update && $role_update) {
        try {
            if (!empty($password_update)) {
                $hash = password_hash($password_update, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, role=?, password_hash=? WHERE id=?");
                $stmt->execute([$username_update, $full_name_update, $role_update, $hash, $user_id_update]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username=?, full_name=?, role=? WHERE id=?");
                $stmt->execute([$username_update, $full_name_update, $role_update, $user_id_update]);
            }
            $message = "User updated successfully.";
            $message_type = 'success';
            // Unset edit GET param to hide edit form and refresh list or re-fetch for edit form
            header('Location: users.php?update_success=1&edit=' . $user_id_update); // Refresh to show updated data in form
            exit;
        } catch (PDOException $e) {
            $message = "Error updating user: " . ($e->errorInfo[1] == 1062 ? "Username already exists." : "Database error.");
            $message_type = 'danger';
        }
    } else {
        $message = "Please fill in all required fields (Username, Full Name, Role).";
        $message_type = 'danger';
    }
    // To keep the edit form open with the error
    if($message_type == 'danger'){
        $edit_user_data = ['id' => $user_id_update, 'username' => $username_update, 'full_name' => $full_name_update, 'role' => $role_update];
    }
}
if(isset($_GET['update_success'])){
    $message = "User updated successfully.";
    $message_type = 'success';
}

// Handle add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username_add = trim($_POST['username']);
    $full_name_add = trim($_POST['full_name']);
    $role_add = $_POST['role'];
    $password_add = $_POST['password'];
    if ($username_add && $full_name_add && $role_add && $password_add) {
        $hash = password_hash($password_add, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username_add, $hash, $full_name_add, $role_add]);
            $message = "User added successfully.";
            $message_type = 'success';
             // Clear POST data by redirecting or unsetting
            header('Location: users.php?add_success=1');
            exit;
        } catch (PDOException $e) {
            $message = "Error adding user: " . ($e->errorInfo[1] == 1062 ? "Username already exists." : "Database error.");
            $message_type = 'danger';
        }
    } else {
        $message = "Please fill in all required fields including password for new user.";
        $message_type = 'danger';
    }
}
if(isset($_GET['add_success'])){
    $message = "User added successfully.";
    $message_type = 'success';
}


// Fetch users for display
$all_users = $pdo->query("SELECT id, username, full_name, role, created_at FROM users ORDER BY created_at DESC")->fetchAll();

$user_roles_available = ['cashier', 'staff', 'admin'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - UMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
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
                    <li class="nav-item"><a class="nav-link" href="students.php">Students</a></li>
                    <?php if (in_array($_SESSION['role'], ['staff', 'admin'])): ?>
                        <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="payments.php">Payments</a></li>
                    <li class="nav-item"><a class="nav-link" href="receipts.php">Receipts</a></li>
                    <li class="nav-item"><a class="nav-link" href="refunds.php">Refunds</a></li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link active" aria-current="page" href="users.php">Users</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4 text-center">User Management</h2>

        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Edit User Form (shown if $edit_user_data is set) -->
        <?php if ($edit_user_data): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h3 class="mb-0">Edit User: <?= htmlspecialchars($edit_user_data['username']) ?></h3>
            </div>
            <div class="card-body">
                <form method="post" action="users.php?edit=<?= $edit_user_data['id'] ?>">
                    <input type="hidden" name="user_id" value="<?= $edit_user_data['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="edit_username" class="form-label">Username*</label>
                            <input type="text" name="username" id="edit_username" class="form-control" value="<?= htmlspecialchars($edit_user_data['username']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_full_name" class="form-label">Full Name*</label>
                            <input type="text" name="full_name" id="edit_full_name" class="form-control" value="<?= htmlspecialchars($edit_user_data['full_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_role" class="form-label">Role*</label>
                            <select name="role" id="edit_role" class="form-select" required>
                                <?php foreach ($user_roles_available as $role_option): ?>
                                <option value="<?= $role_option ?>" <?= ($edit_user_data['role'] ?? '') == $role_option ? 'selected' : '' ?>><?= ucfirst($role_option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" name="password" id="edit_password" class="form-control">
                        </div>
                    </div>
                    <div class="mt-3 d-flex justify-content-end">
                        <a href="users.php" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Add User Form Button and Collapsible Form -->
        <div class="card shadow-sm mb-4 <?= $edit_user_data ? 'd-none' : '' ?>"> <!-- Hide Add form if Edit form is shown -->
             <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Add New User</h3>
                <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#addUserForm" aria-expanded="false" aria-controls="addUserForm">
                    <i class="bi bi-plus-circle me-1"></i> Toggle Form
                </button>
            </div>
            <div class="collapse" id="addUserForm">
                <div class="card-body border-top">
                    <form method="post" action="users.php">
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="add_username" class="form-label">Username*</label>
                                <input type="text" name="username" id="add_username" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_full_name" class="form-label">Full Name*</label>
                                <input type="text" name="full_name" id="add_full_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_role" class="form-label">Role*</label>
                                <select name="role" id="add_role" class="form-select" required>
                                    <?php foreach ($user_roles_available as $role_option): ?>
                                    <option value="<?= $role_option ?>"><?= ucfirst($role_option) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="add_password" class="form-label">Password*</label>
                                <input type="password" name="password" id="add_password" class="form-control" required>
                            </div>
                        </div>
                        <div class="mt-3 d-flex justify-content-end">
                            <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Users List Table -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h3 class="mb-0">Current Users</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Role</th>
                                <th>Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($all_users): ?>
                                <?php foreach ($all_users as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['username']) ?></td>
                                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars(ucfirst($u['role'])) ?></span></td>
                                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($u['created_at']))) ?></td>
                                    <td class="text-center">
                                        <a href="users.php?edit=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit User">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <?php if ($u['id'] !== $_SESSION['user_id']): // Prevent self-delete button ?>
                                            <a href="users.php?delete=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete User" onclick="return confirm('Are you sure you want to delete this user: <?= htmlspecialchars($u['username']) ?>?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center p-3">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
