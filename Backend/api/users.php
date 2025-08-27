<?php
header('Content-Type: application/json');
include('../includes/db.php');

$sql = "SELECT id, name, email FROM users";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

echo json_encode($users);
