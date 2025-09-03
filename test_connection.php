<?php
// Test Database Connection
echo "<h1>Database Connection Test</h1>";

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
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test query
    $sql = "SELECT COUNT(*) as user_count FROM users";
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>✅ Users table accessible: " . $row['user_count'] . " users found</p>";
    } else {
        echo "<p style='color: red;'>❌ Error querying users table: " . $conn->error . "</p>";
    }
    
    // Test roles query
    $sql = "SELECT role_name, COUNT(*) as count FROM users u JOIN roles r ON u.role_id = r.id GROUP BY role_name";
    $result = $conn->query($sql);
    
    if ($result) {
        echo "<p>✅ User roles breakdown:</p>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . $row['role_name'] . ": " . $row['count'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Error querying roles: " . $conn->error . "</p>";
    }
    
    // Show all users with their details
    echo "<h3>📋 All Users in Database:</h3>";
    $sql = "SELECT u.id, u.name, u.email, r.role_name, u.created_at FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f3f4f6;'><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "<td>" . $row['role_name'] . "</td>";
            echo "<td>" . $row['created_at'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Error querying users or no users found</p>";
    }
    
    // Check available roles
    echo "<h3>🔍 Available Roles in System:</h3>";
    $sql = "SELECT * FROM roles ORDER BY id";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li><strong>" . $row['role_name'] . "</strong> (ID: " . $row['id'] . ")</li>";
        }
        echo "</ul>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='Frontend/pages/index.html' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Application</a></p>";
?>
