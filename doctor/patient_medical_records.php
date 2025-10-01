<?php
session_start();
require '../auth/db_connect.php';

// Ensure user is a logged-in doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

// Get patient ID from the URL
if (!isset($_GET['patient_id'])) {
    header("Location: my_patients.php");
    exit();
}
$patient_id = $_GET['patient_id'];
$doctor_id = $_SESSION['user_id'];

// Fetch patient's name
$patient_name = '';
try {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ? AND role = 'patient'");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch();
    if ($patient) {
        $patient_name = $patient['name'];
    } else {
        // Patient not found or is not a patient, redirect
        header("Location: my_patients.php");
        exit();
    }
} catch (PDOException $e) {
    // Handle DB error
}

// Fetch all medical records for this patient, including patient-uploaded ones
$medical_records = [];
try {
    $stmt = $conn->prepare(
        "SELECT 
            r.id, 
            r.record_type, 
            r.title, 
            r.record_date, 
            r.summary, 
            r.file_path,
            r.uploaded_by_patient,
            COALESCE(u.name, r.doctor_name_external, 'N/A') AS author_name
         FROM medical_records r
         LEFT JOIN users u ON r.doctor_id = u.id
         WHERE r.patient_id = ?
         ORDER BY r.record_date DESC"
    );
    $stmt->execute([$patient_id]);
    $medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle DB error
}

function getIconForRecordType($type) {
    switch (strtolower($type)) {
        case 'consultation': return 'fa-stethoscope';
        case 'lab result': return 'fa-vial';
        case 'prescription': return 'fa-prescription-bottle-alt';
        default: return 'fa-file-medical';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records for <?php echo htmlspecialchars($patient_name); ?></title>
    <link rel="stylesheet" href="../assets/css/doctor_dashboard.css">
    <link rel="stylesheet" href="../assets/css/doctor_medical_records.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <!-- <aside class="sidebar">
            <div class="sidebar-header"><a href="../homepage.php" class="logo">HealthFirst</a></div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a href="my_schedule.php" class="nav-item"><i class="fas fa-calendar-day"></i><span>My Schedule</span></a>
                <a href="manage_request.php" class="nav-item"><i class="fas fa-user-plus"></i><span>Manage Requests</span></a>
                <a href="my_patients.php" class="nav-item active"><i class="fas fa-users"></i><span>My Patients</span></a>
                <a href="doctor_profile.php" class="nav-item"><i class="fas fa-user-md"></i><span>Profile</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside> -->

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <a href="my_patients.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
                    <h1>Medical History for <?php echo htmlspecialchars($patient_name); ?></h1>
                </div>
                <div class="header-right">
                    <a href="add_medical_record.php?patient_id=<?php echo $patient_id; ?>" class="add-record-btn"><i class="fas fa-plus"></i> Add New Record</a>
                </div>
            </header>

            <section class="records-timeline">
                <?php if (!empty($medical_records)): ?>
                    <?php foreach ($medical_records as $record): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon record-type-<?php echo strtolower(str_replace(' ', '-', $record['record_type'])); ?>">
                                <i class="fas <?php echo getIconForRecordType($record['record_type']); ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="record-header">
                                    <span class="record-date"><?php echo date("F d, Y", strtotime($record['record_date'])); ?></span>
                                    <?php if ($record['uploaded_by_patient']): ?>
                                        <span class="upload-badge patient-upload"><i class="fas fa-user-edit"></i> Uploaded by Patient</span>
                                    <?php else: ?>
                                        <span class="upload-badge doctor-upload"><i class="fas fa-user-md"></i> Added by Doctor</span>
                                    <?php endif; ?>
                                </div>
                                <h3><?php echo htmlspecialchars($record['title']); ?></h3>
                                <p class="record-author">
                                    Author: <?php echo htmlspecialchars($record['author_name']); ?>
                                </p>
                                <p class="record-summary"><?php echo htmlspecialchars($record['summary']); ?></p>
                                <div class="record-actions">
                                    <?php if (!empty($record['file_path'])): ?>
                                        <a href="../<?php echo htmlspecialchars($record['file_path']); ?>" class="action-btn download" download><i class="fas fa-download"></i> Download File</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-records-card">
                        <i class="fas fa-folder-open"></i>
                        <h2>No Medical Records Found</h2>
                        <p>This patient does not have any records yet. You can add one using the button above.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>

