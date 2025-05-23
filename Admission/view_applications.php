<?php
session_start();
include('connect.php');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo "<div class='alert alert-danger'>Please log in to view your application.</div>";
    exit();
}

$email = $_SESSION['email']; // Get email from session

// Query to fetch the student's application
$query = "SELECT * FROM student_applications WHERE email = '$email'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    echo "<div class='alert alert-warning'>No application found for this student.</div>";
    exit();
}

$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Submitted Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <h2 class="mb-4">Your Submitted Application</h2>

    <div class="card shadow">
        <div class="card-header">
            <h5 class="mb-0"><?= htmlspecialchars($row['full_name']) ?>'s Application</h5>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-6"><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></div>
                <div class="col-md-6"><strong>Contact:</strong> <?= htmlspecialchars($row['contact']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6"><strong>Gender:</strong> <?= htmlspecialchars($row['gender']) ?></div>
                <div class="col-md-6"><strong>Address:</strong> <?= htmlspecialchars($row['address']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6"><strong>Birthdate:</strong> <?= htmlspecialchars($row['birthdate']) ?></div>
                <div class="col-md-6"><strong>Citizenship:</strong> <?= htmlspecialchars($row['citizenship']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6"><strong>Last School:</strong> <?= htmlspecialchars($row['last_school']) ?></div>
                <div class="col-md-6"><strong>School Address:</strong> <?= htmlspecialchars($row['school_address']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6"><strong>Year Graduated:</strong> <?= htmlspecialchars($row['year_graduated']) ?></div>
                <div class="col-md-6"><strong>GPA:</strong> <?= htmlspecialchars($row['gpa']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6"><strong>First Choice:</strong> <?= htmlspecialchars($row['course_choice_1']) ?></div>
                <div class="col-md-6"><strong>Second Choice:</strong> <?= htmlspecialchars($row['course_choice_2']) ?></div>
            </div>
            <div class="row mb-2">
                <div class="col-md-6"><strong>Status:</strong> <?= htmlspecialchars($row['status']) ?></div>
                <div class="col-md-6"><strong>Date Applied:</strong> <?= htmlspecialchars($row['date_applied']) ?></div>
            </div>
            <div class="row">
                <div class="col-md-6"><strong>Time Submitted:</strong> <?= htmlspecialchars($row['time_submitted']) ?></div>
            </div>
        </div>
    </div>

</div>

</body>
</html>
