<?php
header('Content-Type: application/json');
include('../includes/db.php');
include('../includes/functions.php');

ensure_session_started();

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    json_response(['error' => 'Unauthorized access'], 401);
}

$sql = "SELECT id, name, email FROM users";
$result = $conn->query($sql);

if (!$result) {
    json_response(['error' => 'Database error'], 500);
}

$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

json_response($users);
?>
