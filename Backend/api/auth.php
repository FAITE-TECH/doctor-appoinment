<?php
header('Content-Type: application/json');
include('../includes/db.php');
include('../includes/functions.php');

ensure_session_started();

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'POST' && $action === 'signup') {
    $body = get_json_body();
    require_fields($body, ['name', 'email', 'password']);

    $name = trim($body['name']);
    $email = strtolower(trim($body['email']));
    $password = $body['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error' => 'Invalid email'], 422);
    }
    if (strlen($password) < 6) {
        json_response(['error' => 'Password must be at least 6 characters'], 422);
    }

    // Check existing user
    $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) {
        json_response(['error' => 'Database error (prepare)'], 500);
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        json_response(['error' => 'Email already registered'], 409);
    }
    $stmt->close();

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $insert = $conn->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    if (!$insert) {
        json_response(['error' => 'Database error (prepare)'], 500);
    }
    $insert->bind_param('sss', $name, $email, $passwordHash);
    if (!$insert->execute()) {
        $insert->close();
        json_response(['error' => 'Failed to create user'], 500);
    }
    $userId = $insert->insert_id;
    $insert->close();

    $_SESSION['user_id'] = $userId;
    $_SESSION['email'] = $email;

    json_response(['message' => 'Signup successful', 'user' => ['id' => $userId, 'name' => $name, 'email' => $email]], 201);
}

if ($method === 'POST' && $action === 'signin') {
    $body = get_json_body();
    require_fields($body, ['email', 'password']);

    $email = strtolower(trim($body['email']));
    $password = $body['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error' => 'Invalid email'], 422);
    }

    $stmt = $conn->prepare('SELECT id, name, email, password FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) {
        json_response(['error' => 'Database error (prepare)'], 500);
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user || !password_verify($password, $user['password'])) {
        json_response(['error' => 'Invalid credentials'], 401);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];

    json_response(['message' => 'Signin successful', 'user' => ['id' => intval($user['id']), 'name' => $user['name'], 'email' => $user['email']]]);
}

if ($method === 'POST' && $action === 'signout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    json_response(['message' => 'Signed out']);
}

if ($method === 'GET' && $action === 'me') {
    if (!isset($_SESSION['user_id'])) {
        json_response(['authenticated' => false]);
    }
    json_response(['authenticated' => true, 'user' => ['id' => intval($_SESSION['user_id']), 'email' => $_SESSION['email']]]);
}

json_response(['error' => 'Not found'], 404);

?>

