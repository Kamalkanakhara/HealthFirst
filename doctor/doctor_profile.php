<?php
session_start();
require '../auth/db_connect.php';

// Redirect to login if user is not logged in or not a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctor_id = $_SESSION['user_id'];
$doctor_name = $_SESSION['user_name'];

// Fetch existing doctor details if they exist
$details = null;
try {
    $stmt = $conn->prepare("SELECT specialty, bio, availability FROM doctors_details WHERE user_id = ?");
    $stmt->execute([$doctor_id]);
    $details = $stmt->fetch(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../assets/css/doctor_dashboard.css">
    <link rel="stylesheet" href="../assets/css/profile-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Responsive Design */
        .main-wrapper {
            display: flex;
            flex-direction: column;
            width: 100%;
            margin-left: 250px;
            transition: margin-left 0.3s ease-in-out;
            min-height: 100vh;
        }

        .main-header {
            display: none;
            background: #fff;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 998;
        }

        .mobile-menu-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #333;
            margin-right: 1rem;
        }

        .header-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .close-sidebar-toggle {
            display: none;
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 2.5rem;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.8);
            cursor: pointer;
            line-height: 1;
        }

        .sidebar {
            transition: transform 0.3s ease-in-out;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100%;
                z-index: 1000;
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0;
            }

            .main-header {
                display: flex;
            }
            
            .close-sidebar-toggle {
                display: block;
            }
            
            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <button id="close-sidebar-btn" class="close-sidebar-toggle">&times;</button>
            <div class="sidebar-header">
                <i class="fas fa-heartbeat"></i>
                <span>HealthFirst</span>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="manage_request.php"><i class="fas fa-tasks"></i> Manage Requests</a></li>
                <li><a href="my_schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
                <li><a href="my_patients.php"><i class="fas fa-users"></i> My Patients</a></li>
                <li class="active"><a href="doctor_profile.php"><i class="fas fa-user-md"></i> My Profile</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <div class="main-wrapper">
            <header class="main-header">
                <button id="mobile-menu-btn" class="mobile-menu-toggle"><i class="fas fa-bars"></i></button>
                <div class="header-title">My Profile</div>
            </header>

            <main class="main-content">
                <div class="main-header-info">
                    <h1>My Professional Profile</h1>
                    <p>Keep your professional details up-to-date for your patients.</p>
                </div>

                <div class="profile-form-container">
                    <form action="doctor_profile_process.php" method="POST">
                        <?php if (isset($_SESSION['profile_success'])): ?>
                            <div class="status-message success"><?php echo $_SESSION['profile_success']; unset($_SESSION['profile_success']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['profile_error'])): ?>
                            <div class="status-message error"><?php echo $_SESSION['profile_error']; unset($_SESSION['profile_error']); ?></div>
                        <?php endif; ?>

                        <div class="input-group">
                            <label for="specialty">Your Specialty</label>
                            <input type="text" id="specialty" name="specialty" value="<?php echo htmlspecialchars($details['specialty'] ?? ''); ?>" placeholder="e.g., Cardiology, Dermatology" required>
                        </div>

                        <div class="input-group">
                            <label for="bio">Biography</label>
                            <textarea id="bio" name="bio" rows="5" placeholder="Write a short bio about your experience and expertise..."><?php echo htmlspecialchars($details['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="input-group">
                            <label for="availability">Availability</label>
                            <textarea id="availability" name="availability" rows="3" placeholder="e.g., Mon-Fri, 9 AM - 5 PM"><?php echo htmlspecialchars($details['availability'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="submit-btn">Save Changes</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const closeSidebarBtn = document.getElementById('close-sidebar-btn');
            const sidebar = document.querySelector('.sidebar');

            if (mobileMenuBtn && sidebar) {
                mobileMenuBtn.addEventListener('click', function(event) {
                    event.stopPropagation();
                    sidebar.classList.add('open');
                });
            }

            if (closeSidebarBtn && sidebar) {
                closeSidebarBtn.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                });
            }
            
            document.addEventListener('click', function(event) {
                if (sidebar && sidebar.classList.contains('open') && !sidebar.contains(event.target)) {
                    sidebar.classList.remove('open');
                }
            });
        });
    </script>
</body>
</html>
