<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['student_id']) || !isset($_SESSION['fullname'])) {
    header("Location: sch_dashboard.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$fullname = $_SESSION['fullname'];

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt_delete = $conn->prepare("DELETE FROM scholarship_applications WHERE id = ? AND student_id = ?");
    $stmt_delete->bind_param("is", $delete_id, $student_id);
    $stmt_delete->execute();
    $stmt_delete->close();
}

// Handle contact message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $contact_student_id = $_POST['student_id'];
    $contact_fullname = $_POST['fullname'];
    $contact_email = $_POST['email'];
    $contact_message = $_POST['message'];

    if (!empty($contact_student_id) && !empty($contact_fullname) && !empty($contact_email) && !empty($contact_message)) {
        $stmt_contact = $conn->prepare("INSERT INTO contact_messages (student_id, fullname, email, message, submitted_at) VALUES (?, ?, ?, ?, NOW())");

        if (!$stmt_contact) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt_contact->bind_param("ssss", $contact_student_id, $contact_fullname, $contact_email, $contact_message);
        $stmt_contact->execute();
        $stmt_contact->close();
        $contact_success = true;
    } else {
        $contact_error = "All fields are required.";
    }
}



$stmt = $conn->prepare("SELECT sa.*, s.title FROM scholarship_applications sa JOIN scholarships s ON sa.scholarship_id = s.id WHERE sa.student_id = ? AND sa.fullname = ?");
$stmt->bind_param("ss", $student_id, $fullname);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #160243;
            --accent-color: #2c9c9c;
            --background-color: #f5f7fa;
            --card-bg: #ffffff;
            --text-color: #333333;
            --border-color: #e0e0e0;
            --gradient-bg: linear-gradient(135deg, #160243, #2c9c9c);
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border-color);
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        h2,
        h4 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid var(--border-color);
            padding: 10px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 156, 156, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-primary:hover {
            background-color: #0e0138;
            transform: scale(1.05);
        }

        .btn-accent {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            color: white;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn-accent:hover {
            background-color: #247a7a;
            transform: scale(1.05);
        }

        .btn-danger {
            border-radius: 8px;
            font-size: 14px;
        }

        .table {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .table th {
            background: var(--primary-color);
            color: white;
            padding: 12px;
            font-weight: 500;
        }

        .table td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }

        .badge {
            font-size: 12px;
            padding: 6px 12px;
            border-radius: 12px;
        }

        .alert {
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            text-align: center;
        }

        .icon {
            margin-right: 8px;
        }

        .date-time-container {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            justify-content: center;
            background: #160243;
            border-radius: 12px;
            padding: 20px;
            color: white;
        }

        .date-time-box {
            flex: 1;
            min-width: 220px;
            text-align: center;
            padding: 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .date-time-box h5 {
            font-size: 16px;
            margin-bottom: 12px;
            font-weight: 500;
            color: white;
        }

        .date-time-box .form-control {
            text-align: center;
            font-size: 16px;
            color: var(--text-color);
            background: #ffffff;
            border: none;
            border-radius: 6px;
        }

        .clock {
            font-size: 18px;
            font-weight: 500;
            letter-spacing: 1px;
            color: white;
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .card {
                padding: 16px;
            }

            .btn {
                width: 100%;
            }

            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 8px;
            }

            .date-time-container {
                flex-direction: column;
                gap: 12px;
            }

            .date-time-box {
                min-width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Application Status Section -->
        <div class="card">
            <h4><i class="bi bi-check-square-fill"> </i>APPLICATION STATUS</h4>
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Scholarship Title</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Date Applied</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['applied_at']); ?></td>
                                    <td>
                                        <?php
                                        $status = htmlspecialchars($row['status']);
                                        $badgeClass = match ($status) {
                                            'Approved' => 'badge bg-success',
                                            'Pending' => 'badge bg-warning text-dark',
                                            'Declined' => 'badge bg-danger',
                                            default => 'badge bg-secondary',
                                        };
                                        echo "<span class='$badgeClass'>$status</span>";
                                        ?>
                                    </td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this scholarship?');">
                                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash icon"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle icon"></i>You have not applied to any scholarships yet.
                </div>
            <?php endif; ?>
        </div>

        <!-- Date and Time Section -->
        <div class="card">
            <div class="date-time-container">
                <div class="date-time-box">
                    <h5><i class="fas fa-calendar icon"></i>Today's Date</h5>
                    <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                </div>
                <div class="date-time-box">
                    <h5><i class="fas fa-clock icon"></i>Current Time</h5>
                    <div id="realTimeClock" class="clock"></div>
                </div>
            </div>
        </div>

        <!-- Contact Us Section -->
        <!-- Contact Us Section -->
        <div class="card">
            <h4><i class="fas fa-envelope icon"></i>CONTACT US</h4>

            <?php if (isset($contact_success)): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle icon"></i>Message sent successfully!</div>
            <?php elseif (isset($contact_error)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle icon"></i><?php echo $contact_error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="contactStudentId" class="form-label">Student ID</label>
                    <input type="text" class="form-control" id="contactStudentId" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="contactName" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="contactName" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="contactEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="contactEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="contactMessage" class="form-label">Message</label>
                    <textarea class="form-control" id="contactMessage" name="message" rows="4" required></textarea>
                </div>
                <button type="submit" name="send_message" class="btn btn-accent mt-2">
                    <i class="fas fa-paper-plane icon"></i> Send Message
                </button>
            </form>
        </div>


        <!-- Back to Dashboard -->
        <div class="text-center">
            <a href="sch_dashboard.php" class="btn btn-primary"><i class="fas fa-arrow-left icon"></i>Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('realTimeClock').textContent = timeString;
        }
        setInterval(updateClock, 1000);
        updateClock();

        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(alert => alert.classList.add('fade-out'));
            setTimeout(() => document.querySelectorAll('.alert').forEach(alert => alert.remove()), 500);
        }, 3000);

        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>

</html>

<?php
$stmt->close();
$conn->close();
?>