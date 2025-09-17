<?php
session_start();
require './auth/db_connect.php'; // Make sure the path to your db_connect.php file is correct

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. Get and Sanitize Form Data ---
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // --- 2. Validate Form Data ---
    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['contact_error'] = "All fields are required.";
        header("Location: contact.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_error'] = "Please enter a valid email address.";
        header("Location: contact.php");
        exit();
    }

    // --- 3. Insert Message into Database ---
    try {
        $stmt = $conn->prepare(
            "INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)"
        );
        
        if ($stmt->execute([$name, $email, $message])) {
            $_SESSION['contact_success'] = "Thank you for your message! We will get back to you shortly.";
        } else {
            $_SESSION['contact_error'] = "Failed to send message. Please try again.";
        }

    } catch (PDOException $e) {
        $_SESSION['contact_error'] = "A database error occurred. Please try again later.";
    }

    header("Location: contact.php");
    exit();

} else {
    // Redirect if accessed directly
    header("Location: contact.php");
    exit();
}
?>
