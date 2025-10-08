<?php
/**
 * Cross-Platform Configuration Handler
 * Automatically detects the environment and sets appropriate configurations
 * Works across different operating systems (Windows, macOS, Linux) and server setups
 */

// Prevent direct access
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);
}

class CrossPlatformConfig {
    private static $instance = null;
    private $config = [];
    
    private function __construct() {
        $this->detectEnvironment();
        $this->setupPaths();
        $this->setupDatabase();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function detectEnvironment() {
        $this->config['os'] = strtoupper(substr(PHP_OS, 0, 3));
        $this->config['is_windows'] = ($this->config['os'] === 'WIN');
        $this->config['is_mac'] = ($this->config['os'] === 'DAR');
        $this->config['is_linux'] = ($this->config['os'] === 'LIN');
        
        // Detect server software
        $server_software = $_SERVER['SERVER_SOFTWARE'] ?? '';
        $this->config['is_xampp'] = (stripos($server_software, 'apache') !== false) && 
                                   (file_exists('/Applications/XAMPP') || file_exists('C:\xampp') || file_exists('/opt/lampp'));
        $this->config['is_wamp'] = stripos($server_software, 'apache') !== false && file_exists('C:\wamp');
        $this->config['is_mamp'] = stripos($server_software, 'apache') !== false && file_exists('/Applications/MAMP');
        
        // Document root and script path detection
        $this->config['document_root'] = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $this->config['script_name'] = $_SERVER['SCRIPT_NAME'] ?? '';
        $this->config['request_uri'] = $_SERVER['REQUEST_URI'] ?? '';
        
        // Detect if we're in a subdirectory
        $script_dir = dirname($this->config['script_name']);
        $this->config['base_path'] = $script_dir;
        
        // Find the project root by looking for characteristic files
        $current_dir = __DIR__;
        $project_root = $this->findProjectRoot($current_dir);
        $this->config['project_root'] = $project_root;
        
        // Set relative paths
        $this->config['web_root'] = $this->getWebRoot();
    }
    
    private function findProjectRoot($start_dir) {
        $dir = $start_dir;
        $max_levels = 10; // Prevent infinite loops
        $level = 0;
        
        while ($level < $max_levels) {
            // Look for characteristic files that indicate project root
            $indicators = [
                'database_setup.sql',
                'index.php',
                'Backend/api',
                'Frontend/pages'
            ];
            
            $found_indicators = 0;
            foreach ($indicators as $indicator) {
                if (file_exists($dir . DIRECTORY_SEPARATOR . $indicator)) {
                    $found_indicators++;
                }
            }
            
            // If we found multiple indicators, this is likely the project root
            if ($found_indicators >= 2) {
                return $dir;
            }
            
            // Move up one directory
            $parent_dir = dirname($dir);
            if ($parent_dir === $dir) {
                // We've reached the filesystem root
                break;
            }
            $dir = $parent_dir;
            $level++;
        }
        
        // Fallback to the directory containing this config file
        return $start_dir;
    }
    
    private function getWebRoot() {
        // Try to determine the web-accessible path
        $script_name = $this->config['script_name'];
        $project_root = $this->config['project_root'];
        $document_root = $this->config['document_root'];
        
        // Calculate relative path from document root to project
        if ($document_root && $project_root) {
            $relative_path = $this->getRelativePath($document_root, $project_root);
            return '/' . ltrim($relative_path, '/');
        }
        
        // Fallback: try to extract from script name
        $path_parts = explode('/', $script_name);
        $web_root = '';
        
        foreach ($path_parts as $part) {
            if (in_array($part, ['doctor-appoinment', 'doctor-appointment'])) {
                $web_root = '/' . $part;
                break;
            }
        }
        
        return $web_root ?: '/doctor-appoinment';
    }
    
    private function getRelativePath($from, $to) {
        $from = rtrim(str_replace('\\', '/', $from), '/');
        $to = rtrim(str_replace('\\', '/', $to), '/');
        
        $from_parts = explode('/', $from);
        $to_parts = explode('/', $to);
        
        // Find common path
        $common_length = 0;
        $min_length = min(count($from_parts), count($to_parts));
        
        for ($i = 0; $i < $min_length; $i++) {
            if ($from_parts[$i] === $to_parts[$i]) {
                $common_length++;
            } else {
                break;
            }
        }
        
        // Build relative path
        $relative_parts = array_slice($to_parts, $common_length);
        return implode('/', $relative_parts);
    }
    
    private function setupPaths() {
        $project_root = $this->config['project_root'];
        $web_root = $this->config['web_root'];
        
        // File system paths
        $this->config['paths'] = [
            'root' => $project_root,
            'backend' => $project_root . DIRECTORY_SEPARATOR . 'Backend',
            'frontend' => $project_root . DIRECTORY_SEPARATOR . 'Frontend',
            'uploads' => $project_root . DIRECTORY_SEPARATOR . 'uploads',
            'includes' => $project_root . DIRECTORY_SEPARATOR . 'Backend' . DIRECTORY_SEPARATOR . 'includes',
            'api' => $project_root . DIRECTORY_SEPARATOR . 'Backend' . DIRECTORY_SEPARATOR . 'api',
        ];
        
        // Web URLs
        $this->config['urls'] = [
            'base' => $web_root,
            'api' => $web_root . '/Backend/api',
            'uploads' => $web_root . '/uploads',
            'assets' => $web_root . '/Frontend/public/assets',
        ];
    }
    
    private function setupDatabase() {
        // Database configuration with cross-platform MySQL socket detection
        $this->config['database'] = [
            'host' => 'localhost',
            'username' => 'u697508608_doctor',
            'password' => 'admin@SPC2024',
            'database' => 'u697508608_doctor',
            'port' => 3306,
            'charset' => 'utf8mb4'
        ];
        
        // Find appropriate MySQL socket
        $possible_sockets = [];
        
        if ($this->config['is_mac']) {
            $possible_sockets = [
                '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
                '/Applications/MAMP/tmp/mysql/mysql.sock',
                '/opt/lampp/var/mysql/mysql.sock',
                '/tmp/mysql.sock'
            ];
        } elseif ($this->config['is_linux']) {
            $possible_sockets = [
                '/opt/lampp/var/mysql/mysql.sock',
                '/var/run/mysqld/mysqld.sock',
                '/tmp/mysql.sock',
                '/var/lib/mysql/mysql.sock'
            ];
        }
        // Windows typically uses named pipes or TCP, so no socket needed
        
        // Find existing socket
        $socket = null;
        foreach ($possible_sockets as $possible_socket) {
            if (file_exists($possible_socket)) {
                $socket = $possible_socket;
                break;
            }
        }
        
        $this->config['database']['socket'] = $socket;
    }
    
    public function get($key = null) {
        if ($key === null) {
            return $this->config;
        }
        
        // Support dot notation for nested keys
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (is_array($value) && array_key_exists($k, $value)) {
                $value = $value[$k];
            } else {
                return null;
            }
        }
        
        return $value;
    }
    
