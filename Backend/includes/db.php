<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "doctor";
$socket = "/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock";

try {
    // Create connection with socket
    $conn = new mysqli($servername, $username, $password, $dbname, 3306, $socket);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8
    $conn->set_charset("utf8");
    
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
