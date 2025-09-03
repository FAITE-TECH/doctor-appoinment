<?php
// Test session configuration
include('Backend/includes/session_config.php');

echo "Session Status: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Cookie Lifetime: " . ini_get('session.cookie_lifetime') . "\n";
echo "GC Max Lifetime: " . ini_get('session.gc_maxlifetime') . "\n";

if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "\n";
    echo "User Role: " . $_SESSION['role'] . "\n";
} else {
    echo "No user session found\n";
}

// Test setting a session variable
$_SESSION['test_var'] = 'test_value_' . time();
echo "Test variable set: " . $_SESSION['test_var'] . "\n";
?>
