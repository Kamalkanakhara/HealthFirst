<?php
session_start();
require '../auth/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['patient_id'])) {
    header("Location: my_patients.php");
    exit();
}
$patient_id = $_GET['patient_id'];

// Fetch patient's name
$patient_name = '';
try {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$patient_id]);
    $patient_name = $stmt->fetchColumn();
} catch(PDOException $e){}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medical Record - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/doctor_dashboard.css">
    <link rel="stylesheet" href="../assets/css/doctor_medical_records.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header"><a href="../homepage.php" class="logo">HealthFirst</a></div>
             <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a href="my_schedule.php" class="nav-item"><i class="fas fa-calendar-day"></i><span>My Schedule</span></a>
                <a href="manage_request.php" class="nav-item"><i class="fas fa-user-plus"></i><span>Manage Request</span></a>
                <a href="my_patients.php" class="nav-item active"><i class="fas fa-users"></i><span>My Patients</span></a>
                <a href="doctor_profile.php" class="nav-item"><i class="fas fa-user-md"></i><span>Profile</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <main class="main-content">
             <header class="main-header">
                <div class="header-left">
                    <a href="patient_medical_records.php?patient_id=<?php echo $patient_id; ?>" class="back-link"><i class="fas fa-arrow-left"></i></a>
                    <h1>Add Record for <?php echo htmlspecialchars($patient_name); ?></h1>
                </div>
            </header>
            
            <section class="form-container">
                 <form action="add_medical_record_process.php" method="POST">
                    <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
                    
                    <div class="input-group">
                        <label for="record_date">Date</label>
                        <input type="date" id="record_date" name="record_date" required>
                    </div>

                    <div class="input-group">
                        <label for="record_type">Record Type</label>
                        <select id="record_type" name="record_type" required>
                            <option value="Consultation">Consultation</option>
                            <option value="Lab Result">Lab Result</option>
                            <option value="Prescription">Prescription</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="title">Title / Subject</label>
                        <input type="text" id="title" name="title" placeholder="e.g., Annual Check-up, Blood Test Results" required>
                    </div>

                    <div class="input-group">
                        <label for="summary">Summary / Notes</label>
                        <textarea id="summary" name="summary" rows="6" placeholder="Enter details, findings, prescriptions, etc."></textarea>
                    </div>
                    
                    <button type="submit" class="submit-btn">Save Record</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
