<?php
include 'sch_auth.php';
include 'connect.php';

// Get the selected scholarship from the URL
$scholarship_id = $_GET['id'];
$scholarship = $conn->query("SELECT * FROM scholarships WHERE id = $scholarship_id")->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    $document = $_FILES['document']['name'];
    $target = "uploads/" . basename($document);
    move_uploaded_file($_FILES['document']['tmp_name'], $target);

    // Insert application data into the database
    $status = "Pending";
    $stmt = $conn->prepare("INSERT INTO scholarship_applications (student_id, scholarship_id, fullname, email, course, year_level, family_income, document, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssssss", $_SESSION['student_id'], $scholarship_id, $_SESSION['fullname'], $_SESSION['email'], $_POST['course'], $_POST['year_level'], $_POST['family_income'], $document, $status);

    $stmt->execute();

    echo "Application submitted successfully!";
}

?>

<h2>Apply for Scholarship: <?php echo $scholarship['title']; ?></h2>
<form method="post" enctype="multipart/form-data">
    <label for="fullname">Full Name: </label>
    <input type="text" id="fullname" name="fullname" value="<?php echo $_SESSION['fullname']; ?>" readonly><br>

    <label for="course">Course: </label>
    <input type="text" id="course" name="course"><br>

    <label for="year_level">Year Level: </label>
    <input type="text" id="year_level" name="year_level"><br>

    <label for="family_income">Family Income: </label>
    <input type="text" id="family_income" name="family_income"><br>

    <label for="document">Upload Document (e.g., report card, income certificate): </label>
    <input type="file" id="document" name="document"><br><br>

    <button type="submit">Submit Application</button>
</form>