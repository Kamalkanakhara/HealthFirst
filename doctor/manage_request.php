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

// Fetch all appointments for the logged-in doctor
$all_appointments = [];
try {
    $stmt = $conn->prepare(
        "SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status, u.name AS patient_name
         FROM appointments a
         JOIN users u ON a.patient_id = u.id
         WHERE a.doctor_id = ?
         ORDER BY a.created_at DESC"
    );
    $stmt->execute([$doctor_id]);
    $all_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Requests - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/doctor_dashboard.css">
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

            /* Responsive Tables */
            table thead {
                display: none;
            }

            table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                padding: 0.5rem;
            }
            
            table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                text-align: right;
                padding: 0.75rem 0.5rem;
                border: none;
            }
            
            table td::before {
                content: attr(data-label);
                font-weight: bold;
                color: #333;
                text-align: left;
                margin-right: 1rem;
            }

            .action-cell, .action-cell form {
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-end;
                gap: 0.5rem;
            }
             .action-btn {
                width: 100%;
                margin-bottom: 5px;
            }
            .action-cell form { flex-direction: column; }
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
                <li class="active"><a href="manage_request.php"><i class="fas fa-tasks"></i> Manage Requests</a></li>
                <li><a href="my_schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
                <li><a href="my_patients.php"><i class="fas fa-users"></i> My Patients</a></li>
                <li><a href="doctor_profile.php"><i class="fas fa-user-md"></i> My Profile</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <div class="main-wrapper">
             <header class="main-header">
                <button id="mobile-menu-btn" class="mobile-menu-toggle"><i class="fas fa-bars"></i></button>
                <div class="header-title">Manage Requests</div>
            </header>

            <main class="main-content">
                <div class="main-header-info">
                    <h1>Manage Appointment Requests</h1>
                    <p>View and respond to all patient appointment requests.</p>
                </div>

                <section class="content-panel">
                    <table>
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Date & Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($all_appointments)): ?>
                                <?php foreach ($all_appointments as $appt): ?>
                                    <tr>
                                        <td data-label="Patient"><?php echo htmlspecialchars($appt['patient_name']); ?></td>
                                        <td data-label="Date & Time"><?php echo date("D, j M Y", strtotime($appt['appointment_date'])) . ' at ' . date("g:i A", strtotime($appt['appointment_time'])); ?></td>
                                        <td data-label="Reason"><?php echo htmlspecialchars($appt['reason']); ?></td>
                                        <td data-label="Status"><span class="status-badge status-<?php echo htmlspecialchars($appt['status']); ?>"><?php echo ucfirst(htmlspecialchars($appt['status'])); ?></span></td>
                                        <td data-label="Action" class="action-cell">
                                            <?php if ($appt['status'] == 'pending'): ?>
                                                <form action="appointment_status.php" method="POST">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                                    <button type="submit" name="status" value="confirmed" class="action-btn confirm">Confirm</button>
                                                    <button type="submit" name="status" value="cancelled" class="action-btn cancel">Cancel</button>
                                                </form>
                                            <?php elseif ($appt['status'] == 'confirmed'): ?>
                                                <form action="appointment_status.php" method="POST">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
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
                                    <td colspan="5" style="text-align: center;">You have no appointment requests.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
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
