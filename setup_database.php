<?php
// Database Setup Script
// Run this file in your browser to set up the database

echo "<h1>Doctor Appointment System - Database Setup</h1>";

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "doctor";

try {
    // First, connect without specifying database
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p>✅ Connected to MySQL successfully</p>";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Database '$dbname' created successfully or already exists</p>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($dbname);
    echo "<p>✅ Database '$dbname' selected</p>";
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Users table created successfully</p>";
    } else {
        throw new Exception("Error creating users table: " . $conn->error);
    }
    
    // Create doctors table
    $sql = "CREATE TABLE IF NOT EXISTS doctors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        specialization VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Doctors table created successfully</p>";
    } else {
        throw new Exception("Error creating doctors table: " . $conn->error);
    }
    
    // Create appointments table
    $sql = "CREATE TABLE IF NOT EXISTS appointments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        doctor_id INT NOT NULL,
        appointment_date DATE NOT NULL,
        appointment_time TIME NOT NULL,
        status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Appointments table created successfully</p>";
    } else {
        throw new Exception("Error creating appointments table: " . $conn->error);
    }
    
    // Insert sample doctor data
    $sql = "INSERT IGNORE INTO doctors (name, email, specialization, phone) VALUES
    ('Dr. John Smith', 'john.smith@hospital.com', 'Cardiology', '+1234567890'),
    ('Dr. Sarah Johnson', 'sarah.johnson@hospital.com', 'Pediatrics', '+1234567891'),
    ('Dr. Michael Brown', 'michael.brown@hospital.com', 'Orthopedics', '+1234567892')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ Sample doctors data inserted successfully</p>";
    } else {
        echo "<p>⚠️ Note: Sample doctors data may already exist or couldn't be inserted: " . $conn->error . "</p>";
    }
    
    echo "<h2>🎉 Database setup completed successfully!</h2>";
    echo "<p><a href='Frontend/pages/index.html' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Application</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Make sure XAMPP is running (Apache and MySQL)</li>";
    echo "<li>Check if MySQL is running on port 3306</li>";
    echo "<li>Verify MySQL root password (if any)</li>";
    echo "<li>Check XAMPP MySQL logs for errors</li>";
    echo "</ul>";
}

$conn->close();
?>
