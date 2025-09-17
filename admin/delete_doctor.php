<?php // delete_doctor.php
session_start();
require '../auth/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctor_id = $_POST['doctor_id'];

    try {
        // The database is set to ON DELETE CASCADE, so deleting from users
        // will automatically delete from doctors_details and appointments.
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'doctor'");
        $stmt->execute([$doctor_id]);
        
        header("Location: manage_doctors.php");
        exit();

    } catch (PDOException $e) {
        // Handle error
        header("Location: manage_doctors.php");
        exit();
    }
}
?>