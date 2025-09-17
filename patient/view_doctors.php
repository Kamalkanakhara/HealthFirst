<?php
session_start();
require '../auth/db_connect.php';

// Redirect to login if user is not logged in or not a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_name = $_SESSION['user_name'];

// Fetch all doctors and their details in real-time
$doctors = [];
try {
    $stmt = $conn->prepare(
        "SELECT u.id, u.name, u.email, d.specialty, d.bio
         FROM users u
         JOIN doctors_details d ON u.id = d.user_id
         WHERE u.role = 'doctor'"
    );
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Doctors - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/view_doctors.css">
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
                <a href="view_doctors.php" class="nav-item active"><i class="fas fa-user-md"></i><span>View Doctors</span></a>
                <a href="book_appointment.php" class="nav-item"><i class="fas fa-calendar-plus"></i><span>Book Appointment</span></a>
                <a href="my_appointments.php" class="nav-item"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
                <a href="patient_profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1>Our Specialists</h1></div>
            </header>

            <section class="doctors-grid">
                <?php if (!empty($doctors)): ?>
                    <?php foreach ($doctors as $doctor): ?>
                        <div class="doctor-card">
                            <div class="card-header">
                                <img src="https://placehold.co/80x80/a78bfa/ffffff?text=Dr" alt="Doctor Avatar">
                                <div class="doctor-info">
                                    <h3>Dr. <?php echo htmlspecialchars($doctor['name']); ?></h3>
                                    <p><?php echo htmlspecialchars($doctor['specialty']); ?></p>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><?php echo htmlspecialchars($doctor['bio'] ?? 'No biography available.'); ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="book_appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="book-btn">Book Now</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No doctors are available at this time.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
