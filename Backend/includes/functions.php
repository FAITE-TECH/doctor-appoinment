<?php
header('Content-Type: application/json');

/**
 * Send a JSON response and exit
 */
function json_response($data, $status_code = 200) {
    http_response_code($status_code);
    echo json_encode($data);
    exit;
}

/**
 * Get JSON body of a request
 */
function get_json_body() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        json_response(['error' => 'Invalid JSON body'], 400);
    }
    return $data ?: [];
}

/**
 * Validate required fields exist and are non-empty
 */
function require_fields($body, $fields) {
    foreach ($fields as $field) {
        if (!isset($body[$field]) || trim((string)$body[$field]) === '') {
            json_response(['error' => "Missing or empty field: $field"], 422);
        }
    }
}

/**
 * Start session safely
 */
function ensure_session_started() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

?>

