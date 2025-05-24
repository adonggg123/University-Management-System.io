<?php
$servername = "localhost";
$username = "root";
$password = "quest4inno@server";
$dbname = "university_management_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle appointment actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $appointment_id = $_POST['appointment_id'];
        $action = $_POST['action'];

        if ($action === 'accept') {
            $stmt = $conn->prepare("UPDATE appointments SET decision = 'accepted' WHERE id = ?");
            $stmt->execute([$appointment_id]);
        } elseif ($action === 'decline' && isset($_POST['decline_reason'])) {
            $decline_reason = $_POST['decline_reason'];
            $stmt = $conn->prepare("UPDATE appointments SET decision = 'declined', decline_reason = ? WHERE id = ?");
            $stmt->execute([$decline_reason, $appointment_id]);
        }

        header("Location: admin_appointment.php?filter=" . (isset($_GET['filter']) ? $_GET['filter'] : 'all'));
        exit;
    }

    // Handle prescription download
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_rx'])) {
        $appointment_id = $_POST['appointment_id'];
        $nurse_response = $_POST['nurse_response'];

        $stmt = $conn->prepare("SELECT full_name, student_id, reason, symptoms, preferred_date, email FROM appointments WHERE id = ?");
        $stmt->execute([$appointment_id]);
        $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($appointment) {
            if (!extension_loaded('gd')) {
                die('GD extension is not enabled.');
            }

            $width = 600;
            $height = 800;
            $image = imagecreatetruecolor($width, $height);

            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            $border_color = imagecolorallocate($image, 42, 157, 143);
            $header_bg = imagecolorallocate($image, 240, 250, 248);

            imagefill($image, 0, 0, $white);
            imagerectangle($image, 10, 10, $width - 10, $height - 10, $border_color);
            imagefilledrectangle($image, 10, 10, $width - 10, 80, $header_bg);

            $font_path = __DIR__ . '/fonts/arial.ttf';
            $font_size = 14;
            $small_font_size = 10;

            function wrap_text($text, $max_width, $font_size, $font_path) {
                $words = explode(' ', $text);
                $lines = [];
                $current_line = '';
                if (file_exists($font_path)) {
                    foreach ($words as $word) {
                        $test_line = $current_line ? "$current_line $word" : $word;
                        $bbox = imagettfbbox($font_size, 0, $font_path, $test_line);
                        if ($bbox === false) {
                            $lines[] = $current_line;
                            $current_line = $word;
                            continue;
                        }
                        $text_width = $bbox[2] - $bbox[0];
                        if ($text_width < $max_width) {
                            $current_line = $test_line;
                        } else {
                            $lines[] = $current_line;
                            $current_line = $word;
                        }
                    }
                } else {
                    $char_width = $font_size * 0.6;
                    foreach ($words as $word) {
                        $test_line = $current_line ? "$current_line $word" : $word;
                        $approx_width = strlen($test_line) * $char_width;
                        if ($approx_width < $max_width) {
                            $current_line = $test_line;
                        } else {
                            $lines[] = $current_line;
                            $current_line = $word;
                        }
                    }
                }
                if ($current_line) {
                    $lines[] = $current_line;
                }
                return $lines;
            }

            $header_text = "USTP Clinic Prescription";
            if (file_exists($font_path)) {
                $bbox = imagettfbbox(20, 0, $font_path, $header_text);
                if ($bbox !== false) {
                    $text_width = $bbox[2] - $bbox[0];
                    imagettftext($image, 20, 0, ($width - $text_width) / 2, 50, $black, $font_path, $header_text);
                } else {
                    imagestring($image, 5, 20, 30, $header_text, $black);
                }
            } else {
                imagestring($image, 5, 20, 30, $header_text, $black);
            }

            $y = 100;
            $x = 30;
            $max_text_width = $width - 60;

            $details = [
                "Patient Name: " . $appointment['full_name'],
                "Student ID: " . $appointment['student_id'],
                "Appointment Date: " . date('F j, Y', strtotime($appointment['preferred_date'])),
                "Reason: " . $appointment['reason'],
                "Symptoms: " . ($appointment['symptoms'] ?: 'None')
            ];

            foreach ($details as $detail) {
                $lines = wrap_text($detail, $max_text_width, $font_size, $font_path);
                foreach ($lines as $line) {
                    if (file_exists($font_path)) {
                        imagettftext($image, $font_size, 0, $x, $y, $black, $font_path, $line);
                    } else {
                        imagestring($image, 3, $x, $y - 10, $line, $black);
                    }
                    $y += 25;
                }
                $y += 10;
            }

            $y += 20;
            $prescription_title = "Prescription/Response:";
            if (file_exists($font_path)) {
                imagettftext($image, $font_size, 0, $x, $y, $black, $font_path, $prescription_title);
            } else {
                imagestring($image, 3, $x, $y - 10, $prescription_title, $black);
            }
            $y += 30;

            $lines = wrap_text($nurse_response, $max_text_width, $font_size, $font_path);
            foreach ($lines as $line) {
                if (file_exists($font_path)) {
                    imagettftext($image, $font_size, 0, $x, $y, $black, $font_path, $line);
                } else {
                    imagestring($image, 3, $x, $y - 10, $line, $black);
                }
                $y += 25;
            }

            $footer_text = "Issued by: USTP Clinic | Contact: clinic@ustp.edu.ph";
            if (file_exists($font_path)) {
                $bbox = imagettfbbox($small_font_size, 0, $font_path, $footer_text);
                if ($bbox !== false) {
                    $text_width = $bbox[2] - $bbox[0];
                    imagettftext($image, $small_font_size, 0, ($width - $text_width) / 2, $height - 30, $black, $font_path, $footer_text);
                } else {
                    imagestring($image, 2, 20, $height - 40, $footer_text, $black);
                }
            } else {
                imagestring($image, 2, 20, $height - 40, $footer_text, $black);
            }

            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename=prescription_' . $appointment['student_id'] . '_' . date('Ymd') . '.png');
            imagepng($image);
            imagedestroy($image);
            exit;
        } else {
            $error_message = "Failed to generate prescription: Appointment not found.";
        }
    }

    // Handle filter
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    $query = "SELECT id, full_name, student_id, reason, symptoms, preferred_date, decision, email 
              FROM appointments 
              WHERE status = 'sent'";
    if ($filter === 'accepted') {
        $query .= " AND decision = 'accepted'";
    } elseif ($filter === 'declined') {
        $query .= " AND decision = 'declined'";
    } elseif ($filter === 'pending') {
        $query .= " AND decision IS NULL";
    }
    $query .= " ORDER BY preferred_date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>USTP Clinic - Appointment Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --primary-color: #2A9D8F;
            --secondary-color: #4BA8A0;
            --accent-color: #A3D5D1;
            --light-color: #F1FAEE;
            --dark-color: #1D3A44;
            --success-color: #2A9D8F;
            --warning-color: #E9C46A;
            --danger-color: #E76F51;
            --header-height: 60px;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 70px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #F4F6F9;
            overflow-x: hidden;
            color: #264653;
        }

        .clinic-header {
            height: var(--header-height);
            background-color: white;
            border-bottom: 1px solid #E2E8F0;
            z-index: 1030;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .brand-name {
            display: flex;
            align-items: center;
        }

        .brand-name img {
            width: 30px;
            height: 30px;
            margin-right: 8px;
        }

        .brand-name h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0;
            font-size: 1.1rem;
        }

        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            height: calc(100vh - var(--header-height));
            width: var(--sidebar-width);
            background-color: white;
            transition: all 0.3s ease;
            z-index: 1020;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
            padding-top: 1rem;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar .nav-link {
            color: #4A5568;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin: 0.3rem 0.8rem;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            background-color: var(--light-color);
            color: var(--primary-color);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 0.75rem;
            margin: 0.3rem auto;
            width: 48px;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            margin-top: var(--header-height);
            transition: all 0.3s ease;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            background-color: white;
        }

        .table {
            background-color: white;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background-color: #F8FAFC;
            color: #4A5568;
            font-weight: 500;
            padding: 0.75rem;
            border-bottom: 1px solid #E2E8F0;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #E2E8F0;
        }

        .table tr:hover {
            background-color: #F7FAFC;
        }

        .today-appointment {
            background-color: rgba(42, 157, 143, 0.1);
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        .sidebar-toggle:hover {
            color: var(--secondary-color);
        }

        .modal-content {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var－－-primary-color), var(--secondary-color));
            color: white;
            border-bottom: none;
            padding: 1rem;
        }

        .modal-body {
            padding: 1.25rem;
            background-color: #F8FAFC;
        }

        .modal-body .mb-3 {
            padding: 0.75rem;
            background-color: white;
            border-radius: 6px;
            border: 1px solid #E2E8F0;
        }

        .modal-body .mb-3 strong {
            color: var(--dark-color);
            font-weight: 500;
            min-width: 120px;
            display: inline-block;
        }

        .modal-body .mb-3 p {
            margin: 0;
            word-wrap: break-word;
        }

        .modal-footer {
            border-top: none;
            padding: 1rem;
            background-color: #F8FAFC;
        }

        .btn-primary, .btn-success, .btn-danger, .btn-secondary, .btn-info {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color), #2A7B6E);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color), #A43C3A);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #718096, #4A5568);
        }

        .btn-info {
            background: linear-gradient(135deg, #3B82F6, #2563EB);
        }

        .btn-primary:hover, .btn-success:hover, .btn-danger:hover, .btn-secondary:hover, .btn-info:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #2A7B6E, var(--success-color));
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #A43C3A, var(--danger-color));
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #4A5568, #718096);
        }

        .btn-info:hover {
            background: linear-gradient(135deg, #2563EB, #3B82F6);
        }

        .search-container {
            max-width: 100%;
            margin-bottom: 1rem;
        }

        .search-container input {
            border-radius: 6px;
            border: 1px solid #E2E8F0;
            padding: 0.5rem 1rem;
        }

        .search-container input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.1);
            outline: none;
        }

        .filter-btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
        }

        .filter-btn.active {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 12px;
            font-size: 0.9rem;
            border: 1px solid #E2E8F0;
        }

        .pill-accepted {
            background: linear-gradient(135deg, #E6F7F4, #D4F0EC);
            border-color: var(--success-color);
            color: var(--success-color);
        }

        .pill-declined {
            background: linear-gradient(135deg, #FDEEEE, #F9DADA);
            border-color: var(--danger-color);
            color: var(--danger-color);
        }

        .pill-pending {
            background: linear-gradient(135deg, #E6F0FA, #D6E9F5);
            border-color: var(--secondary-color);
            color: var(--secondary-color);
        }

        .status-pill i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .priority-pill {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.6rem;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-left: 0.5rem;
        }

        .pill-high {
            background: linear-gradient(135deg, #FDEEEE, #F9DADA);
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
        }

        .pill-medium {
            background: linear-gradient(135deg, #FFF7E6, #FFEDD5);
            border: 1px solid var(--warning-color);
            color: var(--warning-color);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        @media (max-width: 767.98px) {
            :root {
                --sidebar-width: 0;
            }

            .sidebar {
                transform: translateX(-100%);
                width: 250px;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .main-content.expanded {
                margin-left: 0;
            }

            .sidebar.collapsed {
                width: 0;
            }

            .mobile-overlay {
                position: fixed;
                top: var(--header-height);
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1019;
                display: none;
            }

            .mobile-overlay.show {
                display: block;
            }

            .table-responsive {
                font-size: 0.9rem;
            }

            .table th, .table td {
                padding: 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 575.98px) {
            .clinic-header {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .modal-body .mb-3 {
                flex-direction: column;
                align-items: flex-start;
            }

            .table th, .table td {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <header class="clinic-header fixed-top d-flex align-items-center px-3">
        <div class="d-flex align-items-center">
            <button id="sidebarToggle" class="sidebar-toggle me-2">
                <i class="bi bi-list"></i>
            </button>
            <div class="brand-name">
                <img src="Image/clinic.gif" alt="USTP Clinic Logo" class="logo" />
                <h5>USTP CLINIC</h5>
            </div>
        </div>
    </header>

    <nav class="sidebar" id="sidebar">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="admin.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="patient.php">
                    <i class="bi bi-people"></i>
                    <span>Patients</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="admin_appointment.php">
                    <i class="bi bi-calendar-check"></i>
                    <span>Appointments</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="inventory.php">
                    <i class="bi bi-box-seam"></i>
                    <span>Inventory</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Reports</span>
                </a>
            </li>
        </ul>
    </nav>

    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <h2 class="fw-bold mb-4">Appointment Management</h2>
            <div class="search-container">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name or ID">
            </div>
            <div class="mb-3 d-flex gap-2 flex-wrap">
                <a href="?filter=all" class="btn btn-outline-primary filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    <i class="bi bi-list me-1"></i> All
                </a>
                <a href="?filter=accepted" class="btn btn-outline-success filter-btn <?php echo $filter === 'accepted' ? 'active' : ''; ?>">
                    <i class="bi bi-check-circle me-1"></i> Accepted
                </a>
                <a href="?filter=declined" class="btn btn-outline-danger filter-btn <?php echo $filter === 'declined' ? 'active' : ''; ?>">
                    <i class="bi bi-x-circle me-1"></i> Declined
                </a>
                <a href="?filter=pending" class="btn btn-outline-warning filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                    <i class="bi bi-hourglass-split me-1"></i> Pending
                </a>
            </div>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (empty($appointments)): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    No appointments found.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="appointmentsTable">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Student ID</th>
                                        <th>Reason</th>
                                        <th>Symptoms</th>
                                        <th>Preferred Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <?php
                                        $is_today = date('Y-m-d', strtotime($appointment['preferred_date'])) === date('Y-m-d');
                                        $reason = strtolower($appointment['reason'] . ' ' . ($appointment['symptoms'] ?? ''));
                                        $priority = '';
                                        if (strpos($reason, 'urgent') !== false || strpos($reason, 'emergency') !== false) {
                                            $priority = 'high';
                                        } elseif (strpos($reason, 'fever') !== false || strpos($reason, 'pain') !== false) {
                                            $priority = 'medium';
                                        }
                                        ?>
                                        <tr class="<?php echo $is_today && $appointment['decision'] === 'accepted' ? 'today-appointment' : ''; ?>" data-priority="<?php echo $priority; ?>">
                                            <td><?php echo htmlspecialchars($appointment['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['student_id']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($appointment['reason']); ?>
                                                <?php if ($priority): ?>
                                                    <span class="priority-pill pill-<?php echo $priority; ?>">
                                                        <?php echo ucfirst($priority); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($appointment['symptoms'] ?: 'None'); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['preferred_date']); ?></td>
                                            <td>
                                                <span class="status-pill <?php echo $appointment['decision'] === 'accepted' ? 'pill-accepted' : ($appointment['decision'] === 'declined' ? 'pill-declined' : 'pill-pending'); ?>">
                                                    <i class="bi <?php echo $appointment['decision'] === 'accepted' ? 'bi-check-circle' : ($appointment['decision'] === 'declined' ? 'bi-x-circle' : 'bi-hourglass-split'); ?>"></i>
                                                    <?php echo htmlspecialchars($appointment['decision'] ? ucfirst($appointment['decision']) : 'Pending'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <?php if ($appointment['decision'] === null): ?>
                                                        <form action="" method="POST" style="display:inline;">
                                                            <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment['id']); ?>">
                                                            <input type="hidden" name="action" value="accept">
                                                            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-circle me-1"></i> Accept</button>
                                                        </form>
                                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#declineModal<?php echo htmlspecialchars($appointment['id']); ?>">
                                                            <i class="bi bi-x-circle me-1"></i> Decline
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-primary view-appointment-btn"
                                                            data-id="<?php echo htmlspecialchars($appointment['id']); ?>"
                                                            data-full-name="<?php echo htmlspecialchars($appointment['full_name']); ?>"
                                                            data-student-id="<?php echo htmlspecialchars($appointment['student_id']); ?>"
                                                            data-reason="<?php echo htmlspecialchars($appointment['reason']); ?>"
                                                            data-symptoms="<?php echo htmlspecialchars($appointment['symptoms'] ?: 'None'); ?>"
                                                            data-preferred-date="<?php echo htmlspecialchars($appointment['preferred_date']); ?>"
                                                            data-status="<?php echo htmlspecialchars($appointment['decision'] ? ucfirst($appointment['decision']) : 'Pending'); ?>"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#appointmentModal">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </button>
                                                    <?php if ($appointment['decision'] === 'accepted'): ?>
                                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#responseModal<?php echo htmlspecialchars($appointment['id']); ?>">
                                                            <i class="bi bi-chat-left-text me-1"></i> Respond
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="appointmentModalLabel">Appointment Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <strong>Full Name</strong>
                                <span id="modal-full-name"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Student ID</strong>
                                <span id="modal-student-id"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Reason</strong>
                                <span id="modal-reason"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Symptoms</strong>
                                <span id="modal-symptoms"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Preferred Date</strong>
                                <span id="modal-preferred-date"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Status</strong>
                                <span id="modal-status"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php foreach ($appointments as $appointment): ?>
                <div class="modal fade" id="declineModal<?php echo htmlspecialchars($appointment['id']); ?>" tabindex="-1" aria-labelledby="declineModalLabel<?php echo htmlspecialchars($appointment['id']); ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="declineModalLabel<?php echo htmlspecialchars($appointment['id']); ?>">Decline Appointment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment['id']); ?>">
                                    <input type="hidden" name="action" value="decline">
                                    <div class="mb-3">
                                        <label for="decline_reason" class="form-label">Reason for Declining</label>
                                        <textarea class="form-control" id="decline_reason" name="decline_reason" rows="4" required placeholder="Please provide a reason for declining this appointment"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Decline</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="responseModal<?php echo htmlspecialchars($appointment['id']); ?>" tabindex="-1" aria-labelledby="responseModalLabel<?php echo htmlspecialchars($appointment['id']); ?>" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="responseModalLabel<?php echo htmlspecialchars($appointment['id']); ?>">Respond to Appointment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($appointment['id']); ?>">
                                    <input type="hidden" name="generate_rx" value="1">
                                    <div class="mb-3">
                                        <label for="nurse_response" class="form-label">Prescription/Response</label>
                                        <textarea class="form-control" id="nurse_response" name="nurse_response" rows="8" required placeholder="Enter your prescription or response to the patient's reason for appointment"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Patient:</strong>
                                        <p><?php echo htmlspecialchars($appointment['full_name']); ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Reason:</strong>
                                        <p><?php echo htmlspecialchars($appointment['reason']); ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Symptoms:</strong>
                                        <p><?php echo htmlspecialchars($appointment['symptoms'] ?: 'None'); ?></p>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-info">Generate Prescription</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const searchInput = document.getElementById('searchInput');
            const appointmentsTable = document.getElementById('appointmentsTable');

            function handleViewportChange() {
                if (window.innerWidth < 768) {
                    sidebar.classList.remove('collapsed');
                    sidebar.classList.remove('show');
                    mainContent.classList.add('expanded');
                } else {
                    mobileOverlay.classList.remove('show');
                    if (sidebar.classList.contains('collapsed')) {
                        mainContent.classList.add('expanded');
                    } else {
                        mainContent.classList.remove('expanded');
                    }
                }
            }

            sidebarToggle.addEventListener('click', function() {
                if (window.innerWidth < 768) {
                    sidebar.classList.toggle('show');
                    mobileOverlay.classList.toggle('show');
                } else {
                    sidebar.classList.toggle('collapsed');
                    mainContent.classList.toggle('expanded');
                }
            });

            mobileOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                mobileOverlay.classList.remove('show');
            });

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = appointmentsTable.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const name = row.cells[0].textContent.toLowerCase();
                    const id = row.cells[1].textContent.toLowerCase();
                    row.style.display = name.includes(searchTerm) || id.includes(searchTerm) ? '' : 'none';
                });
            });

            const headers = appointmentsTable.querySelectorAll('th');
            let sortDirection = {};
            headers.forEach((header, index) => {
                if (index === 0 || index === 1 || index === 4) {
                    header.addEventListener('click', () => {
                        const column = index;
                        sortDirection[column] = !sortDirection[column];
                        const rows = Array.from(appointmentsTable.querySelectorAll('tbody tr'));
                        rows.sort((a, b) => {
                            let aText = a.cells[column].textContent.trim();
                            let bText = b.cells[column].textContent.trim();
                            if (column === 4) {
                                aText = new Date(aText).getTime();
                                bText = new Date(bText).getTime();
                            }
                            return sortDirection[column] ?
                                (aText > bText ? 1 : -1) :
                                (bText > aText ? 1 : -1);
                        });
                        const tbody = appointmentsTable.querySelector('tbody');
                        tbody.innerHTML = '';
                        rows.forEach(row => tbody.appendChild(row));
                    });
                }
            });

            document.querySelectorAll('.view-appointment-btn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('modal-full-name').textContent = this.dataset.fullName;
                    document.getElementById('modal-student-id').textContent = this.dataset.studentId;
                    document.getElementById('modal-reason').textContent = this.dataset.reason;
                    document.getElementById('modal-symptoms').textContent = this.dataset.symptoms;
                    document.getElementById('modal-preferred-date').textContent = this.dataset.preferredDate;
                    document.getElementById('modal-status').textContent = this.dataset.status;
                });
            });

            handleViewportChange();
            window.addEventListener('resize', handleViewportChange);
        });
    </script>
</body>
</html>
<?php $conn = null; ?>