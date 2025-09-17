<?php
session_start();
require '../auth/db_connect.php';

// Redirect to login if user is not logged in or not a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// --- MOCK DATA: In a real application, you would fetch this from your database ---
$medical_records = [
    [
        'id' => 1,
        'type' => 'Consultation',
        'title' => 'Annual Check-up',
        'date' => '2023-08-15',
        'doctor' => 'Dr. Emily Carter',
        'summary' => 'Overall health is excellent. Discussed diet and exercise improvements. Recommended continuing current vitamin regimen.',
        'icon' => 'fa-stethoscope'
    ],
    [
        'id' => 2,
        'type' => 'Lab Result',
        'title' => 'Blood Test Results',
        'date' => '2023-08-20',
        'doctor' => 'Central Labs',
        'summary' => 'Cholesterol levels are within the normal range. Vitamin D slightly low. All other markers are normal.',
        'icon' => 'fa-vial'
    ],
    [
        'id' => 3,
        'type' => 'Prescription',
        'title' => 'Vitamin D Supplement',
        'date' => '2023-08-21',
        'doctor' => 'Dr. Emily Carter',
        'summary' => 'Prescribed Vitamin D3, 2000 IU daily to address deficiency found in recent lab work.',
        'icon' => 'fa-prescription-bottle-alt'
    ],
    [
        'id' => 4,
        'type' => 'Consultation',
        'title' => 'Follow-up on Knee Pain',
        'date' => '2023-05-10',
        'doctor' => 'Dr. Ben Stone',
        'summary' => 'Minor sprain diagnosed. Recommended rest, ice, and light stretching exercises. Condition has improved significantly since initial injury.',
        'icon' => 'fa-stethoscope'
    ],
];
// --- END OF MOCK DATA ---

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/medical_records.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header"><a href="../homepage.php" class="logo">HealthFirst</a></div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a href="view_doctors.php" class="nav-item"><i class="fas fa-user-md"></i><span>View Doctors</span></a>
                <a href="book_appointment.php" class="nav-item"><i class="fas fa-calendar-plus"></i><span>Book Appointment</span></a>
                <a href="my_appointments.php" class="nav-item"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
                <a href="medical_records.php" class="nav-item active"><i class="fas fa-file-medical"></i><span>Medical Records</span></a>
                <a href="patient_profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1>Your Medical History</h1></div>
                 <div class="header-right">
                    <button class="upload-btn"><i class="fas fa-upload"></i> Upload Record</button>
                </div>
            </header>

            <section class="records-timeline">
                <?php if (!empty($medical_records)): ?>
                    <?php foreach ($medical_records as $record): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon record-type-<?php echo strtolower($record['type']); ?>">
                                <i class="fas <?php echo $record['icon']; ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <span class="record-date"><?php echo date("F d, Y", strtotime($record['date'])); ?></span>
                                <h3><?php echo htmlspecialchars($record['title']); ?></h3>
                                <p class="record-doctor">with <?php echo htmlspecialchars($record['doctor']); ?></p>
                                <p class="record-summary"><?php echo htmlspecialchars($record['summary']); ?></p>
                                <div class="record-actions">
                                    <a href="#" class="action-btn view-details">View Details</a>
                                    <a href="#" class="action-btn download"><i class="fas fa-download"></i> Download</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-records-card">
                        <i class="fas fa-folder-open"></i>
                        <h2>No Medical Records Found</h2>
                        <p>Your uploaded and shared medical records will appear here.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
