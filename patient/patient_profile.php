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

// Fetch current patient details
$patient = null;
try {
    $stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/profile-style.css">
    <link rel="stylesheet" href="../assets/css/status-message.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header"><a href="../homepage.php" class="logo">HealthFirst</a></div>
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
                <div class="header-left"><h1>My Profile</h1></div>
            </header>

            <section class="profile-section">
                <div class="form-container">
                    <form action="patient_profile_process.php" method="POST">
                        
                        <?php if (isset($_SESSION['profile_success'])): ?>
                            <div class="status-message success"><?php echo $_SESSION['profile_success']; unset($_SESSION['profile_success']); ?></div>
                        <?php elseif (isset($_SESSION['profile_error'])): ?>
                            <div class="status-message error"><?php echo $_SESSION['profile_error']; unset($_SESSION['profile_error']); ?></div>
                        <?php endif; ?>

                        <h2>Personal Information</h2>
                        <div class="input-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($patient['name'] ?? ''); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($patient['email'] ?? ''); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($patient['phone'] ?? ''); ?>" required>
                        </div>

                        <hr>

                        <h2>Change Password</h2>
                        <div class="input-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Leave blank to keep current password">
                        </div>
                        <div class="input-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your new password">
                        </div>

                        <button type="submit" class="submit-btn">Update Profile</button>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
