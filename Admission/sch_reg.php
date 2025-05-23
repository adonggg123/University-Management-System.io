<?php
include 'connect.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $student_id = $_POST['student_id'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the data into the database
    $stmt = $conn->prepare("INSERT INTO users (student_id, email, password, fullname) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $student_id, $email, $hashed_password, $fullname);
    if ($stmt->execute()) {
        // After successful registration, start the session and set session variables
        session_start();
        $_SESSION['student_id'] = $student_id;
        $_SESSION['fullname'] = $fullname;

        // Redirect to the student dashboard
        header("Location: sch_dashboard.php");
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
</head>
<body>
    <h2>Student Registration</h2>
    <form method="POST">
        <label for="student_id">Student ID:</label>
        <input type="text" name="student_id" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" required><br>

        <label for="fullname">Full Name:</label>
        <input type="text" name="fullname" required><br>

        <button type="submit">Register</button>
    </form>
</body>
</html>
