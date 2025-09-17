<?php
// =====================================================================
//  DATABASE CONNECTION FILE
// =====================================================================

// --- 1. Database Credentials ---
// Replace these placeholders with your actual database details.
$db_host = 'localhost';         // Usually 'localhost'
$db_name = 'healthfirst';    // The name of your database
$db_user = 'root';              // Your database username
$db_pass = '';                  // Your database password

// --- 2. Data Source Name (DSN) ---
// This string contains the information required to connect to the database.
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

// --- 3. PDO Connection Options ---
// These options configure the behavior of the PDO connection.
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Turn on errors in the form of exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Make the default fetch mode associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Turn off emulation mode for real prepared statements
];

// --- 4. Create PDO instance and connect ---
// This block tries to create a new PDO object and connect to the database.
// If it fails, it catches the exception and displays an error message.
try {
    $conn = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // In a production environment, you would log this error instead of displaying it.
    // For development, it's useful to see the error message.
    die("Connection failed: " . $e->getMessage());
}
?>
