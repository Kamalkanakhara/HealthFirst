<?php
session_start();
require '../auth/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$medical_records = [];
try {
    $stmt = $conn->prepare(
        "SELECT 
            r.id, 
            r.record_type, 
            r.title, 
            r.record_date, 
            r.summary, 
            r.file_path,
            r.uploaded_by_patient,
            r.doctor_name_external,
            COALESCE(u.name, r.doctor_name_external, 'Self-Uploaded') AS author_name
         FROM medical_records r
         LEFT JOIN users u ON r.doctor_id = u.id
         WHERE r.patient_id = ?
         ORDER BY r.record_date DESC"
    );
    $stmt->execute([$patient_id]);
    $medical_records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Optional: handle database error
}

function getIconForRecordType($type) {
    switch (strtolower($type)) {
        case 'consultation': return 'fa-stethoscope';
        case 'lab result': return 'fa-vial';
        case 'prescription': return 'fa-prescription-bottle-alt';
        default: return 'fa-file-medical';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Records - HealthFirst</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/medical_records.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header"><a href="../homepage.php" class="logo">HealthFirst</a></div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
                <a href="view_doctors.php" class="nav-item"><i class="fas fa-user-md"></i><span>View Doctors</span></a>
                <a href="book_appointment.php" class="nav-item"><i class="fas fa-calendar-plus"></i><span>Book Appointment</span></a>
                <a href="my_appointments.php" class="nav-item"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
                <a href="medical_records.php" class="nav-item active"><i class="fas fa-file-medical"></i><span>Medical Records</span></a>
                <a href="patient_profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Profile</span></a>
                <a href="../auth/logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h1>Your Medical History</h1></div>
                 <div class="header-right">
                    <button class="upload-btn" id="upload-record-btn"><i class="fas fa-upload"></i> Upload Record</button>
                </div>
            </header>

            <section class="records-timeline">
                <?php if (!empty($medical_records)): ?>
                    <?php foreach ($medical_records as $record): ?>
                        <div class="timeline-item">
                            <div class="timeline-icon record-type-<?php echo strtolower(str_replace(' ', '-', $record['record_type'])); ?>">
                                <i class="fas <?php echo getIconForRecordType($record['record_type']); ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <span class="record-date"><?php echo date("F d, Y", strtotime($record['record_date'])); ?></span>
                                <h3><?php echo htmlspecialchars($record['title']); ?></h3>
                                <p class="record-doctor">with <?php echo htmlspecialchars($record['author_name']); ?></p>
                                <p class="record-summary"><?php echo htmlspecialchars($record['summary']); ?></p>
                                <div class="record-actions">
                                    <?php if (!empty($record['file_path'])): ?>
                                        <a href="../<?php echo htmlspecialchars($record['file_path']); ?>" class="action-btn download" download><i class="fas fa-download"></i> Download File</a>
                                    <?php endif; ?>
                                    <?php if ($record['uploaded_by_patient']): ?>
                                        <button class="action-btn edit" 
                                            data-record-id="<?php echo $record['id']; ?>"
                                            data-date="<?php echo $record['record_date']; ?>"
                                            data-type="<?php echo $record['record_type']; ?>"
                                            data-title="<?php echo htmlspecialchars($record['title']); ?>"
                                            data-doctor="<?php echo htmlspecialchars($record['doctor_name_external']); ?>"
                                            data-summary="<?php echo htmlspecialchars($record['summary']); ?>">
                                            <i class="fas fa-pencil-alt"></i> Edit
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-records-card">
                        <i class="fas fa-folder-open"></i>
                        <h2>No Medical Records Found</h2>
                        <p>Your uploaded and shared medical records will appear here.</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

      <!-- Upload Record Modal -->
    <div id="upload-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button id="close-upload-modal-btn" class="close-btn">&times;</button>
            <h2>Upload a New Medical Record</h2>
            <form action="upload_record_process.php" method="POST" class="modal-form" enctype="multipart/form-data">
                <div class="input-group">
                    <label for="record_date">Date of Record</label>
                    <input type="date" id="record_date" name="record_date" required>
                </div>
                <div class="input-group">
                    <label for="record_type">Type of Record</label>
                    <select id="record_type" name="record_type" required>
                        <option value="Consultation">Consultation Note</option>
                        <option value="Lab Result">Lab Result</option>
                        <option value="Prescription">Prescription</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="title">Title / Subject</label>
                    <input type="text" id="title" name="title" placeholder="e.g., Annual Check-up, Blood Test" required>
                </div>
                 <div class="input-group">
                    <label for="doctor_name_external">Doctor or Clinic Name</label>
                    <input type="text" id="doctor_name_external" name="doctor_name_external" placeholder="e.g., Dr. John Smith, City Clinic" required>
                </div>
                <div class="input-group">
                    <label for="summary">Summary / Notes</label>
                    <textarea id="summary" name="summary" rows="4" placeholder="Add a brief summary of the record..."></textarea>
                </div>
                 <div class="input-group file-upload-group">
                    <label for="record_file">Attach File (Optional)</label>
                    <input type="file" id="record_file" name="record_file" class="file-input">
                    <label for="record_file" class="file-label">
                        <i class="fas fa-paperclip"></i>
                        <span class="file-button-text">Choose File</span>
                    </label>
                    <span id="file-name-display" class="file-name">No file chosen</span>
                </div>
                <button type="submit" class="submit-btn">Add Record</button>
            </form>
        </div>
    </div>

    
    <!-- Edit Record Modal -->
    <div id="edit-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <button id="close-edit-modal-btn" class="close-btn">&times;</button>
            <h2>Edit Medical Record</h2>
            <form action="edit_record_process.php" method="POST" class="modal-form" enctype="multipart/form-data">
                <input type="hidden" id="edit_record_id" name="record_id">
                <div class="input-group">
                    <label for="edit_record_date">Date of Record</label>
                    <input type="date" id="edit_record_date" name="record_date" required>
                </div>
                <div class="input-group">
                    <label for="edit_record_type">Type of Record</label>
                    <select id="edit_record_type" name="record_type" required>
                        <option value="Consultation">Consultation Note</option>
                        <option value="Lab Result">Lab Result</option>
                        <option value="Prescription">Prescription</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="edit_title">Title / Subject</label>
                    <input type="text" id="edit_title" name="title" required>
                </div>
                 <div class="input-group">
                    <label for="edit_doctor_name_external">Doctor or Clinic Name</label>
                    <input type="text" id="edit_doctor_name_external" name="doctor_name_external" required>
                </div>
                <div class="input-group">
                    <label for="edit_summary">Summary / Notes</label>
                    <textarea id="edit_summary" name="summary" rows="4"></textarea>
                </div>
                 <div class="input-group file-upload-group">
                    <label for="edit_record_file">Change Attached File (Optional)</label>
                    <input type="file" id="edit_record_file" name="record_file" class="file-input">
                    <label for="edit_record_file" class="file-label">
                        <i class="fas fa-paperclip"></i>
                        <span class="file-button-text">Choose New File</span>
                    </label>
                    <span id="edit-file-name-display" class="file-name">No new file chosen</span>
                </div>
                <button type="submit" class="submit-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const uploadModal = document.getElementById('upload-modal');
        const editModal = document.getElementById('edit-modal');
        
        const uploadBtn = document.getElementById('upload-record-btn');
        const closeUploadModalBtn = document.getElementById('close-upload-modal-btn');
        const closeEditModalBtn = document.getElementById('close-edit-modal-btn');
        
        // --- Logic for Upload Modal ---
        if (uploadBtn) {
            uploadBtn.addEventListener('click', () => uploadModal.style.display = 'flex');
        }
        if (closeUploadModalBtn) {
            closeUploadModalBtn.addEventListener('click', () => uploadModal.style.display = 'none');
        }
        
        // --- Logic for Edit Modal ---
        document.querySelectorAll('.action-btn.edit').forEach(button => {
            button.addEventListener('click', (e) => {
                const data = e.currentTarget.dataset;
                document.getElementById('edit_record_id').value = data.recordId;
                document.getElementById('edit_record_date').value = data.date;
                document.getElementById('edit_record_type').value = data.type;
                document.getElementById('edit_title').value = data.title;
                document.getElementById('edit_doctor_name_external').value = data.doctor;
                document.getElementById('edit_summary').value = data.summary;
                document.getElementById('edit-file-name-display').textContent = 'No new file chosen';
                editModal.style.display = 'flex';
            });
        });
        
        if (closeEditModalBtn) {
            closeEditModalBtn.addEventListener('click', () => editModal.style.display = 'none');
        }

        // Close modal on outside click
        window.addEventListener('click', (event) => {
            if (event.target === uploadModal) uploadModal.style.display = 'none';
            if (event.target === editModal) editModal.style.display = 'none';
        });

        // --- File Name Display Logic ---
        const fileInputs = [
            { input: 'record_file', display: 'file-name-display' },
            { input: 'edit_record_file', display: 'edit-file-name-display' }
        ];
        fileInputs.forEach(item => {
            const inputEl = document.getElementById(item.input);
            const displayEl = document.getElementById(item.display);
            if(inputEl && displayEl) {
                inputEl.addEventListener('change', () => {
                    displayEl.textContent = inputEl.files.length > 0 ? inputEl.files[0].name : 'No file chosen';
                });
            }
        });
    });
    </script>
</body>
</html>

