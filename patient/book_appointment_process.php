<?php
session_start();
require '../auth/db_connect.php';

// Redirect to login if user is not logged in or not a patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. Get Form Data ---
    $patient_id = $_SESSION['user_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time_str = $_POST['appointment_time']; // e.g., "02:00 PM"
    $reason = trim($_POST['reason']);

    // --- 2. Validate Form Data ---
    if (empty($doctor_id) || empty($appointment_date) || empty($appointment_time_str)) {
        $_SESSION['booking_error'] = "Please select a doctor, date, and time.";
        header("Location: book_appointment.php");
        exit();
    }
    
    // --- 3. Convert Time to 24-Hour Format for Database ---
    $appointment_time_24h = date("H:i:s", strtotime($appointment_time_str));

    // --- 4. Time Validation for Today's Date ---
    $timezone = new DateTimeZone('Asia/Kolkata'); // Change to your server's timezone
    $today = new DateTime("now", $timezone);
    $selected_datetime = new DateTime($appointment_date . ' ' . $appointment_time_24h, $timezone);

    if ($selected_datetime < $today) {
        $_SESSION['booking_error'] = "You cannot book an appointment in the past. Please select a valid time.";
        header("Location: book_appointment.php");
        exit();
    }


    // --- 5. Insert Appointment into Database ---
    try {
        $stmt = $conn->prepare(
            "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        if ($stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time_24h, $reason])) {
            $_SESSION['booking_status_message'] = "Your appointment has been successfully requested! The current status is: <strong>Pending</strong>.";
        } else {
            $_SESSION['booking_error'] = "Failed to book appointment. Please try again.";
        }

    } catch (PDOException $e) {
        $_SESSION['booking_error'] = "A database error occurred. Please try again later.";
    }

    header("Location: book_appointment.php");
    exit();

} else {
    // Redirect if accessed directly
    header("Location: book_appointment.php");
    exit();
}
?>
