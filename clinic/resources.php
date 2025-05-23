<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Health Resources - Clinic Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styleResources.css" />
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="index.html">
      <img src="Image/clinic.gif" width="30" class="me-2" alt="Logo" />
      Clinic Management
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="student.php">Dashboard</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

  <!-- Page Header -->
  <header class="py-5 bg-light text-center">
    <div class="container">
      <h1 class="fw-bold">Health & Wellness Resources</h1>
      <p class="text-muted">Explore videos on mental health, nutrition, sleep, and exercise to enhance your well-being.</p>
    </div>
  </header>

  <!-- Resource Videos Section -->
  <section class="py-5">
    <div class="container">
      <div class="row g-5">

        <!-- Mental Health -->
        <div class="col-md-6">
          <h5 class="fw-semibold mb-2">🧠 Mental Health Tips for Students</h5>
          <div class="ratio ratio-16x9 mb-3">
            <iframe src="https://www.youtube.com/embed/Z5etb4xuBrk" title="Mental Health Tips for Students" allowfullscreen></iframe>
          </div>
          <p class="text-muted">Discover strategies to manage stress and maintain mental well-being during your studies.</p>
        </div>

        <!-- Nutrition -->
        <div class="col-md-6">
          <h5 class="fw-semibold mb-2">🥗 Healthy Eating on a Budget</h5>
          <div class="ratio ratio-16x9 mb-3">
            <iframe src="https://www.youtube.com/embed/5jc1NGlnYQk" title="Healthy Eating on a Budget" allowfullscreen></iframe>
          </div>
          <p class="text-muted">Learn how to prepare nutritious meals without overspending.</p>
        </div>

        <!-- Sleep -->
        <div class="col-md-6">
          <h5 class="fw-semibold mb-2">😴 Tips for Better Sleep</h5>
          <div class="ratio ratio-16x9 mb-3">
            <iframe src="https://www.youtube.com/embed/fjVA_mmFmxU" title="Tips for Better Sleep" allowfullscreen></iframe>
          </div>
          <p class="text-muted">Understand the importance of sleep and how to improve your sleep habits.</p>
        </div>

        <!-- Exercise -->
        <div class="col-md-6">
          <h5 class="fw-semibold mb-2">🏃‍♀️ 10-Minute Beginner Workout</h5>
          <div class="ratio ratio-16x9 mb-3">
            <iframe src="https://www.youtube.com/embed/zGf-9VVgCDw" title="10-Minute Beginner Workout" allowfullscreen></iframe>
          </div>
          <p class="text-muted">Engage in a quick and easy workout to stay active and energized.</p>
        </div>

      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-dark text-white py-4 mt-5">
    <div class="container text-center">
      <p class="mb-0">&copy; 2025 Clinic Management System. All rights reserved.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
