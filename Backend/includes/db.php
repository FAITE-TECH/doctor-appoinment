<?php
$servername = "localhost";  // XAMPP default
$username   = "root";       // default user
$password   = "";           // default password is empty
$dbname     = "doctor";       // your database name
$port       = 3306;         // default MySQL port

// Try to connect with different ports if needed
$ports = [3306, 3305, 3307];
$conn = null;

foreach ($ports as $port) {
    try {
        $conn = new mysqli($servername, $username, $password, $dbname, $port);
        if (!$conn->connect_error) {
            break; // Connection successful
        }
        $conn->close();
    } catch (Exception $e) {
        continue; // Try next port
    }
}

// Check connection
if (!$conn || $conn->connect_error) {
    // Log error for debugging
    error_log("Database connection failed: " . ($conn ? $conn->connect_error : "No connection"));
    
    // Return JSON error for API calls
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed. Please check setup.']);
        exit;
    } else {
        die("❌ Database connection failed. Please run setup_database.php first.");
    }
}
?>
