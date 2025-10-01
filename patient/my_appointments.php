<?php
session_start();
require '../auth/db_connect.php';

// Redirect to login if user is not logged in or not a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_name = $_SESSION['user_name'];
$patient_id = $_SESSION['user_id'];

// Fetch all appointments for the logged-in patient
$all_appointments = [];
try {
    $stmt = $conn->prepare(
        "SELECT a.id, a.appointment_date, a.appointment_time, a.status, u.name AS doctor_name
         FROM appointments a
         JOIN users u ON a.doctor_id = u.id
         WHERE a.patient_id = ?
         ORDER BY a.appointment_date DESC, a.appointment_time DESC"
    );
    $stmt->execute([$patient_id]);
    $all_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}

// Get the current time to compare against appointment times
$now = new DateTime("now", new DateTimeZone('Asia/Kolkata')); // Set your server's timezone

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/my_appointments.css">
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
                <a href="my_appointments.php" class="nav-item "><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
                <a href="medical_records.php" class="nav-item "><i class="fas fa-file-medical"></i><span>Medical Records</span></a>
                <a href="patient_profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1>My Appointments</h1></div>
            </header>

            <section class="appointments-list">
                <table>
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($all_appointments)): ?>
                            <?php foreach ($all_appointments as $appt): ?>
                                <tr>
                                    <td>Dr. <?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                    <td><?php echo date("F j, Y", strtotime($appt['appointment_date'])); ?></td>
                                    <td><?php echo (new DateTime($appt['appointment_time']))->format('g:i A'); ?></td>
                                    <td><span class="status <?php echo htmlspecialchars(strtolower($appt['status'])); ?>"><?php echo ucfirst(htmlspecialchars($appt['status'])); ?></span></td>
                                    <td class="action-cell">
                                        <?php 
                                        // Create a DateTime object for the specific appointment
                                        $appointment_datetime = new DateTime($appt['appointment_date'] . ' ' . $appt['appointment_time'], new DateTimeZone('Asia/Kolkata'));

                                        // UPDATED LOGIC: Show buttons if the appointment time is in the future, regardless of status (unless completed)
                                        if ($appointment_datetime > $now && $appt['status'] != 'completed'): 
                                        ?>
                                            <a href="update_appointment.php?id=<?php echo $appt['id']; ?>" class="action-btn update">Update</a>
                                            <form action="cancel_appointment.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this appointment?');" style="display:inline;">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                                <button type="submit" class="action-btn delete">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span>—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">You have no appointments.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
