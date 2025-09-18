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

$doctor_id = $_SESSION['user_id'];
$patient_id = $_GET['patient_id'];

// Fetch patient's name
$patient_name = '';
try {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$patient_id]);
    $patient_name = $stmt->fetchColumn();
} catch(PDOException $e){}


$medical_records = [];
try {
    $stmt = $conn->prepare(
        "SELECT r.id, r.record_type, r.title, r.record_date, r.summary, u.name AS doctor_name
         FROM medical_records r
         JOIN users u ON r.doctor_id = u.id
         WHERE r.patient_id = ?
         ORDER BY r.record_date DESC"
    );
    $stmt->execute([$patient_id]);
    $medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Optional: handle database error
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
    <title>Medical Records for <?php echo htmlspecialchars($patient_name); ?> - HealthFirst</title>
     <link rel="stylesheet" href="../assets/css/doctor_dashboard.css">
    <link rel="stylesheet" href="../assets/css/doctor_medical_records.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                <a href="patient_medical_records.php" class="nav-item active"><i class="fas fa-users"></i><span>Medical Records</span></a>
                <a href="doctor_profile.php" class="nav-item"><i class="fas fa-user-md"></i><span>Profile</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <a href="my_patients.php" class="back-link"><i class="fas fa-arrow-left"></i></a>
                    <h1>Medical Records for <?php echo htmlspecialchars($patient_name); ?></h1>
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
                                <span class="record-date"><?php echo date("F d, Y", strtotime($record['record_date'])); ?></span>
                                <h3><?php echo htmlspecialchars($record['title']); ?></h3>
                                <p class="record-doctor">by <?php echo htmlspecialchars($record['doctor_name']); ?></p>
                                <p class="record-summary"><?php echo htmlspecialchars($record['summary']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-records-card">
                        <i class="fas fa-folder-open"></i>
                        <h2>No Medical Records Found</h2>
                        <p>You have not added any records for this patient yet.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
