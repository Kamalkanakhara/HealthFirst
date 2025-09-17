<?php
session_start();
require '../auth/db_connect.php';

// Security check: ensure user is a logged-in admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $appointment_id = $_POST['appointment_id'];
    $new_status = $_POST['status'];
    // Use the 'redirect_to' value sent from the form for flexibility
    $redirect_page = $_POST['redirect_to'] ?? 'dashboard.php'; 

    // Validate the new status
    if ($new_status == 'confirmed' || $new_status == 'cancelled' || $new_status == 'completed') {
        
        try {
            // The query updates the status based on the appointment ID.
            $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $appointment_id]);

            $_SESSION['message'] = "Appointment status updated successfully.";

        } catch (PDOException $e) {
            $_SESSION['error'] = "Failed to update appointment status.";
        }
    } else {
        $_SESSION['error'] = "Invalid status provided.";
    }

    // Redirect back to the page the request came from
    header("Location: " . $redirect_page);
    exit();

} else {
    // Redirect if accessed directly
    header("Location: dashboard.php");
    exit();
}
?>
