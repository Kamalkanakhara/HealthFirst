<?php
// Start the session to handle messages
session_start();

// Include your database connection file
require 'db_connect.php'; 

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. Get and Sanitize Form Data ---
    // The 'phone' field is assumed to be in your form now, as per previous requests.
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : ''; // Handle phone number
    $role = $_POST['role'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // --- 2. Validate Form Data ---
    if (empty($name) || empty($email) || empty($role) || empty($password) || empty($confirm_password)) {
        $_SESSION['register_error'] = "All fields except phone number are required.";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Invalid email format.";
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = "Passwords do not match.";
        header("Location: register.php");
        exit();
    }
    
    // --- 3. Check for Existing User ---
    try {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['register_error'] = "An account with this email already exists.";
            header("Location: register.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['register_error'] = "Database error. Please try again later.";
        header("Location: register.php");
        exit();
    }


    // --- 4. Insert New User into Database ---
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Note: The phone number is included in the INSERT statement
        $stmt = $conn->prepare("INSERT INTO users (name, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $phone, $role, $hashed_password])) {
            // On success, redirect to login page with a success message
            $_SESSION['success_message'] = "Registration successful! You can now log in.";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['register_error'] = "Registration failed. Please try again.";
            header("Location: register.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['register_error'] = "Database error during registration. Please try again later.";
        // For debugging: error_log($e->getMessage());
        header("Location: register.php");
        exit();
    }

} else {
    // If the page is accessed directly without a POST request, redirect to the registration page
    header("Location: register.php");
    exit();
}
?>
