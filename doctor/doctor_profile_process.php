<?php
session_start();
require '../auth/db_connect.php';

// Redirect to login if user is not logged in or not a doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. Get Form Data ---
    $doctor_id = $_SESSION['user_id'];
    $specialty = trim($_POST['specialty']);
    $bio = trim($_POST['bio']);
    $availability = trim($_POST['availability']);

    // --- 2. Validate Form Data ---
    if (empty($specialty)) {
        $_SESSION['profile_error'] = "Specialty is a required field.";
        header("Location: doctor_profile.php");
        exit();
    }

    // --- 3. Check if details already exist for this doctor ---
    try {
        $stmt = $conn->prepare("SELECT id FROM doctors_details WHERE user_id = ?");
        $stmt->execute([$doctor_id]);
        $existing_details = $stmt->fetch();

        if ($existing_details) {
            // --- 4a. UPDATE existing details ---
            $update_stmt = $conn->prepare(
                "UPDATE doctors_details SET specialty = ?, bio = ?, availability = ? WHERE user_id = ?"
            );
            if ($update_stmt->execute([$specialty, $bio, $availability, $doctor_id])) {
                $_SESSION['profile_success'] = "Your profile has been updated successfully!";
            } else {
                $_SESSION['profile_error'] = "Failed to update profile. Please try again.";
            }
        } else {
            // --- 4b. INSERT new details ---
            $insert_stmt = $conn->prepare(
                "INSERT INTO doctors_details (user_id, specialty, bio, availability) VALUES (?, ?, ?, ?)"
            );
            if ($insert_stmt->execute([$doctor_id, $specialty, $bio, $availability])) {
                $_SESSION['profile_success'] = "Your profile details have been saved successfully!";
            } else {
                $_SESSION['profile_error'] = "Failed to save profile details. Please try again.";
            }
        }

    } catch (PDOException $e) {
        $_SESSION['profile_error'] = "A database error occurred. Please try again later.";
    }

    header("Location: doctor_profile.php");
    exit();

} else {
    // Redirect if accessed directly
    header("Location: dashboard.php");
    exit();
}
?>
