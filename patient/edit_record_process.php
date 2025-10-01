<?php
session_start();
require '../auth/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_SESSION['user_id'];
    $record_id = $_POST['record_id'];
    
    // Get new data from form
    $record_date = $_POST['record_date'];
    $record_type = $_POST['record_type'];
    $title = trim($_POST['title']);
    $doctor_name_external = trim($_POST['doctor_name_external']);
    $summary = trim($_POST['summary']);

    // --- Security Check: Verify the record belongs to the patient ---
    $stmt = $conn->prepare("SELECT file_path FROM medical_records WHERE id = ? AND patient_id = ? AND uploaded_by_patient = TRUE");
    $stmt->execute([$record_id, $patient_id]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        // Record not found or doesn't belong to the user
        header("Location: medical_records.php?error=unauthorized");
        exit();
    }

    $old_file_path = $record['file_path'];
    $new_file_path = $old_file_path;

    // --- File Update Logic ---
    if (isset($_FILES['record_file']) && $_FILES['record_file']['error'] == 0) {
        $upload_dir = '../uploads/records/';
        
        // Delete the old file if it exists
        if (!empty($old_file_path) && file_exists('../' . $old_file_path)) {
            unlink('../' . $old_file_path);
        }

        // Upload the new file
        $file_extension = pathinfo($_FILES['record_file']['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid('record_', true) . '.' . $file_extension;
        $target_file = $upload_dir . $unique_filename;

        if (move_uploaded_file($_FILES['record_file']['tmp_name'], $target_file)) {
            $new_file_path = $target_file;
        } else {
            header("Location: medical_records.php?error=fileupload");
            exit();
        }
    }

    // --- Database Update ---
    try {
        $update_stmt = $conn->prepare(
            "UPDATE medical_records SET 
                record_date = ?, 
                record_type = ?, 
                title = ?, 
                summary = ?, 
                doctor_name_external = ?, 
                file_path = ? 
            WHERE id = ? AND patient_id = ?"
        );
        $update_stmt->execute([$record_date, $record_type, $title, $summary, $doctor_name_external, $new_file_path, $record_id, $patient_id]);
        
        header("Location: medical_records.php?success=updated");
        exit();

    } catch (PDOException $e) {
        header("Location: medical_records.php?error=dberror");
        exit();
    }

} else {
    header("Location: medical_records.php");
    exit();
}
?>
