<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_management_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get unique_key from cookie
    $unique_key = isset($_COOKIE['unique_key']) ? $_COOKIE['unique_key'] : '';

    // Fetch appointments
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE unique_key = ? ORDER BY created_at DESC");
    $stmt->execute([$unique_key]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Appointments</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
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
            --dashboard-color: #6b7280;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
            height: auto;
            min-height: min-content;
            flex: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        h1 {
            color: var(--primary-dark);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .dashboard-btn {
            display: block;
            width: 300px;
            max-width: 100%;
            margin: 0 auto 2rem;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            background: linear-gradient(135deg, var(--dashboard-color), #4b5563);
            color: white;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .dashboard-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .appointment-card {
            background-color: var(--background);
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .appointment-card:hover {
            transform: translateY(-4px);
        }

        .appointment-header {
            background-color: var(--primary-light);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .appointment-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .appointment-body {
            padding: 1.5rem;
        }

        .appointment-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .appointment-section:last-of-type {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .appointment-section h3 {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 1rem;
        }

        .appointment-section p {
            font-size: 0.95rem;
            color: var(--text-color);
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .status {
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            display: inline-block;
        }

        .status.pending {
            color: var(--primary-dark);
            background-color: var(--primary-light);
        }

        .status.approved {
            color: var(--success-color);
            background-color: #e6f7ef;
        }

        .status.sent {
            color: var(--primary-dark);
            background-color: var(--primary-light);
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            padding: 1rem 0;
        }

        .actions button {
            width: 300px;
            max-width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            text-align: center;
        }

        .actions .edit-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
        }

        .actions .delete-btn {
            background: linear-gradient(135deg, var(--error-color), #c53030);
            color: white;
        }

        .actions .send-btn {
            background: linear-gradient(135deg, var(--accent-color), #3bb8ac);
            color: white;
        }

        .actions button:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .error {
            color: var(--error-color);
            background-color: #fee2e2;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 500;
            box-shadow: var(--shadow);
        }

        .no-appointments {
            text-align: center;
            color: var(--text-light);
            font-size: 1.1rem;
            background-color: var(--background);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .appointment-body {
                padding: 1rem;
            }

            .appointment-section {
                padding-bottom: 1rem;
                margin-bottom: 1rem;
            }

            .actions {
                flex-direction: column;
                align-items: center;
            }

            .actions button {
                width: 100%;
            }

            .dashboard-btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.75rem;
            }

            .appointment-header h2 {
                font-size: 1.25rem;
            }

            .appointment-section h3 {
                font-size: 1rem;
            }

            .appointment-section p {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>My Appointments</h1>
            <a href="student.php" class="dashboard-btn">Back to Dashboard</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (empty($appointments)): ?>
            <div class="no-appointments">No appointments found.</div>
        <?php else: ?>
            <?php foreach ($appointments as $appointment): ?>
                <div class="appointment-card">
                    <div class="appointment-header">
                        <h2>Appointment #<?php echo htmlspecialchars($appointment['id']); ?></h2>
                    </div>
                    <div class="appointment-body">
                        <div class="appointment-section">
                            <h3>Personal Information</h3>
                            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($appointment['full_name']); ?></p>
                            <p><strong>Student ID:</strong> <?php echo htmlspecialchars($appointment['student_id']); ?></p>
                            <p><strong>Course:</strong> 
                                <?php echo htmlspecialchars($appointment['course']); ?>
                                <?php if ($appointment['course'] === 'BTLED' && $appointment['btled_specialization']): ?>
                                    (<?php echo htmlspecialchars($appointment['btled_specialization']); ?>)
                                <?php endif; ?>
                            </p>
                            <p><strong>Year Level:</strong> <?php echo htmlspecialchars($appointment['year_level']); ?></p>
                            <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($appointment['dob']); ?></p>
                            <p><strong>Sex:</strong> <?php echo htmlspecialchars($appointment['sex']); ?></p>
                        </div>
                        <div class="appointment-section">
                            <h3>Contact Information</h3>
                            <p><strong>Mobile Number:</strong> <?php echo htmlspecialchars($appointment['mobile_number']); ?></p>
                            <?php if ($appointment['email']): ?>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($appointment['email']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="appointment-section">
                            <h3>Appointment Details</h3>
                            <p><strong>Reason:</strong> <?php echo htmlspecialchars($appointment['reason']); ?></p>
                            <?php if ($appointment['medical_conditions']): ?>
                                <p><strong>Medical Conditions:</strong> <?php echo htmlspecialchars($appointment['medical_conditions']); ?></p>
                            <?php endif; ?>
                            <?php if ($appointment['symptoms']): ?>
                                <p><strong>Symptoms:</strong> <?php echo htmlspecialchars($appointment['symptoms']); ?></p>
                            <?php endif; ?>
                            <p><strong>Preferred Date:</strong> <?php echo htmlspecialchars($appointment['preferred_date']); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="status <?php echo htmlspecialchars($appointment['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($appointment['status'])); ?>
                                </span>
                            </p>
                        </div>
                        <div class="appointment-section">
                            <h3>Emergency Contact</h3>
                            <p><strong>Name:</strong> 
                                <?php echo htmlspecialchars($appointment['emergency_name']); ?> 
                                (<?php echo htmlspecialchars($appointment['emergency_relationship']); ?>)
                            </p>
                            <p><strong>Mobile:</strong> <?php echo htmlspecialchars($appointment['emergency_mobile']); ?></p>
                            <?php if ($appointment['address']): ?>
                                <p><strong>Address:</strong> <?php echo htmlspecialchars($appointment['address']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="actions">
                            <button class="edit-btn" onclick="window.location.href='edit_appointment.php?id=<?php echo $appointment['id']; ?>'">Edit</button>
                            <button class="delete-btn" onclick="deleteAppointment(<?php echo $appointment['id']; ?>)">Delete</button>
                            <?php if ($appointment['status'] === 'pending'): ?>
                                <button class="send-btn" onclick="sendToAdmin(<?php echo $appointment['id']; ?>)">Send to Admin</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        async function deleteAppointment(id) {
            if (confirm('Are you sure you want to delete this appointment?')) {
                try {
                    const response = await fetch('delete_appointment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    });
                    const result = await response.json();
                    if (result.status === 'success') {
                        window.location.reload();
                    } else {
                        alert(result.message || 'Error deleting appointment.');
                    }
                } catch (error) {
                    alert('Network error: Unable to delete appointment.');
                }
            }
        }

        async function sendToAdmin(id) {
            try {
                const response = await fetch('send_to_admin.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    window.location.reload();
                } else {
                    alert(result.message || 'Error sending to admin.');
                }
            } catch (error) {
                alert('Network error: Unable to send to admin.');
            }
        }
    </script>
</body>
</html>
<?php $conn = null; ?>