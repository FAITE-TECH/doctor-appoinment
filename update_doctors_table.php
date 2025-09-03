<?php
// Script to update doctors table for simplified management
include('Backend/includes/db.php');

echo "<h2>Updating Doctors Table Structure</h2>";

try {
    // Check if the new columns already exist
    $result = $conn->query("SHOW COLUMNS FROM doctors LIKE 'name'");
    $nameExists = $result->num_rows > 0;
    
    if (!$nameExists) {
        // Add new columns for simplified doctor management
        $sql = "ALTER TABLE doctors 
                ADD COLUMN name VARCHAR(255) NOT NULL AFTER id,
                ADD COLUMN email VARCHAR(255) NOT NULL AFTER name,
                ADD COLUMN image_path VARCHAR(500) AFTER phone";
        
        if ($conn->query($sql)) {
            echo "<p style='color: green;'><strong>✅ Successfully added new columns to doctors table</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>❌ Failed to add columns:</strong> " . $conn->error . "</p>";
        }
    } else {
        echo "<p style='color: blue;'><strong>ℹ️ New columns already exist</strong></p>";
    }
    
    // Show current table structure
    echo "<h3>Current Doctors Table Structure:</h3>";
    $result = $conn->query("DESCRIBE doctors");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Create uploads directory
    $uploadDir = 'uploads/doctors/';
    if (!is_dir($uploadDir)) {
        if (mkdir($uploadDir, 0755, true)) {
            echo "<p style='color: green;'><strong>✅ Created uploads/doctors/ directory</strong></p>";
        } else {
            echo "<p style='color: red;'><strong>❌ Failed to create uploads directory</strong></p>";
        }
    } else {
        echo "<p style='color: blue;'><strong>ℹ️ Uploads directory already exists</strong></p>";
    }
    
    echo "<hr>";
    echo "<p style='color: green;'><strong>✅ Doctors table updated successfully!</strong></p>";
    echo "<p>You can now:</p>";
    echo "<ul>";
    echo "<li>Add doctors without creating user accounts</li>";
    echo "<li>Upload doctor profile pictures</li>";
    echo "<li>Manage doctors with simple CRUD operations</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
}

$conn->close();
?>
