<?php
session_start();
require '../auth/db_connect.php';

// Security check: ensure user is a logged-in doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $appointment_id = $_POST['appointment_id'];
    $new_status = $_POST['status'];
    $doctor_id = $_SESSION['user_id'];

    // Validate the new status to be one of the allowed values
    if ($new_status == 'confirmed' || $new_status == 'cancelled' || $new_status == 'completed') {
        
        try {
            // Additional security: ensure the appointment belongs to this doctor before updating
            $stmt = $conn->prepare(
                "UPDATE appointments SET status = ? WHERE id = ? AND doctor_id = ?"
            );
            
            if ($stmt->execute([$new_status, $appointment_id, $doctor_id])) {
                $_SESSION['status_update_success'] = "Appointment status updated successfully.";
            } else {
                $_SESSION['status_update_error'] = "Failed to update status.";
            }

        } catch (PDOException $e) {
            $_SESSION['status_update_error'] = "Database error.";
        }
    } else {
        $_SESSION['status_update_error'] = "Invalid status value.";
    }

    // Redirect back to the manage requests page
    header("Location: manage_request.php");
    exit();
} else {
    // If accessed directly without POST, redirect to dashboard
    header("Location: dashboard.php");
    exit();
}
