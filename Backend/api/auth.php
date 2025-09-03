<?php
header('Content-Type: application/json');

// Include session configuration first
include('../includes/session_config.php');

include('../includes/db.php');
include('../includes/functions.php');

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'POST' && $action === 'signup') {
    $body = get_json_body();
    require_fields($body, ['name', 'email', 'password']);

    $name = trim($body['name']);
    $email = strtolower(trim($body['email']));
    $password = $body['password'];

    // Default role is 'patient' (role_id = 3)
    $role_id = 3;
    // Only admins can assign different roles
    if (isset($body['role']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        $role = $body['role'];
        // Map role names to role IDs
        $role_map = ['admin' => 1, 'doctor' => 2, 'patient' => 3, 'staff' => 4];
        $role_id = isset($role_map[$role]) ? $role_map[$role] : 3;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error' => 'Invalid email'], 422);
    }
    if (strlen($password) < 6) {
        json_response(['error' => 'Password must be at least 6 characters'], 422);
    }

    // Check if email already exists
    $stmt = $GLOBALS['conn']->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) json_response(['error'=>'Database error (prepare)'],500);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        json_response(['error'=>'Email already registered'],409);
    }
    $stmt->close();

    // Use MD5 to match the database setup
    $passwordHash = md5($password);

    $insert = $GLOBALS['conn']->prepare('INSERT INTO users (name, email, password, role_id) VALUES (?,?,?,?)');
    if (!$insert) json_response(['error'=>'Database error (prepare)'],500);
    $insert->bind_param('sssi', $name, $email, $passwordHash, $role_id);
    if (!$insert->execute()) {
        $insert->close();
        json_response(['error'=>'Failed to create user'],500);
    }
    $userId = $insert->insert_id;
    $insert->close();

    // Get role name for session
    $role_stmt = $GLOBALS['conn']->prepare('SELECT role_name FROM roles WHERE id = ?');
    $role_stmt->bind_param('i', $role_id);
    $role_stmt->execute();
    $role_result = $role_stmt->get_result();
    $role_data = $role_result->fetch_assoc();
    $role_name = $role_data['role_name'];
    $role_stmt->close();

    // Set session
    $_SESSION['user_id'] = $userId;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role_name;

    json_response([
        'message' => 'Signup successful',
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => $role_name
        ]
    ],201);
}

if ($method === 'POST' && $action === 'signin') {
    $body = get_json_body();
    require_fields($body, ['email','password']);

    $email = strtolower(trim($body['email']));
    $password = $body['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(['error'=>'Invalid email'],422);
    }

    // Join with roles table to get role information
    $stmt = $GLOBALS['conn']->prepare('SELECT u.id, u.name, u.email, u.password, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email=? LIMIT 1');
    if (!$stmt) json_response(['error'=>'Database error (prepare)'],500);
    $stmt->bind_param('s',$email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();

    if (!$user || !password_verify($password,$user['password'])) {
        // Try MD5 as fallback for existing users
        if (!$user || md5($password) !== $user['password']) {
            json_response(['error'=>'Invalid credentials'],401);
        }
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role_name'];

    json_response([
        'message'=>'Signin successful',
        'user'=>[
            'id'=>intval($user['id']),
            'name'=>$user['name'],
            'email'=>$user['email'],
            'role'=>$user['role_name']
        ]
    ]);
}

if ($method === 'POST' && $action === 'signout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    json_response(['message'=>'Signed out']);
}

if ($method === 'GET' && $action === 'me') {
    if (!isset($_SESSION['user_id'])) {
        json_response(['authenticated'=>false]);
    }
    json_response([
        'authenticated'=>true,
        'user'=>[
            'id'=>intval($_SESSION['user_id']),
            'name'=>$_SESSION['name'] ?? 'Unknown User',
            'email'=>$_SESSION['email'],
            'role'=>$_SESSION['role']
        ]
    ]);
}

json_response(['error'=>'Not found'],404);
?>
