<?php
session_start();
require '../auth/db_connect.php';

// Security checks
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data
    $appointment_id = $_POST['appointment_id'];
    $patient_id = $_SESSION['user_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason = trim($_POST['reason']);

    // Validate data
    if (empty($appointment_id) || empty($doctor_id) || empty($appointment_date) || empty($appointment_time)) {
        // Handle error
        header("Location: my_appointments.php");
        exit();
    }
       // --- 3. Convert Time to 24-Hour Format for Database ---
    $appointment_time_24h = date("H:i:s", strtotime($appointment_time));

    try {
        // Update the appointment and reset status to 'pending' for re-confirmation
        $stmt = $conn->prepare(
            "UPDATE appointments 
             SET doctor_id = ?, appointment_date = ?, appointment_time = ?, reason = ?, status = 'pending' 
             WHERE id = ? AND patient_id = ?"
        );
        
        $stmt->execute([$doctor_id, $appointment_date, $appointment_time_24h, $reason, $appointment_id, $patient_id]);
        
        $_SESSION['message'] = "Appointment updated successfully. It is now pending doctor confirmation.";

    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to update appointment.";
    }

    header("Location: my_appointments.php");
    exit();
}
