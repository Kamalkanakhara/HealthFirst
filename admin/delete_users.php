<?php
session_start();
require '../auth/db_connect.php';

// Security check: ensure user is a logged-in admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user_id_to_delete = $_POST['user_id'];

    if (empty($user_id_to_delete)) {
        header("Location: manage_users.php");
        exit();
    }

    try {
        // The database is set to ON DELETE CASCADE for appointments and doctor_details,
        // so deleting a user from the `users` table will automatically remove
        // all associated records in those tables.
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id_to_delete]);
        
        $_SESSION['message'] = "User deleted successfully.";

    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to delete user.";
    }

    header("Location: manage_users.php");
    exit();
}
