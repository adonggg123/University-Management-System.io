<?php
// Connect to DB
$conn = new mysqli('localhost', 'root', '', 'university_system');
$id = $_GET['id'] ?? 0;

$query = mysqli_query($conn, "SELECT * FROM student_applications WHERE id = $id");
$row = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Application Form Preview</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        .section-title {
            background-color: #160243;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
        }
        table {
            background: white;
        }
        th {
            width: 30%;
        }
    </style>
</head>
<body>
    <div class="container no-print mt-4">
        <div class="bg-white shadow rounded p-3 d-flex justify-content-end mb-3" style="width: 100%;">
            <button id="download-btn" class="btn me-2" style="background-color: rgba(22,2,67,1); color: white;">
                <i class="bi bi-download"></i> Download as PDF
            </button>
            <button onclick="window.close();" class="btn" style="background-color: rgb(255, 177, 22); color: black;">
                <i class="bi bi-x-circle"></i> Close Tab
            </button>
        </div>
    </div>

    <div class="container" id="form-preview">
        

        <!-- Personal Info -->
        <div class="section-title">Personal Information</div>
        <table class="table table-bordered">
            <tr><th>Full Name</th><td><?= htmlspecialchars($row['full_name']) ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($row['email']) ?></td></tr>
            <tr><th>Contact</th><td><?= htmlspecialchars($row['contact']) ?></td></tr>
            <tr><th>Gender</th><td><?= htmlspecialchars($row['gender']) ?></td></tr>
            <tr><th>Address</th><td><?= htmlspecialchars($row['address']) ?></td></tr>
            <tr><th>Birthdate</th><td><?= htmlspecialchars($row['birthdate']) ?></td></tr>
            <tr><th>Citizenship</th><td><?= htmlspecialchars($row['citizenship']) ?></td></tr>
        </table>

        <!-- Educational Background -->
        <div class="section-title">Educational Background</div>
        <table class="table table-bordered">
            <tr><th>Last School Attended</th><td><?= htmlspecialchars($row['last_school']) ?></td></tr>
            <tr><th>School Address</th><td><?= htmlspecialchars($row['school_address']) ?></td></tr>
            <tr><th>Year Graduated</th><td><?= htmlspecialchars($row['year_graduated']) ?></td></tr>
            <tr><th>GPA</th><td><?= htmlspecialchars($row['gpa']) ?></td></tr>
        </table>

        <!-- Course Preferences -->
        <div class="section-title">Course Preferences</div>
        <table class="table table-bordered">
            <tr><th>First Choice</th><td><?= htmlspecialchars($row['course_choice_1']) ?></td></tr>
            <tr><th>Second Choice</th><td><?= htmlspecialchars($row['course_choice_2']) ?></td></tr>
            <tr><th>Status</th><td><?= htmlspecialchars($row['status']) ?></td></tr>
            <tr><th>Date Applied</th><td><?= htmlspecialchars($row['date_applied']) ?></td></tr>
            <tr><th>Time Submitted</th><td><?= htmlspecialchars($row['time_submitted']) ?></td></tr>
        </table>
    </div>

    <script>
        document.getElementById("download-btn").addEventListener("click", function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            const element = document.getElementById("form-preview");

            html2canvas(element).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                const imgProps = doc.getImageProperties(imgData);
                const pdfWidth = doc.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

                doc.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                doc.save("<?= htmlspecialchars($row['full_name']) ?>_application.pdf");
            });
        });
    </script>

</body>
</html>
