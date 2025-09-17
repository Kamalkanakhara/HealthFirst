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
$appointments_data = [];
try {
    $stmt = $conn->prepare(
        "SELECT a.appointment_date, a.appointment_time, a.status, u.name AS patient_name
         FROM appointments a
         JOIN users u ON a.patient_id = u.id
         WHERE a.doctor_id = ?"
    );
    $stmt->execute([$doctor_id]);
    $appointments_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/doctor_dashboard.css">
    <link rel="stylesheet" href="../assets/css/my_schedule.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
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
                <li class="active"><a href="my_schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
                <li><a href="my_patients.php"><i class="fas fa-users"></i> My Patients</a></li>
                <li><a href="doctor_profile.php"><i class="fas fa-user-md"></i> My Profile</a></li>
                <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <div class="main-wrapper">
             <header class="main-header">
                <button id="mobile-menu-btn" class="mobile-menu-toggle"><i class="fas fa-bars"></i></button>
                <div class="header-title">My Schedule</div>
            </header>

            <main class="main-content">
                <div class="main-header-info">
                    <h1>My Schedule</h1>
                    <p>View your upcoming appointments at a glance.</p>
                </div>
                
                <div class="schedule-container">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <button id="prev-month-btn"><i class="fas fa-chevron-left"></i></button>
                            <h2 id="current-month-year"></h2>
                            <button id="next-month-btn"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div class="calendar-weekdays">
                            <div>Sun</div>
                            <div>Mon</div>
                            <div>Tue</div>
                            <div>Wed</div>
                            <div>Thu</div>
                            <div>Fri</div>
                            <div>Sat</div>
                        </div>
                        <div id="calendar-days" class="calendar-days-grid">
                            <!-- Calendar days will be injected here by JavaScript -->
                        </div>
                    </div>
                    <div class="appointments-display">
                        <h3 id="selected-date-header">Select a date</h3>
                        <div id="appointments-list">
                            <!-- Appointments will be injected here by JS -->
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
    const appointmentsData = <?php echo json_encode($appointments_data); ?>;
    
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
            if (sidebar && sidebar.classList.contains('open') && !sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });

        // Calendar Logic
        const monthYearEl = document.getElementById('current-month-year');
        const calendarDaysEl = document.getElementById('calendar-days');
        const prevMonthBtn = document.getElementById('prev-month-btn');
        const nextMonthBtn = document.getElementById('next-month-btn');
        const selectedDateHeader = document.getElementById('selected-date-header');
        const appointmentsList = document.getElementById('appointments-list');

        let currentDate = new Date();

        // Helper function to format a date as YYYY-MM-DD without timezone issues
        function toLocalDateString(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function renderCalendar() {
            calendarDaysEl.innerHTML = '';
            const month = currentDate.getMonth();
            const year = currentDate.getFullYear();
            
            monthYearEl.textContent = `${currentDate.toLocaleString('default', { month: 'long' })} ${year}`;
            
            const firstDayOfMonth = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDayOfMonth; i++) {
                calendarDaysEl.insertAdjacentHTML('beforeend', `<div class="calendar-day empty"></div>`);
            }

            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateString = toLocalDateString(date); // Use helper function
                const today = new Date();
                today.setHours(0,0,0,0);

                let dayClass = 'calendar-day';
                if (date < today) dayClass += ' past';
                if (date.getTime() === today.getTime()) dayClass += ' today';
                
                const hasAppointment = appointmentsData.some(appt => appt.appointment_date === dateString && (appt.status === 'confirmed' || appt.status === 'pending'));

                if(hasAppointment) {
                    dayClass += ' has-appointment';
                }

                const dayEl = `<div class="${dayClass}" data-date="${dateString}">${day}</div>`;
                calendarDaysEl.insertAdjacentHTML('beforeend', dayEl);
            }

            document.querySelectorAll('.calendar-day:not(.empty)').forEach(day => {
                day.addEventListener('click', (e) => {
                    const targetDay = e.target.closest('.calendar-day');
                    if (!targetDay) return;

                    document.querySelectorAll('.calendar-day.selected').forEach(d => d.classList.remove('selected'));
                    targetDay.classList.add('selected');
                    displayAppointmentsForDate(targetDay.dataset.date);
                });
            });
        }
        
        function displayAppointmentsForDate(dateString) {
            // Add 'T00:00:00' to ensure the date is parsed in the local timezone consistently
            const date = new Date(dateString + 'T00:00:00');
            selectedDateHeader.textContent = `Appointments for ${date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}`;
            
            const appointmentsForDay = appointmentsData.filter(appt => appt.appointment_date === dateString);
            
            appointmentsList.innerHTML = '';
            
            if (appointmentsForDay.length === 0) {
                appointmentsList.innerHTML = '<p class="no-appointments">No appointments scheduled for this day.</p>';
                return;
            }

            appointmentsForDay.sort((a, b) => a.appointment_time.localeCompare(b.appointment_time));

            appointmentsForDay.forEach(appt => {
                const time = new Date(`1970-01-01T${appt.appointment_time}`).toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                const appointmentItem = `
                    <div class="appointment-item status-${appt.status}">
                        <div class="appointment-time">${time}</div>
                        <div class="appointment-details">
                            <div class="patient-name">${appt.patient_name}</div>
                            <div class="appointment-status">${appt.status}</div>
                        </div>
                    </div>
                `;
                appointmentsList.insertAdjacentHTML('beforeend', appointmentItem);
            });
        }

        prevMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        nextMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        renderCalendar();
        const todayString = toLocalDateString(new Date()); // Use helper function
        const todayCell = document.querySelector(`.calendar-day[data-date="${todayString}"]`);
        if (todayCell) {
            todayCell.click();
        } else {
            const firstDayOfMonth = document.querySelector('.calendar-day:not(.empty)');
            if (firstDayOfMonth) {
                firstDayOfMonth.click();
            } else {
                 displayAppointmentsForDate(new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).toISOString().split('T')[0]);
            }
        }
    });
    </script>
</body>
</html>

