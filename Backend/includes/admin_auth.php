<?php
// Admin authentication helper - include this in all admin pages
session_start();

// Check if user is authenticated and is admin
function checkAdminAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Redirect to login if not admin
        header('Location: ../signin.html');
        exit();
    }
    
    // Return user info for use in admin pages
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['email'],
        'role' => $_SESSION['role']
    ];
}

// Get current admin user info
function getCurrentAdmin() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

// Check if user is admin (returns boolean)
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
?>
