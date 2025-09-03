<?php
// Script to fix admin user role
include('Backend/includes/db.php');

echo "<h2>Fixing Admin User Role</h2>";

// First, check current status
$stmt = $conn->prepare('SELECT u.id, u.name, u.email, u.role_id, r.role_name 
                        FROM users u 
                        JOIN roles r ON u.role_id = r.id 
                        WHERE u.email = ?');
$stmt->bind_param('s', 'admin@hospital.com');
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p><strong>Current User Status:</strong></p>";
    echo "<ul>";
    echo "<li>ID: " . $user['id'] . "</li>";
    echo "<li>Name: " . $user['name'] . "</li>";
    echo "<li>Email: " . $user['email'] . "</li>";
    echo "<li>Current Role ID: " . $user['role_id'] . "</li>";
    echo "<li>Current Role: " . $user['role_name'] . "</li>";
    echo "</ul>";
    
    if ($user['role_name'] === 'admin') {
        echo "<p style='color: green;'><strong>✅ User already has admin role - No fix needed!</strong></p>";
    } else {
        echo "<p style='color: orange;'><strong>⚠️ User does NOT have admin role - Fixing now...</strong></p>";
        
        // Update user to have admin role (role_id = 1)
        $updateStmt = $conn->prepare('UPDATE users SET role_id = 1 WHERE email = ?');
        $updateStmt->bind_param('s', 'admin@hospital.com');
        
        if ($updateStmt->execute()) {
            echo "<p style='color: green;'><strong>✅ Successfully updated user to admin role!</strong></p>";
            
            // Verify the change
            $verifyStmt = $conn->prepare('SELECT u.id, u.name, u.email, u.role_id, r.role_name 
                                        FROM users u 
                                        JOIN roles r ON u.role_id = r.id 
                                        WHERE u.email = ?');
            $verifyStmt->bind_param('s', 'admin@hospital.com');
            $verifyStmt->execute();
            $verifyResult = $verifyStmt->get_result();
            $updatedUser = $verifyResult->fetch_assoc();
            
            echo "<p><strong>Updated User Status:</strong></p>";
            echo "<ul>";
            echo "<li>ID: " . $updatedUser['id'] . "</li>";
            echo "<li>Name: " . $updatedUser['name'] . "</li>";
            echo "<li>Email: " . $updatedUser['email'] . "</li>";
            echo "<li>New Role ID: " . $updatedUser['role_id'] . "</li>";
            echo "<li>New Role: " . $updatedUser['role_name'] . "</li>";
            echo "</ul>";
            
            $verifyStmt->close();
        } else {
            echo "<p style='color: red;'><strong>❌ Failed to update user role!</strong></p>";
        }
        
        $updateStmt->close();
    }
} else {
    echo "<p style='color: red;'><strong>❌ Admin user not found!</strong></p>";
}

$stmt->close();
$conn->close();

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>If the role was fixed, <strong>sign out</strong> and <strong>sign in again</strong></li>";
echo "<li>Go back to the doctors page</li>";
echo "<li>The admin controls should now be visible</li>";
echo "</ol>";
?>
