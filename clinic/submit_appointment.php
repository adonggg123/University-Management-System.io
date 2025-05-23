<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Generate or retrieve unique_key
    $unique_key = isset($_COOKIE['unique_key']) ? $_COOKIE['unique_key'] : bin2hex(random_bytes(16));

    // Sanitize and collect form data
    $full_name = filter_var($_POST['fullName'], FILTER_SANITIZE_STRING);
    $student_id = filter_var($_POST['studentID'], FILTER_SANITIZE_STRING);
    $course = filter_var($_POST['course'], FILTER_SANITIZE_STRING);
    $btled_specialization = isset($_POST['btledSpecialization']) ? filter_var($_POST['btledSpecialization'], FILTER_SANITIZE_STRING) : null;
    $year_level = filter_var($_POST['yearLevel'], FILTER_SANITIZE_STRING);
    $dob = filter_var($_POST['dob'], FILTER_SANITIZE_STRING);
    $sex = filter_var($_POST['sex'], FILTER_SANITIZE_STRING);
    $mobile_number = filter_var($_POST['mobileNumber'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $reason = filter_var($_POST['reason'], FILTER_SANITIZE_STRING);
    $medical_conditions = filter_var($_POST['medicalConditions'], FILTER_SANITIZE_STRING);
    $symptoms = filter_var($_POST['symptoms'], FILTER_SANITIZE_STRING);
    $preferred_date = filter_var($_POST['preferredDate'], FILTER_SANITIZE_STRING);
    $emergency_name = filter_var($_POST['emergencyName'], FILTER_SANITIZE_STRING);
    $emergency_relationship = filter_var($_POST['emergencyRelationship'], FILTER_SANITIZE_STRING);
    $emergency_mobile = filter_var($_POST['emergencyMobile'], FILTER_SANITIZE_STRING);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);

    // Prepare and execute SQL
    $stmt = $conn->prepare("
        INSERT INTO appointments (
            unique_key, full_name, student_id, course, btled_specialization, year_level, dob, sex,
            mobile_number, email, reason, medical_conditions, symptoms, preferred_date,
            emergency_name, emergency_relationship, emergency_mobile, address
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $unique_key, $full_name, $student_id, $course, $btled_specialization, $year_level, $dob, $sex,
        $mobile_number, $email, $reason, $medical_conditions, $symptoms, $preferred_date,
        $emergency_name, $emergency_relationship, $emergency_mobile, $address
    ]);

    echo json_encode(['status' => 'success', 'unique_key' => $unique_key]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>