<?php
session_start();
require '../auth/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctor_id = $_SESSION['user_id'];
    $patient_id = $_POST['patient_id'];
    $record_date = $_POST['record_date'];
    $record_type = $_POST['record_type'];
    $title = trim($_POST['title']);
    $summary = trim($_POST['summary']);

    if (empty($patient_id) || empty($record_date) || empty($record_type) || empty($title)) {
        // Handle error: redirect back with an error message
        header("Location: add_medical_record.php?patient_id=$patient_id&error=missingfields");
        exit();
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO medical_records (patient_id, doctor_id, record_date, record_type, title, summary) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$patient_id, $doctor_id, $record_date, $record_type, $title, $summary]);
        
        // Redirect to the patient's record page on success
        header("Location: patient_medical_records.php?patient_id=$patient_id&success=recordadded");
        exit();

    } catch (PDOException $e) {
         // Handle error: redirect back with a database error message
        header("Location: add_medical_record.php?patient_id=$patient_id&error=dberror");
        exit();
    }
} else {
    header("Location: my_patients.php");
    exit();
}
