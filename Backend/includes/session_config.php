<?php
/**
 * Session Configuration for Admin Panel
 * This file should be included at the top of all admin API files
 */

// Ensure sessions are started with proper configuration
if (session_status() !== PHP_SESSION_ACTIVE) {
    // Configure session parameters for better persistence and security
    ini_set('session.cookie_lifetime', 86400); // 24 hours
    ini_set('session.gc_maxlifetime', 86400); // 24 hours
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_cookies', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_path', '/doctor-appoinment/');
    ini_set('session.cookie_domain', '');
    
    // Start the session
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Set session timeout (24 hours)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 86400)) {
    // Session expired, destroy it
    session_unset();
    session_destroy();
    session_start();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Function to check if user is authenticated as admin
function is_admin_authenticated() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['role']) && 
           $_SESSION['role'] === 'admin';
}

// Function to require admin authentication
function require_admin_auth() {
    if (!is_admin_authenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized - Admin access required']);
        exit;
    }
}
?>
