<?php
echo "<h2>Simple Database Test</h2>";

try {
    // Test basic database connection
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'doctor';
    
    echo "<p>Testing connection to database: <strong>$database</strong></p>";
    
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'><strong>❌ Database connection failed:</strong> " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'><strong>✅ Database connection successful!</strong></p>";
        
        // Test if tables exist
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            echo "<p><strong>Tables in database:</strong></p>";
            echo "<ul>";
            while ($row = $result->fetch_array()) {
                echo "<li>" . $row[0] . "</li>";
            }
            echo "</ul>";
        }
        
        // Test if admin user exists
        $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE email = 'admin@hospital.com'");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p><strong>Admin users found:</strong> " . $row['count'] . "</p>";
        }
        
        $conn->close();
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><strong>Common Solutions:</strong></p>";
echo "<ol>";
echo "<li>Make sure XAMPP is running (Apache + MySQL)</li>";
echo "<li>Check if database 'doctor' exists</li>";
echo "<li>Import database_setup.sql if needed</li>";
echo "<li>Verify MySQL credentials (usually root with no password)</li>";
echo "</ol>";
?>
