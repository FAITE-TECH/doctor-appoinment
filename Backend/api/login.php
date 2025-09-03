<?php
header('Content-Type: application/json');
include('../includes/db.php');
include('../includes/functions.php');

ensure_session_started();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON body
$body = get_json_body();
require_fields($body, ['email', 'password']);

$email = trim($body['email']);
$password = trim($body['password']);

try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        json_response(['error' => 'Invalid email or password'], 401);
    }

    $user = $result->fetch_assoc();

    // Verify password
    if (!password_verify($password, $user['password'])) {
        json_response(['error' => 'Invalid email or password'], 401);
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email']   = $user['email'];
    $_SESSION['role']    = $user['role']; // admin / user

    // Return success with role
    json_response([
        'success' => true,
        'user' => [
            'id'    => $user['id'],
            'email' => $user['email'],
            'role'  => $user['role']
        ]
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Login failed'], 500);
}
?>
