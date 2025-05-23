<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "clinic_management";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all accepted appointments with patient details
    $stmt = $conn->prepare("
        SELECT a.id, a.unique_key, a.full_name, a.student_id, a.course, a.btled_specialization, 
               a.year_level, a.dob, a.sex, a.mobile_number, a.email, a.preferred_date
        FROM appointments a
        WHERE a.decision = 'accepted'
        ORDER BY a.full_name, a.preferred_date
    ");
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>USTP Clinic - Patient Management</title>
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
            cursor: pointer;
        }

        .table th.sortable:hover {
            background-color: #E2E8F0;
        }

        .table th.sort-asc::after {
            content: ' ▲';
        }

        .table th.sort-desc::after {
            content: ' ▼';
        }

        .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #E2E8F0;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .table tr:hover {
            background-color: #F7FAFC;
        }

        .missing-email {
            background-color: rgba(233, 196, 106, 0.2);
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-bottom: none;
            padding: 1rem;
        }

        .modal-body {
            padding: 1.5rem;
            background-color: #F8FAFC;
        }

        .modal-body .section-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 0.25rem;
        }

        .modal-body .mb-3 {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background-color: white;
            border-radius: 6px;
            border: 1px solid #E2E8F0;
            margin-bottom: 0.75rem;
        }

        .modal-body .mb-3 strong {
            color: var(--dark-color);
            font-weight: 500;
            min-width: 140px;
            flex-shrink: 0;
        }

        .modal-footer {
            border-top: none;
            padding: 1rem;
            background-color: #F8FAFC;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }

        .search-container {
            max-width: 100%;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .search-container input {
            border-radius: 6px;
            border: 1px solid #E2E8F0;
            padding: 0.5rem 2.5rem 0.5rem 1rem;
            width: 100%;
            font-size: 0.9rem;
        }

        .search-container input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(42, 157, 143, 0.1);
            outline: none;
        }

        .search-container .bi-search {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
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
                font-size: 0.85rem;
            }

            .modal-body .mb-3 {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 575.98px) {
            .clinic-header {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .table th, .table td {
                font-size: 0.8rem;
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
                <a class="nav-link active" href="patient.php">
                    <i class="bi bi-people"></i>
                    <span>Patients</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_appointment.php">
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
            <h2 class="fw-bold mb-4">Patient Management</h2>
            <div class="search-container">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by name, ID, course, or email">
                <i class="bi bi-search"></i>
            </div>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php elseif (empty($patients)): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    No accepted appointments found.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="patientsTable">
                                <thead>
                                    <tr>
                                        <th class="sortable" data-sort="full_name">Full Name</th>
                                        <th class="sortable" data-sort="student_id">Student ID</th>
                                        <th>Course</th>
                                        <th>Year Level</th>
                                        <th>Date of Birth</th>
                                        <th>Sex</th>
                                        <th>Mobile Number</th>
                                        <th>Email</th>
                                        <th class="sortable" data-sort="preferred_date">Preferred Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($patients as $patient): ?>
                                        <tr class="<?php echo empty($patient['email']) ? 'missing-email' : ''; ?>">
                                            <td><?php echo htmlspecialchars($patient['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars($patient['student_id']); ?></td>
                                            <td>
                                                <?php 
                                                echo htmlspecialchars($patient['course']); 
                                                if ($patient['course'] === 'BTLED' && $patient['btled_specialization']) {
                                                    echo ' (' . htmlspecialchars($patient['btled_specialization']) . ')';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($patient['year_level']); ?></td>
                                            <td>
                                                <?php 
                                                echo $patient['dob'] ? date('M d, Y', strtotime($patient['dob'])) : 'Not provided'; 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($patient['sex']); ?></td>
                                            <td><?php echo htmlspecialchars($patient['mobile_number']); ?></td>
                                            <td><?php echo htmlspecialchars($patient['email'] ?: 'Not provided'); ?></td>
                                            <td>
                                                <?php 
                                                echo $patient['preferred_date'] ? date('M d, Y', strtotime($patient['preferred_date'])) : 'Not set'; 
                                                ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary view-patient-btn"
                                                        data-id="<?php echo htmlspecialchars($patient['id']); ?>"
                                                        data-unique-key="<?php echo htmlspecialchars($patient['unique_key']); ?>"
                                                        data-full-name="<?php echo htmlspecialchars($patient['full_name']); ?>"
                                                        data-student-id="<?php echo htmlspecialchars($patient['student_id']); ?>"
                                                        data-course="<?php echo htmlspecialchars($patient['course']); ?>"
                                                        data-btled-specialization="<?php echo htmlspecialchars($patient['btled_specialization'] ?: ''); ?>"
                                                        data-year-level="<?php echo htmlspecialchars($patient['year_level']); ?>"
                                                        data-dob="<?php echo htmlspecialchars($patient['dob'] ?: 'Not provided'); ?>"
                                                        data-sex="<?php echo htmlspecialchars($patient['sex']); ?>"
                                                        data-mobile-number="<?php echo htmlspecialchars($patient['mobile_number']); ?>"
                                                        data-email="<?php echo htmlspecialchars($patient['email'] ?: 'Not provided'); ?>"
                                                        data-preferred-date="<?php echo htmlspecialchars($patient['preferred_date'] ?: 'Not set'); ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#patientModal">
                                                    <i class="bi bi-eye me-1"></i> View
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="modal fade" id="patientModal" tabindex="-1" aria-labelledby="patientModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="patientModalLabel">Patient Appointment Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="section-title">Appointment Information</div>
                            <div class="mb-3">
                                <strong>Appointment ID</strong>
                                <span id="modal-id"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Preferred Date</strong>
                                <span id="modal-preferred-date"></span>
                            </div>
                            <div class="section-title">Personal Information</div>
                            <div class="mb-3">
                                <strong>Full Name</strong>
                                <span id="modal-full-name"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Date of Birth</strong>
                                <span id="modal-dob"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Sex</strong>
                                <span id="modal-sex"></span>
                            </div>
                            <div class="section-title">Academic Information</div>
                            <div class="mb-3">
                                <strong>Student ID</strong>
                                <span id="modal-student-id"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Course</strong>
                                <span id="modal-course"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Year Level</strong>
                                <span id="modal-year-level"></span>
                            </div>
                            <div class="section-title">Contact Information</div>
                            <div class="mb-3">
                                <strong>Mobile Number</strong>
                                <span id="modal-mobile-number"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Email</strong>
                                <span id="modal-email"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
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
            const patientsTable = document.getElementById('patientsTable');
            let sortDirection = {};

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
                const rows = patientsTable.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const name = row.cells[0].textContent.toLowerCase();
                    const id = row.cells[1].textContent.toLowerCase();
                    const course = row.cells[2].textContent.toLowerCase();
                    const email = row.cells[7].textContent.toLowerCase();
                    row.style.display = name.includes(searchTerm) || 
                                       id.includes(searchTerm) || 
                                       course.includes(searchTerm) || 
                                       email.includes(searchTerm) ? '' : 'none';
                });
            });

            document.querySelectorAll('.view-patient-btn').forEach(button => {
                button.addEventListener('click', function() {
                    document.getElementById('modal-id').textContent = this.dataset.id;
                    document.getElementById('modal-full-name').textContent = this.dataset.fullName;
                    document.getElementById('modal-student-id').textContent = this.dataset.studentId;
                    document.getElementById('modal-course').textContent = this.dataset.course + 
                        (this.dataset.btledSpecialization ? ` (${this.dataset.btledSpecialization})` : '');
                    document.getElementById('modal-year-level').textContent = this.dataset.yearLevel;
                    document.getElementById('modal-dob').textContent = this.dataset.dob !== 'Not provided' ? 
                        new Date(this.dataset.dob).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Not provided';
                    document.getElementById('modal-sex').textContent = this.dataset.sex;
                    document.getElementById('modal-mobile-number').textContent = this.dataset.mobileNumber;
                    document.getElementById('modal-email').textContent = this.dataset.email;
                    document.getElementById('modal-preferred-date').textContent = this.dataset.preferredDate !== 'Not set' ? 
                        new Date(this.dataset.preferredDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Not set';
                });
            });

            document.querySelectorAll('.sortable').forEach(th => {
                th.addEventListener('click', function() {
                    const column = this.dataset.sort;
                    const isAsc = sortDirection[column] !== 'asc';
                    sortDirection[column] = isAsc ? 'asc' : 'desc';

                    document.querySelectorAll('.sortable').forEach(t => {
                        t.classList.remove('sort-asc', 'sort-desc');
                    });
                    this.classList.add(isAsc ? 'sort-asc' : 'sort-desc');

                    const rows = Array.from(patientsTable.querySelectorAll('tbody tr'));
                    rows.sort((a, b) => {
                        let aValue = a.querySelector(`td:nth-child(${[...this.parentNode.children].indexOf(this) + 1})`).textContent;
                        let bValue = b.querySelector(`td:nth-child(${[...this.parentNode.children].indexOf(this) + 1})`).textContent;

                        if (column === 'preferred_date') {
                            aValue = aValue === 'Not set' ? '' : new Date(aValue);
                            bValue = bValue === 'Not set' ? '' : new Date(bValue);
                        }

                        if (aValue < bValue) return isAsc ? -1 : 1;
                        if (aValue > bValue) return isAsc ? 1 : -1;
                        return 0;
                    });

                    const tbody = patientsTable.querySelector('tbody');
                    tbody.innerHTML = '';
                    rows.forEach(row => tbody.appendChild(row));
                });
            });

            handleViewportChange();
            window.addEventListener('resize', handleViewportChange);
        });
    </script>
</body>
</html>
<?php $conn = null; ?>