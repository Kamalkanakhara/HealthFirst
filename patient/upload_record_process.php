<?php
session_start();
require '../auth/db_connect.php';

// Security check: ensure user is a logged-in patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- 1. Get Form Data ---
    $patient_id = $_SESSION['user_id'];
    $record_date = $_POST['record_date'];
    $record_type = $_POST['record_type'];
    $title = trim($_POST['title']);
    $doctor_name_external = trim($_POST['doctor_name_external']);
    $summary = trim($_POST['summary']);

    // --- 2. Validate Required Fields ---
    if (empty($record_date) || empty($record_type) || empty($title) || empty($doctor_name_external)) {
        $_SESSION['record_error'] = "Please fill in all required fields.";
        header("Location: medical_records.php");
        exit();
    }

    $file_path = null;

    // --- 3. Handle File Upload ---
    if (isset($_FILES['record_file']) && $_FILES['record_file']['error'] == UPLOAD_ERR_OK) {
        
        $upload_dir = '../uploads/records/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name = basename($_FILES['record_file']['name']);
        // Sanitize filename and create a unique name to prevent overwriting
        $safe_file_name = preg_replace("/[^a-zA-Z0-9\._-]/", "", $file_name);
        $unique_file_name = time() . '_' . uniqid() . '_' . $safe_file_name;
        $target_file = $upload_dir . $unique_file_name;

        if (move_uploaded_file($_FILES['record_file']['tmp_name'], $target_file)) {
            // Store the relative path for database
            $file_path = 'uploads/records/' . $unique_file_name;
        } else {
            $_SESSION['record_error'] = "Sorry, there was an error uploading your file.";
            header("Location: medical_records.php");
            exit();
        }
    }

    // --- 4. Insert into Database ---
    try {
        $stmt = $conn->prepare(
            "INSERT INTO medical_records (patient_id, record_date, record_type, title, summary, doctor_name_external, file_path, uploaded_by_patient) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)" // Set uploaded_by_patient to TRUE (1)
        );
        
        if ($stmt->execute([$patient_id, $record_date, $record_type, $title, $summary, $doctor_name_external, $file_path])) {
            $_SESSION['record_success'] = "Medical record added successfully!";
        } else {
            $_SESSION['record_error'] = "Failed to add record. Please try again.";
        }

    } catch (PDOException $e) {
        // In a real app, you might want to log the error instead of showing it to the user
        $_SESSION['record_error'] = "A database error occurred.";
    }

    header("Location: medical_records.php");
    exit();

} else {
    // Redirect if accessed directly
    header("Location: medical_records.php");
    exit();
}
?>

