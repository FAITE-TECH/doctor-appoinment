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

        // Determine server name and whether we're running in production.
        // Treat non-localhost names as production by default. This can be
        // overridden by setting the environment variable FORCE_PRODUCTION=1
        // on the server if necessary.
        $server_name = $_SERVER['SERVER_NAME'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $force_production = getenv('FORCE_PRODUCTION');
        $is_production = $force_production ? true : !in_array($server_name, ['localhost', '127.0.0.1', '::1']);

        $this->config['server_name'] = $server_name;
        $this->config['is_production'] = $is_production;
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
        // Build absolute origin for production and local cases. Prefer HTTPS
        // in production (site is served on spchospital.com).
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 0) == 443 ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');

        // Origin without path (e.g. https://spchospital.com or http://localhost:8000)
        $origin_no_path = $protocol . '://' . $host;

        // For production, use the canonical deployment domain. This avoids
        // issues when the project sits in a subdirectory on a developer's machine.
        if (!empty($this->config['is_production'])) {
            // Hard-code the production domain here to ensure client fetches go to
            // the public API endpoint. Update if production domain changes.
            $production_domain = 'https://spchospital.com';
            $origin_no_path = $production_domain;
        }

        // api_full is an absolute URL clients (browser JS) can use. api is a
        // relative URL useful for same-origin server-side requests.
        $api_full = rtrim($origin_no_path, '/') . rtrim($web_root, '/') . '/Backend/api';

        $this->config['urls'] = [
            'base' => $web_root,
            'origin' => $origin_no_path,
            'api' => $web_root . '/Backend/api',
            'api_full' => $api_full,
            'uploads' => $web_root . '/uploads',
            'assets' => $web_root . '/Frontend/public/assets',
        ];
    }
    
    private function setupDatabase() {
        // Database configuration with cross-platform MySQL socket detection
        // Default (production/hosted) credentials. These will be overridden
        // automatically when running in local XAMPP/localhost development.
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

        // If running on a local development server (localhost / XAMPP / MAMP),
        // many dev environments use the MySQL root user with no password and
        // a local database named `doctor`. Override the credentials in that
        // case to avoid 'Access denied' errors during local development.
        $server_name = $_SERVER['SERVER_NAME'] ?? '';
        $is_localhost = in_array($server_name, ['localhost', '127.0.0.1', '::1']) || $this->config['is_xampp'] || $this->config['is_mamp'];

        if ($is_localhost) {
            // Prefer the default XAMPP MySQL root user and the 'doctor' database
            $this->config['database']['username'] = 'root';
            $this->config['database']['password'] = '';
            // Use 'doctor' if it exists, otherwise keep existing name
            $this->config['database']['database'] = 'doctor';
        }
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
        // If running in production, return the absolute API URL so clients
        // fetch the deployed domain (e.g. https://spchospital.com/Backend/api/...)
        if (!empty($this->config['is_production'])) {
            return rtrim($this->config['urls']['api_full'], '/') . ($endpoint ? '/' . ltrim($endpoint, '/') : '');
        }

        // Local / development: use relative API path to respect developer setups
        return $this->config['urls']['api'] . ($endpoint ? '/' . ltrim($endpoint, '/') : '');
    }
    
    public function getUploadUrl($path = '') {
        return $this->config['urls']['uploads'] . ($path ? '/' . ltrim($path, '/') : '');
    }
    
    public function getAssetUrl($path = '') {
        return $this->config['urls']['assets'] . ($path ? '/' . ltrim($path, '/') : '');
    }
    
    public function isProduction() {
        return !empty($this->config['is_production']);
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