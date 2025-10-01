<?php
session_start();
require '../auth/db_connect.php';

// Ensure the user is a logged-in patient
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_SESSION['user_id'];
    $record_date = $_POST['record_date'];
    $record_type = $_POST['record_type'];
    $title = trim($_POST['title']);
    $doctor_name_external = trim($_POST['doctor_name_external']);
    $summary = trim($_POST['summary']);
    $file_path = null;

    // --- File Upload Logic ---
    // Check if a file was uploaded without errors
    if (isset($_FILES['record_file']) && $_FILES['record_file']['error'] == 0) {
        $upload_dir = '../uploads/records/';
        // Create the directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate a unique name for the file to prevent overwrites
        $file_extension = pathinfo($_FILES['record_file']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid('record_', true) . '.' . $file_extension;
        $target_file = $upload_dir . $unique_filename;

        // Move the uploaded file to your target directory
        if (move_uploaded_file($_FILES['record_file']['tmp_name'], $target_file)) {
            $file_path = $target_file;
        } else {
            // Handle file move error
            header("Location: medical_records.php?error=fileupload");
            exit();
        }
    }

    // Basic validation
    if (empty($record_date) || empty($record_type) || empty($title) || empty($doctor_name_external)) {
        header("Location: medical_records.php?error=missingfields");
        exit();
    }

    try {
        $stmt = $conn->prepare(
            "INSERT INTO medical_records 
            (patient_id, record_date, record_type, title, summary, doctor_name_external, file_path, uploaded_by_patient) 
            VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)"
        );
        $stmt->execute([$patient_id, $record_date, $record_type, $title, $summary, $doctor_name_external, $file_path]);
        
        header("Location: medical_records.php?success=uploaded");
        exit();

    } catch (PDOException $e) {
        // Handle database error
        header("Location: medical_records.php?error=dberror");
        exit();
    }
} else {
    header("Location: medical_records.php");
    exit();
}
?>

