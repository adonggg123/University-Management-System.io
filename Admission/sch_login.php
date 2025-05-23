<?php
session_start();
include 'connect.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $fullname = trim($_POST['fullname']);

    if ($student_id !== "" && $fullname !== "") {
        $stmt = $conn->prepare("SELECT * FROM scholarship_applications WHERE student_id = ? AND fullname = ?");
        $stmt->bind_param("ss", $student_id, $fullname);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['student_id'] = $student_id;
            $_SESSION['fullname'] = $fullname;
            header("Location: sch_status.php");
            exit;
        } else {
            $error = "No application found with those credentials.";
        }

        $stmt->close();
    } else {
        $error = "Please enter both Student ID and Fullname.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Scholarship Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
            font-family: Arial, sans-serif;
        }
        .login-box {
            max-width: 450px;
            margin: auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h3 class="text-center mb-4">Student Login</h3>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="student_id" class="form-label">Student ID</label>
            <input type="text" name="student_id" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="fullname" class="form-label">Fullname</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>

</body>
</html>
