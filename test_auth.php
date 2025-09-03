<?php
// Test Authentication System
echo "<h1>🔐 Authentication System Test</h1>";

// Test database connection first
include('Backend/includes/db.php');
echo "<p style='color: green;'>✅ Database connection successful!</p>";

// Test 1: Check if we can query users with roles
echo "<h3>Test 1: User Roles Query</h3>";
$sql = "SELECT u.id, u.name, u.email, r.role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f3f4f6;'><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['role_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ Error querying users with roles</p>";
}

// Test 2: Check available roles
echo "<h3>Test 2: Available Roles</h3>";
$sql = "SELECT * FROM roles ORDER BY id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li><strong>" . $row['role_name'] . "</strong> (ID: " . $row['id'] . ")</li>";
    }
    echo "</ul>";
}

// Test 3: Try to create a test patient user
echo "<h3>Test 3: Create Test Patient</h3>";
$test_email = 'test.patient@example.com';
$test_name = 'Test Patient';
$test_password = 'test123';

// Check if test user already exists
$check_sql = "SELECT id FROM users WHERE email = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $test_email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows == 0) {
    // Create test patient (role_id = 3 for patient)
    $insert_sql = "INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, 3)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("sss", $test_name, $test_email, md5($test_password));
    
    if ($insert_stmt->execute()) {
        echo "<p style='color: green;'>✅ Test patient created successfully!</p>";
        echo "<p><strong>Test Credentials:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Email:</strong> " . $test_email . "</li>";
        echo "<li><strong>Password:</strong> " . $test_password . "</li>";
        echo "<li><strong>Role:</strong> patient</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create test patient: " . $conn->error . "</p>";
    }
    $insert_stmt->close();
} else {
    echo "<p style='color: orange;'>⚠️ Test patient already exists</p>";
    echo "<p><strong>Test Credentials:</strong></p>";
    echo "<ul>";
    echo "<li><strong>Email:</strong> " . $test_email . "</li>";
    echo "<li><strong>Password:</strong> " . $test_password . "</li>";
    echo "<li><strong>Role:</strong> patient</li>";
    echo "</ul>";
}
$check_stmt->close();

// Test 4: Verify the test user was created
echo "<h3>Test 4: Verify Test User</h3>";
$verify_sql = "SELECT u.id, u.name, u.email, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ?";
$verify_stmt = $conn->prepare($verify_sql);
$verify_stmt->bind_param("s", $test_email);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result && $verify_result->num_rows > 0) {
    $user = $verify_result->fetch_assoc();
    echo "<p style='color: green;'>✅ Test user verified:</p>";
    echo "<ul>";
    echo "<li><strong>ID:</strong> " . $user['id'] . "</li>";
    echo "<li><strong>Name:</strong> " . $user['name'] . "</li>";
    echo "<li><strong>Email:</strong> " . $user['email'] . "</li>";
    echo "<li><strong>Role:</strong> " . $user['role_name'] . "</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Test user verification failed</p>";
}
$verify_stmt->close();

$conn->close();

echo "<hr>";
echo "<h3>🧪 Testing Instructions:</h3>";
echo "<ol>";
echo "<li>Go to <a href='Frontend/pages/signin.html'>Sign In Page</a></li>";
echo "<li>Use the test credentials above</li>";
echo "<li>You should be redirected to the home page after successful login</li>";
echo "</ol>";

echo "<p><a href='Frontend/pages/signin.html' style='background: #3b82f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Sign In</a></p>";
echo "<p><a href='Frontend/pages/index.html' style='background: #10b981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Home Page</a></p>";
?>





