<?php
session_start();
require '../auth/db_connect.php';

// Security checks
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin' || !isset($_GET['id'])) {
    header("Location: manage_doctors.php");
    exit();
}

$doctor_id = $_GET['id'];

// Fetch doctor's current details from both tables
$doctor = null;
try {
    $stmt = $conn->prepare(
        "SELECT u.name, u.email, u.phone, d.specialty, d.bio 
         FROM users u 
         LEFT JOIN doctors_details d ON u.id = d.user_id 
         WHERE u.id = ? AND u.role = 'doctor'"
    );
    $stmt->execute([$doctor_id]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$doctor) {
        header("Location: manage_doctors.php");
        exit();
    }
} catch (PDOException $e) {}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/profile-style.css"> <!-- Reusing profile CSS -->
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <!-- Sidebar content -->
        </aside>
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1>Edit Doctor: <?php echo htmlspecialchars($doctor['name']); ?></h1></div>
            </header>
            <section class="profile-section">
                <div class="form-container">
                    <form action="edit_doctor_process.php" method="POST">
                        <input type="hidden" name="doctor_id" value="<?php echo $doctor_id; ?>">
                        <h2>Personal Information</h2>
                        <div class="input-group">
                            <label for="name">Full Name</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($doctor['email']); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="phone">Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
                        </div>
                        <hr>
                        <h2>Professional Details</h2>
                        <div class="input-group">
                            <label for="specialty">Specialty</label>
                            <input type="text" name="specialty" value="<?php echo htmlspecialchars($doctor['specialty'] ?? ''); ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="bio">Biography</label>
                            <textarea name="bio" rows="5"><?php echo htmlspecialchars($doctor['bio'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="submit-btn">Save Changes</button>
                            <a href="manage_doctors.php" class="cancel-btn">Cancel</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
