<?php
// Cross-platform Database configuration using the new config system
require_once __DIR__ . '/cross-platform-config.php';

try {
    // Use the cross-platform configuration system
    $conn = get_db_connection();
    
    // Verify connection is working
    if (!$conn) {
        throw new Exception("Failed to establish database connection");
    }
    
    // Optional: Log successful connection for debugging
    $config_instance = get_config_instance();
    if (get_config('debug') || !$config_instance->isProduction()) {
        error_log("Database connected successfully on " . PHP_OS . " using " . 
                 (get_config('database.socket') ? 'socket' : 'TCP') . " connection");
    }
    
} catch (Exception $e) {
    // Enhanced error reporting with environment info
    $config_instance = get_config_instance();
    $env_info = $config_instance->getEnvironmentInfo();
    $error_msg = "Database connection failed: " . $e->getMessage() . "\n";
    $error_msg .= "Environment: " . $env_info['os'] . " with " . $env_info['server_software'] . "\n";
    $error_msg .= "Project root: " . $env_info['project_root'] . "\n";
    
    error_log($error_msg);

    // If running from CLI, write to STDERR and exit
    if (php_sapi_name() === 'cli') {
        fwrite(STDERR, $error_msg);
        exit(1);
    }

    // Ensure API/clients receive JSON instead of plain-text HTML
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    http_response_code(500);

    // Minimal, safe JSON for production, more details in non-production
    if ($config_instance->isProduction()) {
        echo json_encode(['error' => 'Database connection error. Please contact the administrator.']);
    } else {
        echo json_encode([
            'error' => 'Database connection failed: ' . $e->getMessage(),
            'environment' => [
                'os' => $env_info['os'],
                'server' => $env_info['server_software'],
                'project_root' => $env_info['project_root']
            ]
        ]);
    }
    exit;
}
?>
