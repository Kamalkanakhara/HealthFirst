<?php
session_start();
require '../auth/db_connect.php'; // Ensure the path is correct

// Redirect to login if user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

$admin_name = $_SESSION['user_name'];

// --- Fetch Real-Time Data for Widgets ---

// 1. Get total user count
$total_users_count = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $total_users_count = $stmt->fetchColumn();
} catch (PDOException $e) {}

// 2. Get total doctor count
$total_doctors_count = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'doctor'");
    $total_doctors_count = $stmt->fetchColumn();
} catch (PDOException $e) {}

// 3. Get total appointments count
$total_appointments_count = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM appointments");
    $total_appointments_count = $stmt->fetchColumn();
} catch (PDOException $e) {}


// --- Fetch Recent User Registrations for the Table ---
$recent_users = [];
try {
    $stmt = $conn->query("SELECT name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
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
                <a href="#" class="nav-item active"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a href="manage_users.php" class="nav-item"><i class="fas fa-users"></i><span>Manage Users</span></a>
                <a href="manage_doctors.php" class="nav-item"><i class="fas fa-user-md"></i><span>Manage Doctors</span></a>
                <a href="view_appointments.php" class="nav-item"><i class="fas fa-calendar-alt"></i><span>View Appointments</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left">
                    <h1>Admin Dashboard</h1>
                    <p>Welcome, <?php echo htmlspecialchars($admin_name); ?>!</p>
                </div>
                <div class="header-right">
                    <button class="notification-btn">
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="profile-dropdown">
                        <img src="https://placehold.co/40x40/a78bfa/ffffff?text=A" alt="Admin Avatar">
                    </div>
                </div>
            </header>

            <section class="dashboard-widgets">
                <!-- Widget 1: Total Users -->
                <div class="widget-card">
                    <div class="card-icon" style="background-color: #dbeafe;">
                        <i class="fas fa-users" style="color: #3b82f6;"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Users</h3>
                        <p class="stat"><?php echo $total_users_count; ?></p>
                    </div>
                </div>

                <!-- Widget 2: Total Doctors -->
                <div class="widget-card">
                    <div class="card-icon" style="background-color: #d1fae5;">
                        <i class="fas fa-user-md" style="color: #10b981;"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Doctors</h3>
                        <p class="stat"><?php echo $total_doctors_count; ?></p>
                    </div>
                </div>

                <!-- Widget 3: Total Appointments -->
                <div class="widget-card">
                    <div class="card-icon" style="background-color: #e9d5ff;">
                        <i class="fas fa-calendar-check" style="color: #9333ea;"></i>
                    </div>
                    <div class="card-content">
                        <h3>Total Appointments</h3>
                        <p class="stat"><?php echo $total_appointments_count; ?></p>
                    </div>
                </div>
            </section>

            <section class="data-table">
                <h2>Recent User Registrations</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_users)): ?>
                            <?php foreach ($recent_users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                                    <td><?php echo date("F j, Y", strtotime($user['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No recent registrations found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
