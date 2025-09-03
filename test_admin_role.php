<?php
// Test script to check admin user role
include('Backend/includes/db.php');

echo "<h2>Testing Admin User Role</h2>";

// Check if admin user exists and has correct role
$stmt = $conn->prepare('SELECT u.id, u.name, u.email, u.password, r.role_name 
                        FROM users u 
                        JOIN roles r ON u.role_id = r.id 
                        WHERE u.email = ?');
$stmt->bind_param('s', 'admin@hospital.com');
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p><strong>User Found:</strong></p>";
    echo "<ul>";
    echo "<li>ID: " . $user['id'] . "</li>";
    echo "<li>Name: " . $user['name'] . "</li>";
    echo "<li>Email: " . $user['email'] . "</li>";
    echo "<li>Role: " . $user['role_name'] . "</li>";
    echo "<li>Role ID: " . (strpos($user['role_name'], 'admin') !== false ? 'Admin' : 'Not Admin') . "</li>";
    echo "</ul>";
    
    if ($user['role_name'] === 'admin') {
        echo "<p style='color: green;'><strong>✅ User has admin role - This is correct!</strong></p>";
    } else {
        echo "<p style='color: red;'><strong>❌ User does NOT have admin role!</strong></p>";
    }
} else {
    echo "<p style='color: red;'><strong>❌ Admin user not found!</strong></p>";
}

$stmt->close();

// Check all roles in the system
echo "<h3>All Available Roles:</h3>";
$stmt = $conn->prepare('SELECT * FROM roles ORDER BY id');
$stmt->execute();
$result = $stmt->get_result();

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Role Name</th></tr>";
while ($role = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $role['id'] . "</td>";
    echo "<td>" . $role['role_name'] . "</td>";
    echo "</tr>";
}
echo "</table>";

$stmt->close();

// Check all users and their roles
echo "<h3>All Users and Their Roles:</h3>";
$stmt = $conn->prepare('SELECT u.id, u.name, u.email, r.role_name 
                        FROM users u 
                        JOIN roles r ON u.role_id = r.id 
                        ORDER BY u.id');
$stmt->execute();
$result = $stmt->get_result();

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>";
while ($user = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . $user['name'] . "</td>";
    echo "<td>" . $user['email'] . "</td>";
    echo "<td>" . $user['role_name'] . "</td>";
    echo "</tr>";
}
echo "</table>";

$stmt->close();
$conn->close();
?>
