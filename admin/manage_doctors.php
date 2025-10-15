<?php
session_start();
require '../auth/db_connect.php';

// Redirect to login if user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all doctors and their details
$doctors = [];
try {
    $stmt = $conn->prepare(
        "SELECT u.id, u.name, u.email, u.phone, d.specialty
         FROM users u
         LEFT JOIN doctors_details d ON u.id = d.user_id
         WHERE u.role = 'doctor'
         ORDER BY u.created_at DESC"
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
    <title>Manage Doctors - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/manage_doctors.css">
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
                <a href="manage_users.php" class="nav-item active"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="manage_doctors.php" class="nav-item"><i class="fas fa-user-md"></i><span>Manage Doctors</span></a>
                <a href="view_appointments.php" class="nav-item"><i class="fas fa-calendar-alt"></i><span>View Appointments</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1>Manage Doctors</h1></div>
            </header>

            <section class="doctors-list">
                <table>
                    <thead>
                        <tr>
                            <th>Doctor Name</th>
                            <th>Specialty</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($doctors)): ?>
                            <?php foreach ($doctors as $doctor): ?>
                                <tr>
                                    <td>Dr. <?php echo htmlspecialchars($doctor['name']); ?></td>
                                    <td><?php echo htmlspecialchars($doctor['specialty'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                    <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                    <td class="action-cell">
                                        <a href="edit_doctors.php?id=<?php echo $doctor['id']; ?>" class="action-btn edit">Edit</a>
                                        <form action="delete_doctor.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this doctor? This action cannot be undone.');" style="display:inline;">
                                            <input type="hidden" name="doctor_id" value="<?php echo $doctor['id']; ?>">
                                            <button type="submit" class="action-btn delete">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No doctors found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
