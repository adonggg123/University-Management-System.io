<?php
include 'connect.php'; // DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);

    if ($stmt->execute()) {
        session_start();
        $_SESSION['user_id'] = $stmt->insert_id;
        header("Location: index.php");
        exit();
    } else {
        echo "Signup failed.";
    }
}
?>

<!-- Include Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Signup Form -->
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4 rounded-4" style="max-width: 400px; width: 100%;">
        <h3 class="text-center mb-4" style="color: rgba(22,2,67,1);"><i class="bi bi-person-plus-fill me-2"></i>Sign Up</h3>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Enter username" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn w-100 text-white" style="background-color: rgba(22,2,67,1);">Sign Up</button>
        </form>
        <div class="text-center mt-3">
            <small>Already have an account? <a href="login.php">Login</a></small>
        </div>
    </div>
</div>

<!-- Bootstrap Icons (for the user icon) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
