<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "doctor";
$socket = "/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock";

try {
    // Try connection with socket first, then fallback to port
    $conn = new mysqli($servername, $username, $password, $dbname, 3306, $socket);
    
    // If socket connection fails, try with port only
    if ($conn->connect_error) {
        $conn = new mysqli($servername, $username, $password, $dbname, 3306);
    }
    
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
