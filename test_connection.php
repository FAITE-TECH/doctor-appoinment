<?php
// Test Database Connection
// This will help identify the specific connection issue

echo "<h1>Database Connection Test</h1>";

// Test 1: Basic MySQL connection
echo "<h2>Test 1: Basic MySQL Connection</h2>";
try {
    $conn = new mysqli("localhost", "root", "");
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Connection failed: " . $conn->connect_error . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Basic MySQL connection successful</p>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
}

// Test 2: Try with different ports
echo "<h2>Test 2: Port Testing</h2>";
$ports = [3306, 3305, 3307];

foreach ($ports as $port) {
    try {
        $conn = new mysqli("localhost", "root", "", "", $port);
        
        if ($conn->connect_error) {
            echo "<p>Port $port: ❌ " . $conn->connect_error . "</p>";
        } else {
            echo "<p style='color: green;'>Port $port: ✅ Connected successfully</p>";
            $conn->close();
        }
    } catch (Exception $e) {
        echo "<p>Port $port: ❌ Exception: " . $e->getMessage() . "</p>";
    }
}

// Test 3: Check if doctor database exists
echo "<h2>Test 3: Database Check</h2>";
try {
    $conn = new mysqli("localhost", "root", "");
    
    if ($conn->connect_error) {
        echo "<p style='color: red;'>❌ Cannot test database - connection failed</p>";
    } else {
        $result = $conn->query("SHOW DATABASES LIKE 'doctor'");
        
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✅ Database 'doctor' exists</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Database 'doctor' does not exist</p>";
        }
        $conn->close();
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
}

// Test 4: XAMPP Status Check
echo "<h2>Test 4: XAMPP Status</h2>";
echo "<p>Please check if XAMPP is running:</p>";
echo "<ul>";
echo "<li>Open XAMPP Control Panel</li>";
echo "<li>Make sure Apache is started (green)</li>";
echo "<li>Make sure MySQL is started (green)</li>";
echo "<li>Check MySQL port in XAMPP (usually 3306)</li>";
echo "</ul>";

echo "<h2>Next Steps:</h2>";
echo "<p>If the connection tests fail, try these solutions:</p>";
echo "<ol>";
echo "<li>Start XAMPP MySQL service</li>";
echo "<li>Check MySQL port in XAMPP configuration</li>";
echo "<li>Try accessing phpMyAdmin: <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>";
echo "<li>If phpMyAdmin works, create the 'doctor' database manually</li>";
echo "<li>Run the setup script: <a href='setup_database.php'>setup_database.php</a></li>";
echo "</ol>";
?>
