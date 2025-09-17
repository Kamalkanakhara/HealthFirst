<?php
// Start the session to manage user login state
session_start();

// Include your database connection file
require 'db_connect.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. Get and Sanitize Form Data ---
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // --- 2. Validate Form Data ---
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Email and password are required.";
        header("Location: login.php");
        exit();
    }

    // --- 3. Authenticate User ---
    try {
        // Query user by email only
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify the password
            if (password_verify($password, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // --- 4. Redirect based on role fetched from the database ---
                if ($user['role'] == 'admin') {
                    header("Location: ../admin/dashboard.php");
                    exit();
                } elseif ($user['role'] == 'doctor') {
                    header("Location: ../doctor/dashboard.php");
                    exit();
                } else { // Assumes 'patient' is the other role
                    header("Location: ../patient/dashboard.php");
                    exit();
                }

            } else {
                // Incorrect password
                $_SESSION['login_error'] = "Invalid email or password.";
                header("Location: login.php");
                exit();
            }
        } else {
            // No user found with the given email
            $_SESSION['login_error'] = "No account found with that email address.";
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['login_error'] = "Database error. Please try again later.";
        header("Location: login.php");
        exit();
    }

} else {
    // If the page is accessed directly, redirect to the login page
    header("Location: login.php");
    exit();
}
?>
