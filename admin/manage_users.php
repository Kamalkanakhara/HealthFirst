<?php
session_start();
require '../auth/db_connect.php';

// Redirect to login if user is not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all doctors
$doctors = [];
try {
    $stmt = $conn->prepare("SELECT id, name, email, phone, role FROM users WHERE role = 'doctor' ORDER BY created_at DESC");
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

// Fetch all patients and count their pending appointments
$patients = [];
try {
    $stmt = $conn->prepare(
        "SELECT u.id, u.name, u.email, u.phone, u.role, 
         (SELECT COUNT(*) FROM appointments a WHERE a.patient_id = u.id AND a.status = 'pending') AS pending_count
         FROM users u 
         WHERE u.role = 'patient' 
         ORDER BY u.created_at DESC"
    );
    $stmt->execute();
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/manage_users.css">
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
                <div class="header-left"><h1>Manage Users</h1></div>
            </header>

            <div class="user-tables-container">
                <!-- Patients Table -->
                <section class="users-list">
                    <h2>Patients</h2>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Pending Appointments</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($patients)): ?>
                                    <?php foreach ($patients as $patient): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                            <td><?php echo $patient['pending_count']; ?></td>
                                            <td class="action-cell">
                                                <a href="view_appointments.php?user_id=<?php echo $patient['id']; ?>" class="action-btn view">View Appointments</a>
                                                <form action="delete_users.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this patient?');" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $patient['id']; ?>">
                                                    <button type="submit" class="action-btn delete">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" style="text-align: center;">No patients found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                
                <!-- Doctors Table -->
                <section class="users-list">
                    <h2>Doctors</h2>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($doctors)): ?>
                                    <?php foreach ($doctors as $doctor): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($doctor['name']); ?></td>
                                            <td><?php echo htmlspecialchars($doctor['email']); ?></td>
                                            <td><?php echo htmlspecialchars($doctor['phone']); ?></td>
                                            <td class="action-cell">
                                                <form action="delete_user.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this doctor?');" style="display:inline;">
                                                    <input type="hidden" name="user_id" value="<?php echo $doctor['id']; ?>">
                                                    <button type="submit" class="action-btn delete">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align: center;">No doctors found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
