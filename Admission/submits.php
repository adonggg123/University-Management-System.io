<?php
include('connect.php');
ob_start();

// File upload function
function uploadFile($fileInputName) {
    $targetDir = "uploads/";
    $fileName = basename($_FILES[$fileInputName]["name"]);
    $targetFile = $targetDir . uniqid() . "_" . $fileName;

    move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $targetFile);
    return $targetFile;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $full_name = $_POST["full_name"];
    $email = $_POST["email"];
    $contact = $_POST["contact"];
    $address = $_POST["address"];
    $birthdate = $_POST["birthdate"];
    $gender = $_POST["gender"];
    $citizenship = $_POST["citizenship"];
    $last_school = $_POST["last_school"];
    $school_address = $_POST["school_address"];
    $year_graduated = $_POST["year_graduated"];
    $gpa = $_POST["gpa"];
    $course_choice_1 = $_POST["course_choice_1"];
    $course_choice_2 = $_POST["course_choice_2"];

    // Check for duplicates
    $check_sql = "SELECT id FROM student_applications WHERE email = ? OR contact = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $email, $contact);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error_message = urlencode("It seems an application with these credentials has already been submitted. Each student is allowed only one application.");
        header("Location: index.php?error=$error_message");
        exit();
    } else {
        // Upload files
        $transcript = uploadFile("transcript");
        $good_moral = uploadFile("good_moral");
        $birth_certificate = uploadFile("birth_certificate");
        $picture = uploadFile("picture");
        $valid_id = !empty($_FILES["valid_id"]["name"]) ? uploadFile("valid_id") : "";

        // Insert data
        $insert_sql = "INSERT INTO student_applications (
            full_name, email, contact, address, birthdate, gender, citizenship,
            last_school, school_address, year_graduated, gpa,
            course_choice_1, course_choice_2,
            transcript, good_moral, birth_certificate, picture, valid_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param(
            "sssssssssdssssssss",
            $full_name, $email, $contact, $address, $birthdate, $gender, $citizenship,
            $last_school, $school_address, $year_graduated, $gpa,
            $course_choice_1, $course_choice_2,
            $transcript, $good_moral, $birth_certificate, $picture, $valid_id
        );

        if ($insert_stmt->execute()) {
            $new_id = $insert_stmt->insert_id;
            header("Location: view.php?id=" . $new_id . "&new=1");
            exit();
        } else {
            echo "<div style='padding: 20px; background-color: #f8d7da; color: #721c24; border-radius: 5px;'>Error submitting your application. Please try again.</div>";
        }
    }

    $stmt->close();
    $conn->close();
}

ob_end_flush();
?>
