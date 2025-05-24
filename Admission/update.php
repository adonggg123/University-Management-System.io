<?php
$servername = "localhost";
$username = "root";
$password = "quest4inno@server";
$dbname = "university_management_system";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if (!$id) {
    die("Invalid ID.");
}

// Admission status and auto-update exam status if approved
if (isset($_POST['action'])) {
    $newStatus = $_POST['action'];
    
    // Get student ID before updating the status 
    $stmt = $conn->prepare("SELECT id, email FROM student_applications WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentData = $result->fetch_assoc();
    $studentId = $studentData['id'];  // Use student_id for the notification
    $studentEmail = $studentData['email'];
    $stmt->close();
    
    // Update admission status
    $stmt = $conn->prepare("UPDATE student_applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $id);
    $stmt->execute();
    $stmt->close();

    // Create notification message for the student
    if ($newStatus === "approved") {
        $message = "Your application has been approved. The exam is now scheduled.";
        $examStatus = "scheduled";

        // Update exam status if approved
        $stmt = $conn->prepare("UPDATE student_applications SET exam_status = ? WHERE id = ?");
        $stmt->bind_param("si", $examStatus, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $message = "Your application has been declined.";
    }

    // Insert a notification message into the notifications table using student_id
    $stmt = $conn->prepare("INSERT INTO admi_notifications (student_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $studentId, $message);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['exam_action'])) {
    $examStatus = $_POST['exam_action'];

    $stmt = $conn->prepare("SELECT id, email FROM student_applications WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentData = $result->fetch_assoc();
    $studentId = $studentData['id'];
    $studentEmail = $studentData['email'];
    $stmt->close();

    $stmt = $conn->prepare("UPDATE student_applications SET exam_status = ? WHERE id = ?");
    $stmt->bind_param("si", $examStatus, $id);
    $stmt->execute();
    $stmt->close();

    if ($examStatus === "passed") {
        // Generate random 8-character password
        $generatedPassword = bin2hex(random_bytes(4));

        // Store the password in the student_portal_accounts table
        $stmt = $conn->prepare("INSERT INTO student_portal_accounts (student_id, portal_password) VALUES (?, ?)");
        $stmt->bind_param("is", $studentId, $generatedPassword);
        $stmt->execute();
        $stmt->close();

        $message = "Congratulations! You have passed the entrance exam. You can now proceed to enrollment. Your university portal password is: $generatedPassword";
    } else {
        $message = "Sorry, you are not qualified. Please visit the office for more inquiries";
    }

    $stmt = $conn->prepare("INSERT INTO admi_notifications (student_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $studentId, $message);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

// Redirect back to the view page
header("Location: manage_admissions.php?id=$id");
exit;
?>
