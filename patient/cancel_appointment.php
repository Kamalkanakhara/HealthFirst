<?php
session_start();
require '../auth/db_connect.php';

// Security check: ensure user is a logged-in patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $appointment_id = $_POST['appointment_id'];
    $patient_id = $_SESSION['user_id'];

    if (empty($appointment_id)) {
        // Handle error, maybe set a session message
        header("Location: my_appointments.php");
        exit();
    }

    try {
        // Additional security: ensure the appointment belongs to this patient before cancelling
        $stmt = $conn->prepare(
            "UPDATE appointments SET status = 'cancelled' WHERE id = ? AND patient_id = ?"
        );
        
        $stmt->execute([$appointment_id, $patient_id]);
        
        // Optionally, you can set a success message here
        $_SESSION['message'] = "Appointment cancelled successfully.";

    } catch (PDOException $e) {
        // Optionally, set an error message
        $_SESSION['error'] = "Failed to cancel appointment.";
    }

    // Redirect back to the appointments page
    header("Location: my_appointments.php");
    exit();

} else {
    // Redirect if accessed directly
    header("Location: my_appointments.php");
    exit();
}
?>
