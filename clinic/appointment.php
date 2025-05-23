<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Book an Appointment</title>
  <style>
    :root {
      --primary-color: #2c6ecb;
      --primary-light: #eef5ff;
      --primary-dark: #1a4fa0;
      --accent-color: #4fd1c5;
      --text-color: #333333;
      --text-light: #666666;
      --background: #ffffff;
      --background-alt: #f9fafb;
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

    .logo {
      max-height: 80px;
      margin-bottom: 1rem;
      filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.07));
    }

    h1 {
      color: var(--primary-dark);
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
    }

    .subtitle {
      color: var(--text-light);
      font-size: 1rem;
      margin-bottom: 1.5rem;
    }

    .progress-indicator {
      height: 6px;
      width: 100%;
      background-color: var(--border-color);
      border-radius: 3px;
      margin-bottom: 2rem;
      overflow: hidden;
    }

    .progress-indicator::before {
      content: '';
      display: block;
      height: 100%;
      width: 100%;
      background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
      animation: progress-animation 2s ease-in-out infinite alternate;
    }

    @keyframes progress-animation {
      0% {
        width: 10%;
        transform: translateX(0);
      }
      100% {
        width: 10%;
        transform: translateX(900%);
      }
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
      transition: all 0.3s ease;
    }

    .section:last-of-type {
      border-bottom: none;
    }

    .section:hover {
      background-color: var(--primary-light);
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
      transition: all 0.3s ease;
    }

    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(44, 110, 203, 0.15);
    }

    input::placeholder, textarea::placeholder {
      color: #aab7c4;
    }

    textarea {
      resize: vertical;
      min-height: 100px;
    }

    button {
      display: block;
      width: 300px;
      max-width: 100%;
      margin: 2rem auto;
      padding: 1rem;
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      color: white;
      border: none;
      border-radius: 8px;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: var(--shadow);
    }

    button:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 14px rgba(0, 0, 0, 0.1);
    }

    button:active {
      transform: translateY(0);
    }

    button:disabled {
      background: #cccccc;
      cursor: not-allowed;
    }

    .success-message {
      display: none;
      background-color: #e6f7ef;
      border-left: 4px solid var(--success-color);
      color: var(--success-color);
      padding: 1rem;
      margin: 1rem auto;
      border-radius: 8px;
      text-align: center;
      font-weight: bold;
      animation: fadeIn 0.5s ease-in-out;
      width: 300px;
      max-width: 100%;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    #backToDashboardBtn {
      background: linear-gradient(135deg, #6b7280, #4b5563);
      margin: 1rem auto;
      width: 300px;
      max-width: 100%;
      display: none;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .container {
        padding: 1rem;
      }
      
      .section {
        padding: 1.25rem;
      }
      
      .row {
        flex-direction: column;
        gap: 1rem;
      }
      
      .input-group {
        width: 100%;
      }

      button {
        width: 100%;
      }

      .success-message {
        width: 100%;
      }

      #backToDashboardBtn {
        width: 100%;
      }
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="container">
  <div class="form-header">
    <img src="Image/clinic.gif" alt="Clinic Logo" class="logo">
    <h1>Book an Appointment</h1>
    <p class="subtitle">Fill out the form below to schedule your clinic visit</p>
  </div>

  <div class="progress-indicator"></div>

  <form id="appointmentForm">
    <section class="section">
      <h2>Basic Personal Details</h2>
      <div class="row">
        <div class="input-group">
          <label for="fullName">Full Name:</label>
          <input type="text" id="fullName" name="fullName" placeholder="e.g., Juan Dela Cruz" aria-label="Full Name" required>
        </div>
        <div class="input-group">
          <label for="studentID">Student ID Number:</label>
          <input type="text" id="studentID" name="studentID" placeholder="e.g., 2023123456" aria-label="Student ID" required>
        </div>
      </div>

      <div class="input-group">
        <label for="course">Course/Program:</label>
        <select id="course" name="course" required onchange="toggleBTLEDSub()">
          <option value="">Select Course</option>
          <option value="BSIT">BSIT</option>
          <option value="BFPT">BFPT</option>
          <option value="BTLED">BTLED</option>
        </select>
      </div>

      <div class="input-group" id="btledSubGroup" style="display: none;">
        <label for="btledSpecialization">BTLED Specialization:</label>
        <select id="btledSpecialization" name="btledSpecialization">
          <option value="">Select Specialization</option>
          <option value="H.E">H.E</option>
          <option value="I.C.T">I.C.T</option>
          <option value="I.A">I.A</option>
        </select>
      </div>

      <div class="input-group">
        <label for="yearLevel">Year Level:</label>
        <select id="yearLevel" name="yearLevel" required>
          <option value="">Select Year</option>
          <option value="1st Year">1st Year</option>
          <option value="2nd Year">2nd Year</option>
          <option value="3rd Year">3rd Year</option>
          <option value="4th Year">4th Year</option>
        </select>
      </div>

      <div class="row">
        <div class="input-group">
          <label for="dob">Date of Birth:</label>
          <input type="date" id="dob" name="dob" aria-label="Date of Birth" required>
        </div>
        <div class="input-group">
          <label for="sex">Sex/Gender:</label>
          <select id="sex" name="sex" aria-label="Sex" required>
            <option value="">Select</option>
            <option>Male</option>
            <option>Female</option>
            <option>Other</option>
          </select>
        </div>
      </div>
    </section>

    <section class="section">
      <h2>Contact Information</h2>
      <div class="row">
        <div class="input-group">
          <label for="mobileNumber">Mobile Number:</label>
          <input type="tel" id="mobileNumber" name="mobileNumber" placeholder="e.g., 09123456789" pattern="[0-9]{11}" aria-label="Mobile Number" required>
        </div>
        <div class="input-group">
          <label for="email">Email Address (optional):</label>
          <input type="email" id="email" name="email" placeholder="e.g., juan@email.com" aria-label="Email">
        </div>
      </div>
    </section>

    <section class="section">
      <h2>Health-Related Details</h2>
      <div class="row">
        <div class="input-group">
          <label for="reason">Reason for Appointment:</label>
          <input type="text" id="reason" name="reason" placeholder="e.g., Check-up" aria-label="Reason" required>
        </div>
        <div class="input-group">
          <label for="medicalConditions">Existing Medical Conditions:</label>
          <textarea id="medicalConditions" name="medicalConditions" placeholder="(Optional)" aria-label="Medical Conditions"></textarea>
        </div>
      </div>

      <div class="row">
        <div class="input-group full-width">
          <label for="symptoms">Current Symptoms:</label>
          <textarea id="symptoms" name="symptoms" placeholder="Describe your symptoms" aria-label="Symptoms"></textarea>
        </div>
      </div>
    </section>

    <section class="section">
      <h2>Appointment Details</h2>
      <div class="row">
        <div class="input-group full-width">
          <label for="preferredDate">Preferred Date and Time:</label>
          <input type="datetime-local" id="preferredDate" name="preferredDate" aria-label="Preferred Date and Time" required>
        </div>
      </div>
    </section>

    <section class="section">
      <h2>Emergency Contact Information</h2>
      <div class="row">
        <div class="input-group">
          <label for="emergencyName">Full Name:</label>
          <input type="text" id="emergencyName" name="emergencyName" placeholder="e.g., Maria Santos" aria-label="Emergency Name" required>
        </div>
        <div class="input-group">
          <label for="emergencyRelationship">Relationship to Student:</label>
          <input type="text" id="emergencyRelationship" name="emergencyRelationship" placeholder="e.g., Mother" aria-label="Emergency Relationship" required>
        </div>
      </div>

      <div class="row">
        <div class="input-group">
          <label for="emergencyMobile">Mobile Number:</label>
          <input type="tel" id="emergencyMobile" name="emergencyMobile" placeholder="e.g., 09123456789" pattern="[0-9]{11}" aria-label="Emergency Mobile" required>
        </div>
        <div class="input-group">
          <label for="address">Address (optional):</label>
          <input type="text" id="address" name="address" placeholder="e.g., Brgy. San Jose, Oroquieta City" aria-label="Address">
        </div>
      </div>
    </section>

    <button type="submit" id="submitBtn">
      Submit Appointment
    </button>

    <div id="successMessage" class="success-message">✅ Thank you! Your appointment has been booked.</div>

    <button type="button" id="backToDashboardBtn" onclick="window.location.href='student.php'">
      Back to Dashboard
    </button>
  </form>
</div>

<script>
    function toggleBTLEDSub() {
        const courseSelect = document.getElementById('course');
        const btledSubGroup = document.getElementById('btledSubGroup');
        
        if (courseSelect.value === 'BTLED') {
            btledSubGroup.style.display = 'block';
        } else {
            btledSubGroup.style.display = 'none';
        }
    }

    const form = document.getElementById('appointmentForm');
    const submitBtn = document.getElementById('submitBtn');
    const successMessage = document.getElementById('successMessage');
    const backToDashboardBtn = document.getElementById('backToDashboardBtn');

    // Initially hide the Back to Dashboard button
    backToDashboardBtn.style.display = 'none';

    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';

        const formData = new FormData(form);

        const response = await fetch('submit_appointment.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        if (result.status === 'success') {
            // Store unique_key in a cookie
            document.cookie = `unique_key=${result.unique_key}; path=/; max-age=2592000`; // 30 days
            successMessage.style.display = 'block';
            
            // Hide Submit button and show Back to Dashboard button
            submitBtn.style.display = 'none';
            backToDashboardBtn.style.display = 'block';

            form.reset();
        } else {
            alert(result.message || "There was an error submitting your form.");
        }

        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Appointment';
    });
</script>

</body>
</html>