<?php
$servername = "localhost";
$username = "root";
$password = "quest4inno@server";
$dbname = "university_management_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ?");
    $stmt->execute([$id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        die("Appointment not found.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Update appointment
        $stmt = $conn->prepare("
            UPDATE appointments SET
                full_name = ?, student_id = ?, course = ?, btled_specialization = ?, year_level = ?,
                dob = ?, sex = ?, mobile_number = ?, email = ?, reason = ?, medical_conditions = ?,
                symptoms = ?, preferred_date = ?, emergency_name = ?, emergency_relationship = ?,
                emergency_mobile = ?, address = ?
            WHERE id = ?
        ");
        $stmt->execute([
            filter_var($_POST['fullName'], FILTER_SANITIZE_STRING),
            filter_var($_POST['studentID'], FILTER_SANITIZE_STRING),
            filter_var($_POST['course'], FILTER_SANITIZE_STRING),
            isset($_POST['btledSpecialization']) ? filter_var($_POST['btledSpecialization'], FILTER_SANITIZE_STRING) : null,
            filter_var($_POST['yearLevel'], FILTER_SANITIZE_STRING),
            filter_var($_POST['dob'], FILTER_SANITIZE_STRING),
            filter_var($_POST['sex'], FILTER_SANITIZE_STRING),
            filter_var($_POST['mobileNumber'], FILTER_SANITIZE_STRING),
            filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
            filter_var($_POST['reason'], FILTER_SANITIZE_STRING),
            filter_var($_POST['medicalConditions'], FILTER_SANITIZE_STRING),
            filter_var($_POST['symptoms'], FILTER_SANITIZE_STRING),
            filter_var($_POST['preferredDate'], FILTER_SANITIZE_STRING),
            filter_var($_POST['emergencyName'], FILTER_SANITIZE_STRING),
            filter_var($_POST['emergencyRelationship'], FILTER_SANITIZE_STRING),
            filter_var($_POST['emergencyMobile'], FILTER_SANITIZE_STRING),
            filter_var($_POST['address'], FILTER_SANITIZE_STRING),
            $id
        ]);

        header("Location: my_appointmnets.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Appointment</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reuse styles from original form */
        :root {
            --primary-color: #2c6ecb;
            --primary-light: #eef5ff;
            --primary-dark: #1a4fa0;
            --accent-color: #4fd1c5;
            --text-color: #333333;
            --text-light: #666666;
            --background: #ffffff;
            --border-color: #e2e8f0;
            --success-color: #38a169;
            --error-color: #e53e3e;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        h1 {
            color: var(--primary-dark);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        #appointmentForm {
            background-color: var(--background);
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .section {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .section:last-of-type {
            border-bottom: none;
        }

        h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
        }

        h2::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 20px;
            background-color: var(--primary-color);
            margin-right: 10px;
            border-radius: 4px;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 1rem;
        }

        .input-group {
            flex: 1;
            min-width: 250px;
        }

        .full-width {
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
            font-size: 0.9rem;
        }

        input, select, textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            color: var(--text-color);
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44, 110, 203, 0.15);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            display: block;
            width: 80%;
            padding: 1rem;
            margin: 2rem auto;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-header">
            <h1>Edit Appointment</h1>
        </div>

        <form id="appointmentForm" method="POST">
            <section class="section">
                <h2>Basic Personal Details</h2>
                <div class="row">
                    <div class="input-group">
                        <label for="fullName">Full Name:</label>
                        <input type="text" id="fullName" name="fullName" value="<?php echo htmlspecialchars($appointment['full_name']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="studentID">Student ID Number:</label>
                        <input type="text" id="studentID" name="studentID" value="<?php echo htmlspecialchars($appointment['student_id']); ?>" required>
                    </div>
                </div>
                <div class="input-group">
                    <label for="course">Course/Program:</label>
                    <select id="course" name="course" required onchange="toggleBTLEDSub()">
                        <option value="">Select Course</option>
                        <option value="BSIT" <?php echo $appointment['course'] === 'BSIT' ? 'selected' : ''; ?>>BSIT</option>
                        <option value="BFPT" <?php echo $appointment['course'] === 'BFPT' ? 'selected' : ''; ?>>BFPT</option>
                        <option value="BTLED" <?php echo $appointment['course'] === 'BTLED' ? 'selected' : ''; ?>>BTLED</option>
                    </select>
                </div>
                <div class="input-group" id="btledSubGroup" style="display: <?php echo $appointment['course'] === 'BTLED' ? 'block' : 'none'; ?>;">
                    <label for="btledSpecialization">BTLED Specialization:</label>
                    <select id="btledSpecialization" name="btledSpecialization">
                        <option value="">Select Specialization</option>
                        <option value="H.E" <?php echo $appointment['btled_specialization'] === 'H.E' ? 'selected' : ''; ?>>H.E</option>
                        <option value="I.C.T" <?php echo $appointment['btled_specialization'] === 'I.C.T' ? 'selected' : ''; ?>>I.C.T</option>
                        <option value="I.A" <?php echo $appointment['btled_specialization'] === 'I.A' ? 'selected' : ''; ?>>I.A</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="yearLevel">Year Level:</label>
                    <select id="yearLevel" name="yearLevel" required>
                        <option value="">Select Year</option>
                        <option value="1st Year" <?php echo $appointment['year_level'] === '1st Year' ? 'selected' : ''; ?>>1st Year</option>
                        <option value="2nd Year" <?php echo $appointment['year_level'] === '2nd Year' ? 'selected' : ''; ?>>2nd Year</option>
                        <option value="3rd Year" <?php echo $appointment['year_level'] === '3rd Year' ? 'selected' : ''; ?>>3rd Year</option>
                        <option value="4th Year" <?php echo $appointment['year_level'] === '4th Year' ? 'selected' : ''; ?>>4th Year</option>
                    </select>
                </div>
                <div class="row">
                    <div class="input-group">
                        <label for="dob">Date of Birth:</label>
                        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($appointment['dob']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="sex">Sex/Gender:</label>
                        <select id="sex" name="sex" required>
                            <option value="">Select</option>
                            <option value="Male" <?php echo $appointment['sex'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $appointment['sex'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo $appointment['sex'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
            </section>
            <section class="section">
                <h2>Contact Information</h2>
                <div class="row">
                    <div class="input-group">
                        <label for="mobileNumber">Mobile Number:</label>
                        <input type="tel" id="mobileNumber" name="mobileNumber" value="<?php echo htmlspecialchars($appointment['mobile_number']); ?>" pattern="[0-9]{11}" required>
                    </div>
                    <div class="input-group">
                        <label for="email">Email Address (optional):</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($appointment['email']); ?>">
                    </div>
                </div>
            </section>
            <section class="section">
                <h2>Health-Related Details</h2>
                <div class="row">
                    <div class="input-group">
                        <label for="reason">Reason for Appointment:</label>
                        <input type="text" id="reason" name="reason" value="<?php echo htmlspecialchars($appointment['reason']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="medicalConditions">Existing Medical Conditions:</label>
                        <textarea id="medicalConditions" name="medicalConditions"><?php echo htmlspecialchars($appointment['medical_conditions']); ?></textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="input-group full-width">
                        <label for="symptoms">Current Symptoms:</label>
                        <textarea id="symptoms" name="symptoms"><?php echo htmlspecialchars($appointment['symptoms']); ?></textarea>
                    </div>
                </div>
            </section>
            <section class="section">
                <h2>Appointment Details</h2>
                <div class="row">
                    <div class="input-group full-width">
                        <label for="preferredDate">Preferred Date and Time:</label>
                        <input type="datetime-local" id="preferredDate" name="preferredDate" value="<?php echo str_replace(' ', 'T', htmlspecialchars($appointment['preferred_date'])); ?>" required>
                    </div>
                </div>
            </section>
            <section class="section">
                <h2>Emergency Contact Information</h2>
                <div class="row">
                    <div class="input-group">
                        <label for="emergencyName">Full Name:</label>
                        <input type="text" id="emergencyName" name="emergencyName" value="<?php echo htmlspecialchars($appointment['emergency_name']); ?>" required>
                    </div>
                    <div class="input-group">
                        <label for="emergencyRelationship">Relationship to Student:</label>
                        <input type="text" id="emergencyRelationship" name="emergencyRelationship" value="<?php echo htmlspecialchars($appointment['emergency_relationship']); ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="input-group">
                        <label for="emergencyMobile">Mobile Number:</label>
                        <input type="tel" id="emergencyMobile" name="emergencyMobile" value="<?php echo htmlspecialchars($appointment['emergency_mobile']); ?>" pattern="[0-9]{11}" required>
                    </div>
                    <div class="input-group">
                        <label for="address">Address (optional):</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($appointment['address']); ?>">
                    </div>
                </div>
            </section>
            <button type="submit">Update Appointment</button>
        </form>
    </div>

    <script>
        function toggleBTLEDSub() {
            const courseSelect = document.getElementById('course');
            const btledSubGroup = document.getElementById('btledSubGroup');
            btledSubGroup.style.display = courseSelect.value === 'BTLED' ? 'block' : 'none';
        }
    </script>
</body>
</html>
<?php $conn = null; ?>