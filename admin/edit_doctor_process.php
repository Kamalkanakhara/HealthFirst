<?php // edit_doctor_process.php
session_start();
require '../auth/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_id = $_POST['doctor_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $specialty = trim($_POST['specialty']);
    $bio = trim($_POST['bio']);

    try {
        // Update users table
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $doctor_id]);

        // Check if details exist, then update or insert
        $stmt = $conn->prepare("SELECT id FROM doctors_details WHERE user_id = ?");
        $stmt->execute([$doctor_id]);
        if ($stmt->fetch()) {
            $stmt = $conn->prepare("UPDATE doctors_details SET specialty = ?, bio = ? WHERE user_id = ?");
            $stmt->execute([$specialty, $bio, $doctor_id]);
        } else {
            $stmt = $conn->prepare("INSERT INTO doctors_details (user_id, specialty, bio) VALUES (?, ?, ?)");
            $stmt->execute([$doctor_id, $specialty, $bio]);
        }
        
        header("Location: manage_doctors.php");
        exit();

    } catch (PDOException $e) {
        // Handle error
        header("Location: manage_doctors.php");
        exit();
    }
}
?>