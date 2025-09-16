<?php
header('Content-Type: application/json');

// Include session configuration first
include('../includes/session_config.php');

// Your existing includes
include('../includes/db.php');
include('../includes/functions.php');

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
    // Check if user exists and get role information
    $stmt = $conn->prepare("SELECT u.id, u.name, u.email, u.password, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        json_response(['error' => 'Invalid email or password'], 401);
    }

    $user = $result->fetch_assoc();

    // Verify password (try both password_verify and MD5 for compatibility)
    if (!password_verify($password, $user['password']) && md5($password) !== $user['password']) {
        json_response(['error' => 'Invalid email or password'], 401);
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role_name'];

    // Return success with role
    json_response([
        'success' => true,
        'user' => [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role_name']
        ]
    ]);
} catch (Exception $e) {
    json_response(['error' => 'Login failed'], 500);
}
?>
