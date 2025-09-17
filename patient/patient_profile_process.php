<?php
session_start();
require '../auth/db_connect.php';

// Security check: ensure user is a logged-in patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. Get Form Data ---
    $patient_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // --- 2. Validate Basic Info ---
    if (empty($name) || empty($email) || empty($phone)) {
        $_SESSION['profile_error'] = "Name, email, and phone number are required.";
        header("Location: patient_profile.php");
        exit();
    }

    // --- 3. Update Basic Information ---
    try {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $patient_id]);
        
        // Update session name in case it changed
        $_SESSION['user_name'] = $name;

    } catch (PDOException $e) {
        $_SESSION['profile_error'] = "Error updating profile. The email may already be in use.";
        header("Location: patient_profile.php");
        exit();
    }

    // --- 4. Handle Password Change ---
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $_SESSION['profile_error'] = "New passwords do not match.";
            header("Location: patient_profile.php");
            exit();
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $patient_id]);
        } catch (PDOException $e) {
            $_SESSION['profile_error'] = "Error updating password.";
            header("Location: patient_profile.php");
            exit();
        }
    }

    $_SESSION['profile_success'] = "Your profile has been updated successfully!";
    header("Location: patient_profile.php");
    exit();

} else {
    // Redirect if accessed directly
    header("Location: patient_profile.php");
    exit();
}
?>
