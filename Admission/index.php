<?php
include('connect.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>University Admission</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">


  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }

    header,
    footer {
      background-color: rgba(22, 2, 67);
      color: #fff;
      padding: 20px;
      text-align: center;
    }

    h1,
    h2 {
      margin-bottom: 20px;
    }

    .info-card th {
      font-weight: 600;
    }

    .info-card td,
    .info-card th {
      padding: 8px 12px;
    }

    .form-section {
      background-color: #fff;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      margin-top: 40px;
    }

    .form-section h3 {
      margin-top: 30px;
    }

    .back {
      height: auto;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #f8f9fa;
      box-shadow: 0px -30px 90px 95px #f8f9fa;
    }

    .bgi {
      height: 90vh;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }

    .bgi img {
      height: 1100px;
      margin-bottom: 40px;
      z-index: -1;
    }
  </style>
</head>

<body>
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
      <?= htmlspecialchars($_GET['error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <header class="d-flex justify-content-between align-items-center p-3" style="background-color: rgba(22, 2, 67);">
  <div class="d-flex align-items-center">
    <img src="ustp_logo.jpg" alt="Logo" class="me-3" style="width: 40px; border-radius: 50%;">
    <h1 class="mb-0 text-white" style="font-size: 26px;">UNIVERSITY ADMISSION</h1>
  </div>
  <div class="d-flex gap-2">
    <a href="../UMS.php" class="btn btn-light"><i class="bi bi-box-arrow-in-right me-2"></i>Return</a>
    <a href="login.php" class="btn btn-light"><i class="bi bi-eye me-2"></i>View Status</a>
  </div>
</header>


  <section class="bgi">
    <img src="bg2.jpg" alt="">
  </section>

  <section class="back">
    <div class="container my-5">
      <div class="row g-4">

        <!-- Admission Guidelines -->
        <div class="col-md-6 col-lg-3">
          <div class="card p-3 shadow-sm rounded-4 h-100">
            <div class="card-body">
              <h5 class="fw-semibold mb-3" style="color: rgb(0, 0, 0)"><i class="bi bi-person-lines-fill me-2"></i>Admission Guidelines</h5>
              <p class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Ensure all uploaded files are in JPEG, PNG, or PDF format</p>
              <p class="mb-0"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Double registration is prohibited and will invalidate the application.</p>
              <p class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Senior high graduates, transferees</p>
              <p class="mb-0"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Apply → Submit Docs → Exam → Results</p>
            </div>
          </div>
        </div>

        <!-- Required Documents -->
        <div class="col-md-6 col-lg-3">
          <div class="card p-3 shadow-sm rounded-4 h-100">
            <div class="card-body">
              <h5 class="fw-semibold mb-3" style="color: rgb(0, 0, 0)"><i class="bi bi-folder-check me-2"></i>Required Documents</h5>
              <ul class="list-unstyled">
                <li class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Form 138 / TOR for transferee</li>
                <li class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>PSA Birth Certificate</li>
                <li class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>School ID</li>
                <li class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>2x2 ID Picture</li>
                <li><i class="bi bi-caret-right-fill me-2 text-muted"></i>Parent's Income Tax (ITR) or affidavit of Low Income</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Important Dates -->
        <div class="col-md-6 col-lg-3">
          <div class="card p-3 shadow-sm rounded-4 h-100">
            <div class="card-body">
              <h5 class="fw-semibold mb-3" style="color: rgb(0, 0, 0)"><i class="bi bi-calendar-event me-2"></i>Important Dates</h5>
              <p class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Opens: February 28, 2025</p>
              <p class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Deadline: March 28, 2025</p>
              <p class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Entrance Exam: April 24, 2025</p>
            </div>
          </div>
        </div>

        <!-- Available Courses -->
        <div class="col-md-6 col-lg-3">
          <div class="card p-3 shadow-sm rounded-4 h-100">
            <div class="card-body">
              <h5 class="fw-semibold mb-3" style="color: rgb(0, 0, 0)"><i class="bi bi-mortarboard-fill me-2"></i>Available Courses</h5>
              <ul class="list-unstyled">
                <li class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Bachelor of Science in Information Technology</li>
                <li class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Bachelor of Technology & Livelihood Education - Major in Home Economics</li>
                <li class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Bachelor of Technology & Livelihood Education - Major in Industrial Arts</li>
                <li class="mb-2"><i class="bi bi-caret-right-fill me-2 text-muted"></i>Bachelor of Technology & Livelihood Education - Major in Information and Communation Technology</li>
                <li><i class="bi bi-caret-right-fill me-2 text-muted"></i>Bachelor in Food Processing and Technology</li>
              </ul>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>


  <div class="container my-5">
    <div class="card shadow-lg">
      <div class="card-header text-white" style="background-color: rgba(22,2,67);">
        <h3 class="mb-0">APPLICATION FORM</h3>
      </div>
      <div class="card-body">
        <form action="submits.php" method="POST" enctype="multipart/form-data">
          <!-- Personal Info -->
          <h5 class="mb-3 mt-3" style="color: #333; font-weight: bold;">Personal Information</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" class="form-control" name="full_name" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email Address</label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Number</label>
              <input type="text" class="form-control" name="contact" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Address</label>
              <input type="text" class="form-control" name="address" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Birthdate</label>
              <input type="date" class="form-control" name="birthdate" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Gender</label>
              <select class="form-select" name="gender" required>
                <option value="" disabled selected>Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Citizenship</label>
              <input type="text" class="form-control" name="citizenship" required>
            </div>
          </div>

          <!-- Education -->
          <h5 class="mb-3 mt-5" style="color: #333; font-weight: bold;">Educational Background</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Last School Attended</label>
              <input type="text" class="form-control" name="last_school" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">School Address</label>
              <input type="text" class="form-control" name="school_address" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Year Graduated</label>
              <input type="text" class="form-control" name="year_graduated" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">General Average / GWA</label>
              <input type="text" class="form-control" name="gpa" required>
            </div>
          </div>

          <!-- Course Choices -->
          <h5 class="mb-3 mt-5" style="color: #333; font-weight: bold;">Course Preferences</h5>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">First choice of Course</label>
              <select class="form-select" name="course_choice_1" required>
                <option value="" disabled selected>Select Course</option>
                <option value="BSIT">Bachelor of Science in Information Technology</option>
                <option value="BTLED-HE">Bachelor of Technology & Livelihood Education - Major in Home Economics</option>
                <option value="BTLED-IA">Bachelor of Technology & Livelihood Education - Major in Industrial Arts</option>
                <option value="BTLED-ICT">Bachelor of Technology & Livelihood Education - Major in Information and Communication Teachnology</option>
                <option value="BFPT">Bachelor in Food Processiong and Technology</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Second choice of Course</label>
              <select class="form-select" name="course_choice_2" required>
                <option value="" disabled selected>Select Course</option>
                <option value="BSIT">Bachelor of Science in Information Technology</option>
                <option value="BTLED-HE">Bachelor of Technology & Livelihood Education - Major in Home Economics</option>
                <option value="BTLED-IA">Bachelor of Technology & Livelihood Education - Major in Industrial Arts</option>
                <option value="BTLED-ICT">Bachelor of Technology & Livelihood Education - Major in Information and Communication Teachnology</option>
                <option value="BFPT">Bachelor in Food Processiong and Technology</option>
              </select>
            </div>
          </div>

          <!-- Upload -->
          <h5 class="mb-3 mt-5" style="color: #333; font-weight: bold;">Upload Requirements</h5>
          <div class="mb-3">
            <label class="form-label">Form 138 / TOR for transferee</label>
            <input type="file" class="form-control" name="transcript" required>
          </div>
          <div class="mb-3">
            <label class="form-label">PSA Birth Certificate</label>
            <input type="file" class="form-control" name="good_moral" required>
          </div>
          <div class="mb-3">
            <label class="form-label">School ID</label>
            <input type="file" class="form-control" name="birth_certificate" required>
          </div>
          <div class="mb-3">
            <label class="form-label">2x2 ID Picture</label>
            <input type="file" class="form-control" name="picture" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Parent's Income Tax (ITR) or Affidavit of Low Income</label>
            <input type="file" class="form-control" name="valid_id">
          </div>

          <!-- Submit and Clear Buttons -->
          <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="submit" class="btn btn text-white" style="background-color: rgba(22,2,67)">Submit Application</button>
            <button type="reset" class="btn btn text-white" style="background-color: rgb(255, 177, 22)">Clear All</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  </div>

  <footer class="mt-5">
    &copy; 2025 USTP Admission | All Rights Reserved
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>