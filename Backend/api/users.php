<?php
header('Content-Type: application/json');

// Include session configuration first
include('../includes/session_config.php');

// Your existing includes
include('../includes/db.php');
include('../includes/functions.php');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    json_response(['error' => 'Unauthorized access'], 401);
}

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'Forbidden: Admins only'], 403);
}

$sql = "SELECT u.id, u.name, u.email, u.created_at, r.role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);

if (!$result) {
    json_response(['error' => 'Database error: ' . $conn->error], 500);
}

$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Get total count
$countSql = "SELECT COUNT(*) as total FROM users";
$countResult = $conn->query($countSql);
$totalUsers = $countResult ? $countResult->fetch_assoc()['total'] : 0;

json_response([
    'success' => true, 
    'users' => $users,
    'total_users' => $totalUsers
]);
?>
