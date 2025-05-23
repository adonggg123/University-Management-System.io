<?php
include 'connect.php'; // DB connection
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT * FROM student_applications WHERE full_name = ? AND email = ?");
    $stmt->bind_param("ss", $full_name, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        header("Location: view.php?id=" . $row['id']);
        exit();
    } else {
        $error = "Invalid full name or email.";
    }
}
?>

<!-- Include Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Login Form -->
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow p-4 rounded-4" style="max-width: 400px; width: 100%;">
        <h3 class="text-center mb-4" style="color: rgba(22,2,67,1);">
            <i class="bi bi-box-arrow-in-right me-2"></i>View Status
        </h3>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter email" required>
            </div>
            <button type="submit" class="btn w-100 text-white" style="background-color: rgba(22,2,67,1);">Login</button>
        </form>

        <div class="text-center mt-3">
            <small>Haven’t submitted yet? <a href="index.php">Apply now</a></small>
        </div>
    </div>
</div>

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
