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

    // Fetch notifications for student
    $stmt = $conn->prepare("
        SELECT n.id, n.appointment_id, n.message, n.is_read
        FROM notifications n
        WHERE n.user_type = 'student' AND n.unique_key = ? AND n.is_read = 0
        ORDER BY n.created_at DESC
    ");
    $stmt->execute([$unique_key]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Clinic Management System - Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        /* Base Styles */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fdfdfd;
        }

        html {
            scroll-behavior: smooth;
        }

        /* Navbar */
        .navbar-brand img {
            width: clamp(20px, 5vw, 25px);
            vertical-align: middle;
        }

        .navbar-nav .nav-link {
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
            padding: 0.5rem 0.75rem;
        }

        /* Notification Panel */
        .notification-panel {
            position: fixed;
            top: 0;
            right: -100%;
            width: 100%;
            max-width: 350px;
            height: 100vh;
            background-color: white;
            z-index: 1050;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            padding: 1rem;
            overflow-y: auto;
        }

        .notification-panel.show {
            right: 0;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }

        .notification-list {
            list-style: none;
            padding: 0;
        }

        .notification-list li {
            padding: 0.75rem;
            border-bottom: 1px solid #f1f1f1;
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
        }

        .notification-toggle {
            position: relative;
            color: #333;
            text-decoration: none;
        }

        .notification-toggle .badge {
            position: absolute;
            top: -5px;
            right: -10px;
            font-size: 0.6rem;
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            width: 100%;
            background: url('Image/bg.jpg') no-repeat center center/cover;
            min-height: 85vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            background-size: cover;
            background-position: center center;
        }

        .hero-section .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(34, 35, 36, 0.45);
            z-index: 0;
        }

        .hero-section .container {
            z-index: 1;
            position: relative;
        }

        .hero-section .container .row {
            justify-content: flex-start !important;
        }

        .hero-section h1 {
            font-size: clamp(1.8rem, 5vw, 2.5rem);
            color: white;
        }

        .hero-section p {
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            color: white;
        }

        .btn-book {
            background-color: #afafaf;
            color: white;
            transition: background-color 0.3s ease;
            font-size: clamp(0.85rem, 2.5vw, 0.95rem);
            padding: 0.5rem 1.5rem;
        }

        .btn-book:hover {
            background-color: rgb(46, 49, 49);
        }

        /* Underline Animate */
        .underline-animate {
            position: relative;
            display: inline-block;
            color: #000;
        }

        .underline-animate::after {
            content: '';
            position: absolute;
            width: 0;
            height: 4px;
            left: 0;
            bottom: 0;
            background-color: #fdd835;
            transition: width 0.5s ease-in-out;
        }

        .underline-animate:hover::after {
            width: 100%;
        }

        /* Who We Are Section */
        #who-we-are h2 {
            font-size: clamp(1.8rem, 5vw, 2.2rem);
        }

        #who-we-are p.fs-5 {
            font-size: clamp(0.9rem, 2.5vw, 1rem);
        }

        .spin-icon {
            width: clamp(30px, 8vw, 40px);
        }

        /* Table Styling */
        table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }

        .table-responsive {
            max-width: 90%;
            margin: 0 auto;
        }

        .table {
            width: 100%;
        }

        .table th,
        .table td {
            padding: clamp(6px, 2vw, 8px) clamp(8px, 2vw, 12px);
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
        }

        /* Health Tips */
        .list-group-item {
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            padding: clamp(0.5rem, 2vw, 0.75rem);
        }

        /* Features Section */
        .card {
            border-radius: 1rem;
        }

        .card img {
            width: 100%;
            max-width: clamp(100px, 30vw, 120px);
            height: auto;
        }

        .card h6 {
            font-size: clamp(0.95rem, 2.5vw, 1.05rem);
        }

        .card p {
            font-size: clamp(0.8rem, 2.5vw, 0.85rem);
        }

        /* Footer */
        .footer-section {
            position: relative;
            background: url("Image/footer.jpg") no-repeat center center/cover;
            color: white;
            padding-top: 60px;
            padding-bottom: 40px;
            z-index: 1;
            overflow: hidden;
        }

        .footer-section::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(43, 41, 41, 0.85);
            z-index: 0;
        }

        .footer-section .container,
        .footer-section h5,
        .footer-section p,
        .footer-section a,
        .footer-section i {
            position: relative;
            z-index: 1;
        }

        .footer-section h5 {
            font-size: clamp(1rem, 2.5vw, 1.1rem);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .footer-section p,
        .footer-section li {
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .footer-section a {
            color: #ffffff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: #f0f0f0;
            text-decoration: underline;
        }

        .footer-icon {
            margin-right: 10px;
            color: #ffffff;
            width: 20px;
            display: inline-flex;
            justify-content: center;
        }

        .footer-section .list-unstyled li,
        .footer-section .social-icons a {
            margin-bottom: 0.5rem;
        }

        .footer-section .social-icons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .footer-section .social-icons a {
            font-size: clamp(1rem, 2.5vw, 1.1rem);
        }

        .footer-section .row > div {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        /* Footer Responsiveness */
        @media (max-width: 767.98px) {
            .footer-section .row > div {
                align-items: center;
                text-align: center;
                margin-bottom: 2rem;
            }

            .footer-section .social-icons,
            .footer-section .list-unstyled {
                justify-content: center;
                align-items: center;
            }

            .footer-section .social-icons {
                gap: 12px;
            }

            .footer-section p,
            .footer-section li {
                margin-bottom: 0.5rem;
            }

            .footer-icon {
                margin-right: 10px;
            }
        }

        @media (min-width: 768px) {
            .footer-section .row {
                align-items: flex-start;
            }
        }

        /* Existing Animation (Preserved) */
        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        /* Media Queries for Responsiveness */
        @media (min-width: 768px) {
            .hero-section h1 {
                font-size: clamp(2.5rem, 5vw, 3rem);
            }
        }

        @media (max-width: 767.98px) {
            .navbar-brand {
                font-size: clamp(1rem, 3vw, 1.1rem);
            }

            .notification-panel {
                max-width: 100%;
            }

            .hero-section {
                min-height: 50vh;
                padding: 2rem 1rem;
            }

            .hero-section .col-md-6 {
                padding-left: 15px !important;
            }

            .hero-section .carousel-inner {
                text-align: center;
            }

            .hero-section .row {
                justify-content: center !important;
            }

            .btn-book {
                display: inline-block;
                margin: 0 auto;
            }

            #who-we-are .container {
                padding: 0 15px;
            }

            .table-responsive {
                max-width: 100%;
            }

            .card img {
                max-width: 100px;
            }
        }

        @media (max-width: 575.98px) {
            .table th,
            .table td {
                padding: clamp(4px, 2vw, 6px) clamp(6px, 2vw, 8px);
                font-size: clamp(0.75rem, 2.5vw, 0.85rem);
            }

            .card-body {
                padding: 0.75rem;
            }

            .hero-section h1 {
                font-size: clamp(1.5rem, 5vw, 2rem);
            }

            .hero-section p {
                font-size: clamp(0.8rem, 2.5vw, 0.9rem);
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
                <img src="Image/clinic.gif" alt="Logo" class="me-2" />
                Clinic Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav gap-2 align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="#who-we-are">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_appointmnets.php">My Appointments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link notification-toggle" href="#" id="notificationToggle">
                            <i class="bi bi-bell"></i> Notifications
                            <?php if (!empty($notifications)): ?>
                                <span class="badge bg-danger"><?php echo count($notifications); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Notification Panel -->
    <div id="notificationPanel" class="notification-panel">
        <div class="notification-header">
            <h5>Notifications</h5>
            <button id="closeNotificationPanel" class="btn-close"></button>
        </div>
        <div id="noNotificationsMessage" class="<?php echo empty($notifications) ? '' : 'd-none'; ?>">
            <p>No new notifications.</p>
        </div>
        <?php
            if (!isset($notifications) || !is_array($notifications)) {
                $notifications = [];
            }
            ?>
        <ul class="notification-list" id="notificationList">
            <?php 
            foreach ($notifications as $notification): ?>
                <li><?php echo htmlspecialchars($notification['message']); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Hero Section with Carousel -->
    <section class="hero-section text-white">
        <div class="overlay"></div>
        <div class="container position-relative">
            <div class="row">
                <div class="col-md-6">
                    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <h1 class="fw-bold">Caring for You, Wherever You Are</h1>
                                <p class="mt-3">Our campus clinic brings quality healthcare to your fingertips—safe, reliable, and student-centered.</p>
                            </div>
                            <div class="carousel-item">
                                <h1 class="fw-bold">Your Health, Just a Click Away</h1>
                                <p class="mt-3">Get the care you need from trusted professionals—right from the comfort of your home.</p>
                            </div>
                            <div class="carousel-item">
                                <h1 class="fw-bold">Wellness Starts with Access</h1>
                                <p class="mt-3">Connect with our clinic for hassle-free consultations—anytime, anywhere.</p>
                            </div>
                        </div>
                    </div>
                    <a href="appointment.php" class="btn btn-book mt-4">Book an appointment</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Who We Are Section -->
    <section id="who-we-are" class="py-5 bg-light">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Who We Are</h2>
            <div class="d-flex justify-content-center align-items-center mb-4 gap-3">
                <hr class="flex-grow-1" style="height: 2px; background-color: #000; border: none;" />
                <img src="Image/clinic.gif" alt="Organization Icon" class="spin-icon" />
                <hr class="flex-grow-1" style="height: 2px; background-color: #000; border: none;" />
            </div>
            <p class="text-muted fs-5">
                At the University of Science and Technology of Southern Philippines, we provide a virtual consultation service
                that connects students and employees with trusted healthcare professionals. Our platform is designed to make
                medical support more accessible, allowing the USTP community to get the care they need without leaving their
                homes. We aim to promote wellness through convenience, safety, and care you can rely on.
            </p>
        </div>
        <!-- Side-by-side Health Tips and Weekly Schedule -->
        <div class="container mt-5">
            <div class="row">
                <!-- Health Tips & Resources -->
                <div class="col-md-6 mb-4">
                    <h4 class="fw-bold mb-3 text-center text-md-start">🩺 Health Tips & Resources</h4>
                    <ul class="list-group shadow-sm">
                        <li class="list-group-item">
                            <strong>💧 Stay Hydrated:</strong> Drink at least 8 glasses of water daily to stay energized.
                        </li>
                        <li class="list-group-item">
                            <strong>🧠 Mental Health:</strong> Schedule time to rest and limit screen exposure before bed.
                        </li>
                        <li class="list-group-item">
                            <strong>😴 Get Enough Sleep:</strong> Aim for 7–9 hours of quality sleep to support your immune system and focus.
                        </li>
                        <li class="list-group-item">
                            <strong>🍎 Eat Smart:</strong> Choose whole foods over junk to improve energy and immunity.
                        </li>
                    </ul>
                    <div class="mt-4 text-center text-md-start">
                        <a href="resources.php" class="btn btn-outline-success px-4">More Wellness Tips</a>
                    </div>
                </div>

                <!-- Weekly Consultation Schedule -->
                <div class="col-md-6 mb-4">
                    <h4 class="fw-bold mb-3 text-center text-md-start">Weekly Consultation Schedule</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-center shadow-sm">
                            <thead class="table-success">
                                <tr>
                                    <th>Day</th>
                                    <th>Opening Time</th>
                                    <th>Closing Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Monday</td><td>8:00 AM</td><td>5:00 PM</td></tr>
                                <tr><td>Tuesday</td><td>8:00 AM</td><td>5:00 PM</td></tr>
                                <tr><td>Wednesday</td><td>8:00 AM</td><td>5:00 PM</td></tr>
                                <tr><td>Thursday</td><td>8:00 AM</td><td>5:00 PM</td></tr>
                                <tr><td>Friday</td><td>8:00 AM</td><td>5:00 PM</td></tr>
                                <tr><td>Saturday</td><td colspan="2">Closed</td></tr>
                                <tr><td>Sunday</td><td colspan="2">Closed</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="container mt-5">
            <h4 class="fw-bold mb-4 text-center">What Our Clinic Offers</h4>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <img src="Image/checkup1.jpg" alt="Basic Checkups" class="mb-3" />
                            <h6 class="fw-semibold">Basic Medical Checkups</h6>
                            <p class="text-muted">Get your vitals checked, minor health concerns addressed, and general consultations with ease.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <img src="Image/firstaid.jpg" alt="First Aid" class="mb-3" />
                            <h6 class="fw-semibold">First Aid & Emergency Care</h6>
                            <p class="text-muted">Immediate care for minor injuries and sudden illnesses right on campus.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <img src="Image/referral.jpg" alt="Referral" class="mb-3" />
                            <h6 class="fw-semibold">Referral Services</h6>
                            <p class="text-muted">Need further treatment? We help connect you to nearby hospitals or specialists.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-section">
        <div class="container">
            <div class="row text-center text-md-start">
                <!-- Contact Info -->
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">📬 Contact Information</h5>
                    <p><i class="bi bi-envelope footer-icon"></i><strong>Email:</strong>oroquieta.ustp.edu</p>
                    <p><i class="bi bi-telephone footer-icon"></i><strong>Phone:</strong>0946 045 0924</p>
                    <p><i class="bi bi-geo-alt footer-icon"></i><strong>Location:</strong>Mobod, Oroquieta City, Philippines</p>
                </div>

                <!-- Quick Links & Social -->
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">🔗 Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">About Us</a></li>
                        <li><a href="#" class="text-white">Patient Forms</a></li>
                        <li><a href="#" class="text-white">Testimonials</a></li>
                        <li><a href="#" class="text-white">Contact Us</a></li>
                    </ul>
                    
                </div>

                <!-- Clinic Hours -->
                <div class="col-md-4 mb-4">
                    <h5 class="fw-bold mb-3">🕒 Clinic Hours</h5>
                    <ul class="list-unstyled">
                        <li><strong>Mon & Tue:</strong> 8am – 5pm</li>
                        <li><strong>Wed & Thu:</strong> 8am – 5pm</li>
                        <li><strong>Fri:</strong> 8am – 5pm</li>
                        <li><strong>Sat:</strong> Closed</li>
                        <li><strong>Sun:</strong> Closed</li>
                    </ul>
                </div>
            </div>
            <hr class="border-light my-4" />
            <p class="text-center text-white mb-0">© 2025 Dental Clinic. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationToggle = document.getElementById('notificationToggle');
            const notificationPanel = document.getElementById('notificationPanel');
            const closeNotificationPanel = document.getElementById('closeNotificationPanel');

            notificationToggle.addEventListener('click', function(e) {
                e.preventDefault();
                notificationPanel.classList.toggle('show');
            });

            closeNotificationPanel.addEventListener('click', function() {
                notificationPanel.classList.remove('show');
            });

            document.addEventListener('click', function(e) {
                if (!notificationPanel.contains(e.target) && e.target !== notificationToggle && !notificationToggle.contains(e.target)) {
                    notificationPanel.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
<?php $conn = null; ?>