    public function getDatabaseConnection() {
        $db_config = $this->config['database'];
        
        try {
            $conn = null;
            
            // Try socket connection first if available
            if ($db_config['socket'] && file_exists($db_config['socket'])) {
                $conn = new mysqli(
                    $db_config['host'],
                    $db_config['username'],
                    $db_config['password'],
                    $db_config['database'],
                    $db_config['port'],
                    $db_config['socket']
                );
            }
            
            // If socket connection fails or not available, try TCP
            if (!$conn || $conn->connect_error) {
                $conn = new mysqli(
                    $db_config['host'],
                    $db_config['username'],
                    $db_config['password'],
                    $db_config['database'],
                    $db_config['port']
                );
            }
            
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            $conn->set_charset($db_config['charset']);
            return $conn;
            
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed on " . PHP_OS . ": " . $e->getMessage());
        }
    }
    
    public function getApiUrl($endpoint = '') {
        return $this->config['urls']['api'] . ($endpoint ? '/' . ltrim($endpoint, '/') : '');
    }
    
    public function getUploadUrl($path = '') {
        return $this->config['urls']['uploads'] . ($path ? '/' . ltrim($path, '/') : '');
    }
    
    public function getAssetUrl($path = '') {
        return $this->config['urls']['assets'] . ($path ? '/' . ltrim($path, '/') : '');
    }
    
    public function isProduction() {
        return !in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1', '::1']);
    }
    
    public function getEnvironmentInfo() {
        return [
            'os' => PHP_OS,
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'document_root' => $this->config['document_root'],
            'project_root' => $this->config['project_root'],
            'web_root' => $this->config['web_root'],
            'is_xampp' => $this->config['is_xampp'],
            'is_production' => $this->isProduction()
        ];
    }
}

// Create global instance and helper functions
$GLOBALS['cross_platform_config'] = CrossPlatformConfig::getInstance();

// Helper functions for easy access
function get_config($key = null) {
    return $GLOBALS['cross_platform_config']->get($key);
}

function get_config_instance() {
    return $GLOBALS['cross_platform_config'];
}

function get_db_connection() {
    return $GLOBALS['cross_platform_config']->getDatabaseConnection();
}

function get_api_url($endpoint = '') {
    return $GLOBALS['cross_platform_config']->getApiUrl($endpoint);
}

function get_upload_url($path = '') {
    return $GLOBALS['cross_platform_config']->getUploadUrl($path);
}

function get_asset_url($path = '') {
    return $GLOBALS['cross_platform_config']->getAssetUrl($path);
}
?>