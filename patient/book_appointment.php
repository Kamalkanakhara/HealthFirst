<?php
session_start();
require '../auth/db_connect.php';

// Redirect to login if user is not logged in or not a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$user_name = $_SESSION['user_name'];

// Check for a pre-selected doctor ID from the URL
$selected_doctor_id = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : null;

// Fetch all doctors from the database
try {
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE role = 'doctor'");
    $stmt->execute();
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $doctors = [];
    $booking_error = "Could not fetch doctor list.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/appointment_calender.css">
    <link rel="stylesheet" href="../assets/css/status-message.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header"><a href="#" class="logo">HealthFirst</a></div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a href="view_doctors.php" class="nav-item"><i class="fas fa-user-md"></i><span>View Doctors</span></a>
                <a href="book_appointment.php" class="nav-item active"><i class="fas fa-calendar-plus"></i><span>Book Appointment</span></a>
                <a href="my_appointments.php" class="nav-item"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
                <a href="patient_profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1>Schedule Your Visit</h1></div>
            </header>

            <section class="booking-interface">
                <form action="book_appointment_process.php" method="POST" id="booking-form">
                    <div class="booking-details">
                        <h2>Appointment Details</h2>
                        
                        <div class="input-group">
                            <label for="doctor_select">Doctor</label>
                            <select name="doctor_id" id="doctor_select" required>
                                <option value="" disabled <?php if ($selected_doctor_id === null) echo 'selected'; ?>>Choose a doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                    <option value="<?php echo $doctor['id']; ?>" <?php if ($doctor['id'] == $selected_doctor_id) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($doctor['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="input-group">
                            <label for="reason">Reason for Visit</label>
                            <textarea name="reason" id="reason" rows="4" placeholder="Briefly describe the reason for your visit..."></textarea>
                        </div>
                    </div>

                    <div class="booking-calendar-time">
                        <div class="calendar-container">
                            <div class="calendar-header">
                                <button type="button" class="nav-btn" id="prev-month-btn"><i class="fas fa-chevron-left"></i></button>
                                <h3 id="month-year"></h3>
                                <button type="button" class="nav-btn" id="next-month-btn"><i class="fas fa-chevron-right"></i></button>
                            </div>
                            <div class="calendar-weekdays">
                                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                            </div>
                            <div class="calendar-grid" id="calendar-grid"></div>
                        </div>
                        <input type="hidden" name="appointment_date" id="selected_date" required>
                        
                        <div class="time-slot-container" id="time-slot-container">
                            <h4>Available Slots</h4>
                            <div class="time-slots" id="time-slots">
                                <p class="placeholder">Please select a date to see available times.</p>
                            </div>
                        </div>
                        <input type="hidden" name="appointment_time" id="selected_time" required>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submit-btn">Confirm Booking</button>
                </form>
            </section>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthYearEl = document.getElementById('month-year');
    const calendarGrid = document.getElementById('calendar-grid');
    const prevMonthBtn = document.getElementById('prev-month-btn');
    const nextMonthBtn = document.getElementById('next-month-btn');
    const selectedDateInput = document.getElementById('selected_date');
    const timeSlotsContainer = document.getElementById('time-slots');
    const selectedTimeInput = document.getElementById('selected_time');

    let currentDate = new Date();
    currentDate.setDate(1);

    function renderCalendar() {
        calendarGrid.innerHTML = '';
        const month = currentDate.getMonth();
        const year = currentDate.getFullYear();
        
        monthYearEl.textContent = `${currentDate.toLocaleString('default', { month: 'long' })} ${year}`;

        const firstDayOfMonth = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        for (let i = 0; i < firstDayOfMonth; i++) {
            calendarGrid.insertAdjacentHTML('beforeend', '<div class="calendar-day empty"></div>');
        }

        for (let i = 1; i <= daysInMonth; i++) {
            const dayCell = document.createElement('div');
            dayCell.classList.add('calendar-day');
            dayCell.textContent = i;
            const cellDateISO = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            dayCell.dataset.date = cellDateISO;

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const cellDate = new Date(year, month, i);
            if (cellDate < today) {
                dayCell.classList.add('disabled');
            }

            dayCell.addEventListener('click', function() {
                if (this.classList.contains('disabled')) return;

                document.querySelectorAll('.calendar-day.selected').forEach(d => d.classList.remove('selected'));
                this.classList.add('selected');
                selectedDateInput.value = this.dataset.date;
                
                renderTimeSlots(this.dataset.date);
            });
            calendarGrid.appendChild(dayCell);
        }
    }

    function renderTimeSlots(date) {
        timeSlotsContainer.innerHTML = '<div class="loader"></div>';
        selectedTimeInput.value = '';
        
        setTimeout(() => {
            timeSlotsContainer.innerHTML = '';
            const allTimes = ['09:00 AM', '09:30 AM', '10:00 AM', '11:30 AM', '02:00 PM', '02:30 PM', '03:30 PM', '04:00 PM', '05:00 PM'];
            
            const now = new Date();
            const todayISO = now.toISOString().split('T')[0];

            const availableTimes = allTimes.filter(time => {
                if (date > todayISO) {
                    return true; // All times are available for future dates
                }
                // For today, filter out past times
                const [hour, minute] = time.split(/:| /);
                const appointmentTime = new Date();
                appointmentTime.setHours(parseInt(hour) + (time.includes('PM') && hour != 12 ? 12 : 0), parseInt(minute), 0, 0);
                return appointmentTime > now;
            });

            if (availableTimes.length === 0) {
                timeSlotsContainer.innerHTML = '<p class="placeholder">No available time slots for this day.</p>';
                return;
            }

            availableTimes.forEach(time => {
                const timeSlot = document.createElement('div');
                timeSlot.classList.add('time-slot');
                timeSlot.textContent = time;
                timeSlot.dataset.time = time;

                timeSlot.addEventListener('click', function() {
                    document.querySelectorAll('.time-slot.selected').forEach(t => t.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedTimeInput.value = this.dataset.time;
                });
                timeSlotsContainer.appendChild(timeSlot);
            });
        }, 500);
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
});
</script>
</body>
</html>
