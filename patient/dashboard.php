<?php
session_start();
require '../auth/db_connect.php'; // Ensure the path to your db_connect.php is correct

// Redirect to login if user is not logged in or not a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_name = $_SESSION['user_name'];
$patient_id = $_SESSION['user_id'];

// --- Fetch Real-Time Data ---

// 1. Get the next single upcoming appointment
$upcoming_appointment = null;
try {
    $stmt = $conn->prepare(
        "SELECT a.appointment_date, a.appointment_time, u.name AS doctor_name
         FROM appointments a
         JOIN users u ON a.doctor_id = u.id
         WHERE a.patient_id = ? AND a.appointment_date >= CURDATE() AND a.status = 'confirmed'
         ORDER BY a.appointment_date ASC, a.appointment_time ASC
         LIMIT 1"
    );
    $stmt->execute([$patient_id]);
    $upcoming_appointment = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error, maybe log it
}

// 2. Get the most recently created appointments (Recent Activity)
$recent_appointments = [];
try {
    $stmt = $conn->prepare(
        "SELECT a.appointment_date, a.status, u.name AS doctor_name
         FROM appointments a
         JOIN users u ON a.doctor_id = u.id
         WHERE a.patient_id = ?
         ORDER BY a.created_at DESC
         LIMIT 5"
    );
    $stmt->execute([$patient_id]);
    $recent_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="../homepage.php" class="logo">HealthFirst</a>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a href="view_doctors.php" class="nav-item"><i class="fas fa-user-md"></i><span>View Doctors</span></a>
                <a href="book_appointment.php" class="nav-item"><i class="fas fa-calendar-plus"></i><span>Book Appointment</span></a>
                <a href="my_appointments.php" class="nav-item"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
                <a href="patient_profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
                    <p>Here's your health summary. Have a great day!</p>
                </div>
                <div class="header-right">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="profile-dropdown">
                    <a href="patient_profile.php"><img src="https://placehold.co/40x40/a78bfa/ffffff?text=<?php echo substr($user_name, 0, 1); ?>" alt="User Avatar"></a>
                    
                    </div>
                </div>
            </header>

            <section class="dashboard-widgets">
                <div class="widget-card">
                    <div class="card-icon" style="background-color: #e9d5ff;">
                        <i class="fas fa-calendar-alt" style="color: #9333ea;"></i>
                    </div>
                    <div class="card-content">
                        <h3>Upcoming Appointment</h3>
                        <?php if ($upcoming_appointment): ?>
                            <p><strong>Dr. <?php echo htmlspecialchars($upcoming_appointment['doctor_name']); ?></strong></p>
                            <p><?php echo date("F j, Y", strtotime($upcoming_appointment['appointment_date'])); ?>, <?php echo date("g:i A", strtotime($upcoming_appointment['appointment_time'])); ?></p>
                        <?php else: ?>
                            <p>No confirmed upcoming appointments.</p>
                            <a href="book_appointment.php" class="card-link">Book One Now</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="widget-card">
                    <div class="card-icon" style="background-color: #dbeafe;">
                        <i class="fas fa-plus-circle" style="color: #3b82f6;"></i>
                    </div>
                    <div class="card-content">
                        <h3>New Appointment</h3>
                        <p>Need to see a doctor?</p>
                        <a href="book_appointment.php" class="card-link">Book Now</a>
                    </div>
                </div>
                <div class="widget-card">
                    <div class="card-icon" style="background-color: #d1fae5;">
                        <i class="fas fa-file-medical" style="color: #10b981;"></i>
                    </div>
                    <div class="card-content">
                        <h3>Medical Records</h3>
                        <p>View your health history</p>
                        <a href="medical_records.php" class="card-link">View Records</a>
                    </div>
                </div>
            </section>

            <section class="appointments-table">
                <h2>Recent Activity</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_appointments)): ?>
                            <?php foreach ($recent_appointments as $appt): ?>
                                <tr>
                                    <td>Dr. <?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                    <td><?php echo date("F j, Y", strtotime($appt['appointment_date'])); ?></td>
                                    <td><span class="status <?php echo htmlspecialchars(strtolower($appt['status'])); ?>"><?php echo ucfirst(htmlspecialchars($appt['status'])); ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">No recent activity to show.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
