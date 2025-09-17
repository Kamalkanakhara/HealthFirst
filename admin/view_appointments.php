<?php
session_start();
require '../auth/db_connect.php';

// Security checks
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all appointments
$appointments = [];
try {
    $stmt = $conn->prepare(
        "SELECT a.id, a.appointment_date, a.appointment_time, a.status, p.name AS patient_name, d.name AS doctor_name
         FROM appointments a
         JOIN users p ON a.patient_id = p.id
         JOIN users d ON a.doctor_id = d.id
         ORDER BY a.appointment_date DESC"
    );
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Appointments - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="../assets/css/manage_request.css">
    <!-- This line was missing and has been added to load the icons -->
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
                <a href="manage_users.php" class="nav-item"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="manage_doctors.php" class="nav-item"><i class="fas fa-user-md"></i><span>Manage Doctors</span></a>
                <a href="view_appointments.php" class="nav-item active"><i class="fas fa-calendar-alt"></i><span>View Appointments</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1>All Appointments</h1></div>
            </header>
            <section class="requests-list">
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date & Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($appointments)): ?>
                            <?php foreach ($appointments as $appt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                                    <td>Dr. <?php echo htmlspecialchars($appt['doctor_name']); ?></td>
                                    <td><?php echo date("M d, Y", strtotime($appt['appointment_date'])) . ' at ' . date("g:i A", strtotime($appt['appointment_time'])); ?></td>
                                    <td><span class="status <?php echo htmlspecialchars(strtolower($appt['status'])); ?>"><?php echo ucfirst(htmlspecialchars($appt['status'])); ?></span></td>
                                    <td class="action-cell">
                                        <?php if ($appt['status'] == 'pending'): ?>
                                            <form action="admin_update_status.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                                <input type="hidden" name="redirect_to" value="view_appointments.php">
                                                <button type="submit" name="status" value="confirmed" class="action-btn confirm">Confirm</button>
                                                <button type="submit" name="status" value="cancelled" class="action-btn cancel">Cancel</button>
                                            </form>
                                        <?php elseif ($appt['status'] == 'confirmed'): ?>
                                            <form action="admin_update_status.php" method="POST" style="display:inline;">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                                <input type="hidden" name="redirect_to" value="view_appointments.php">
                                                <button type="submit" name="status" value="completed" class="action-btn complete">Mark as Completed</button>
                                            </form>
                                        <?php else: ?>
                                            <span>—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No appointments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
