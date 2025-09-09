<?php
// Add these at the VERY TOP
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off HTML error display
ini_set('log_errors', 1);

header('Content-Type: application/json');

// Include session configuration first
include('../includes/session_config.php');

// Your existing includes
include('../includes/db.php');
include('../includes/functions.php');

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// ==============================
// Helper Functions
// ==============================
function checkAdminAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        json_response(['error' => 'Unauthorized access'], 403);
    }
}

function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        json_response(['error' => 'Not authenticated'], 401);
    }
}

// ==============================
// Admin / Dashboard Endpoints
// ==============================

if ($method === 'GET' && $action === 'dashboard_stats') {
    checkAdminAuth();
    try {
        $stats = [];
        $tables = ['doctors', 'appointments', 'users', 'events'];
        
        foreach ($tables as $table) {
            $stmt = $GLOBALS['conn']->prepare("SELECT COUNT(*) AS count FROM $table");
            $stmt->execute();
            $result = $stmt->get_result();
            $stats[$table] = $result->fetch_assoc()['count'];
        }
        json_response(['success' => true, 'stats' => $stats]);
    } catch (Exception $e) {
        json_response(['error' => 'Failed to load statistics: ' . $e->getMessage()], 500);
    }
}

if ($method === 'GET' && $action === 'recent_appointments') {
    checkAdminAuth();
    try {
        $sql = "SELECT a.*, u.name AS patient_name, du.name AS doctor_name 
                FROM appointments a
                JOIN users u ON a.user_id = u.id
                JOIN doctors d ON a.doctor_id = d.id
                JOIN users du ON d.user_id = du.id
                ORDER BY a.created_at DESC LIMIT 5";
        
        $stmt = $GLOBALS['conn']->prepare($sql);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $GLOBALS['conn']->error);
        }
        
        $stmt->execute();
        if (!$stmt) {
            throw new Exception('Failed to execute statement: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if (!$result) {
            throw new Exception('Failed to get result: ' . $stmt->error);
        }
        
        $appointments = [];
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        json_response(['success' => true, 'appointments' => $appointments]);
    } catch (Exception $e) {
        error_log('Recent appointments error: ' . $e->getMessage());
        json_response(['error' => 'Failed to load appointments: ' . $e->getMessage()], 500);
    }
}

if ($method === 'GET' && $action === 'check_auth') {
    if (!isset($_SESSION['user_id'])) {
        json_response(['authenticated' => false]);
    }
    json_response([
        'authenticated' => true,
        'is_admin' => ($_SESSION['role'] ?? '') === 'admin',
        'user' => [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role'] ?? 'user'
        ]
    ]);
}

// ==============================
// Generic CRUD Functions
// ==============================

function getTableData($table, $orderBy = 'id') {
    checkAdminAuth();
    
    // Whitelist allowed tables
    $allowedTables = ['doctors', 'appointments', 'users', 'events', 'departments', 'services', 'gallery'];
    if (!in_array($table, $allowedTables)) {
        json_response(['error' => 'Invalid table'], 400);
    }
    
    // Whitelist allowed order by columns
    $allowedColumns = ['id', 'name', 'created_at', 'event_date', 'title', 'specialization'];
    if (!in_array($orderBy, $allowedColumns)) {
        $orderBy = 'id';
    }
    
    try {
        $sql = "SELECT * FROM $table ORDER BY $orderBy";
        $stmt = $GLOBALS['conn']->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        json_response(['success' => true, $table => $rows]);
    } catch (Exception $e) {
        json_response(['error' => "Failed to load $table: " . $e->getMessage()], 500);
    }
}

function addTableRow($table, $fields) {
    checkAdminAuth();
    $body = get_json_body();
    require_fields($body, $fields);
    try {
        $columns = implode(',', $fields);
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $types = str_repeat('s', count($fields));
        
        if (in_array('price', $fields)) {
            $types = str_replace('s', 'd', $types);
        }

        $stmt = $GLOBALS['conn']->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        $values = array_map(fn($f) => $body[$f], $fields);
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            json_response(['success' => true, 'message' => ucfirst($table) . ' added successfully'], 201);
        } else {
            json_response(['error' => "Failed to add $table: " . $stmt->error], 500);
        }
    } catch (Exception $e) {
        json_response(['error' => "Failed to add $table: " . $e->getMessage()], 500);
    }
}

// ==============================
// Doctors CRUD
// ==============================
// Order by a safe default column to avoid errors if 'name' does not exist
if ($method === 'GET' && $action === 'get_doctors') getTableData('doctors', 'id');

// Enhanced add doctor to support both JSON and multipart (with image)
if ($method === 'POST' && $action === 'add_doctor') {
    checkAdminAuth();

    // Helper: ensure column exists
    $ensureColumn = function($table, $column, $definition) {
        $result = $GLOBALS['conn']->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        $exists = $result && $result->num_rows > 0;
        if (!$exists) {
            $GLOBALS['conn']->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        }
    };

    // Ensure optional columns for richer data
    $ensureColumn('doctors', 'name', 'VARCHAR(255) NOT NULL');
    $ensureColumn('doctors', 'email', 'VARCHAR(255) NOT NULL');
    $ensureColumn('doctors', 'phone', 'VARCHAR(20) NULL');
    $ensureColumn('doctors', 'image_path', 'VARCHAR(500) NULL');
    // Optional extra fields requested
    $ensureColumn('doctors', 'description', 'TEXT NULL');
    // Department linkage (optional)
    $ensureColumn('doctors', 'department_id', 'INT NULL');

    // Relax legacy NOT NULL on user_id to allow admin-created doctors without a linked user
    try {
        $colRes = $GLOBALS['conn']->query("SHOW COLUMNS FROM doctors LIKE 'user_id'");
        if ($colRes && ($colInfo = $colRes->fetch_assoc())) {
            if (isset($colInfo['Null']) && strtoupper($colInfo['Null']) === 'NO') {
                // Make user_id nullable if it was defined NOT NULL
                $GLOBALS['conn']->query('ALTER TABLE doctors MODIFY user_id INT NULL');
            }
        }
    } catch (Exception $e) {
        // If this fails, continue; insert may still work if schema already compatible
    }

    $isMultipart = !empty($_POST) || !empty($_FILES);

    if ($isMultipart) {
        $name = trim($_POST['name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $specialization = trim($_POST['specialization'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $departmentId = isset($_POST['department_id']) && !empty($_POST['department_id']) ? intval($_POST['department_id']) : null;

        if ($name === '' || $email === '' || $specialization === '') {
            json_response(['error' => 'Name, email, and specialization are required'], 422);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['error' => 'Invalid email format'], 422);
        }
        
        // Validate department_id if provided
        if ($departmentId !== null) {
            $deptCheck = $GLOBALS['conn']->query("SELECT id FROM departments WHERE id = $departmentId");
            if (!$deptCheck || $deptCheck->num_rows === 0) {
                json_response(['error' => 'Invalid department selected'], 422);
            }
        }

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','image/gif'];
            if (!in_array($_FILES['image']['type'], $allowed)) {
                json_response(['error' => 'Invalid image type'], 422);
            }
            // Ensure uploads/doctors directory exists at project root
            $uploadDir = __DIR__ . '/../../uploads/doctors/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('', true) . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                json_response(['error' => 'Failed to upload image'], 500);
            }
            // Store a web-accessible path for the frontend
            $imagePath = '/doctor-appoinment/uploads/doctors/' . $filename;
        }

        // Insert with optional department and description
        $sql = 'INSERT INTO doctors (name, email, specialization, phone, image_path, description, department_id) VALUES (?,?,?,?,?,?,?)';
        $stmt = $GLOBALS['conn']->prepare($sql);
        $deptParam = $departmentId ? $departmentId : null;
        $stmt->bind_param('ssssssi', $name, $email, $specialization, $phone, $imagePath, $description, $deptParam);
        if ($stmt->execute()) {
            json_response(['success' => true, 'message' => 'Doctor added successfully', 'id' => $stmt->insert_id], 201);
        } else {
            json_response(['error' => 'Failed to add doctor: ' . $stmt->error], 500);
        }
    } else {
        // Fallback to previous JSON handling
        addTableRow('doctors', ['name','email','specialization','phone']);
    }
}

if ($method === 'PUT' && $action === 'update_doctor') {
    checkAdminAuth();
    $body = get_json_body();
    require_fields($body, ['id','name','email','specialization','phone']);
    
    // Handle optional department_id
    $departmentId = isset($body['department_id']) && !empty($body['department_id']) ? intval($body['department_id']) : null;
    
    // Validate department_id if provided
    if ($departmentId !== null) {
        $deptCheck = $GLOBALS['conn']->query("SELECT id FROM departments WHERE id = $departmentId");
        if (!$deptCheck || $deptCheck->num_rows === 0) {
            json_response(['error' => 'Invalid department selected'], 422);
        }
    }
    
    try {
        $stmt = $GLOBALS['conn']->prepare('UPDATE doctors SET name=?, email=?, specialization=?, phone=?, department_id=? WHERE id=?');
        $stmt->bind_param('ssssii', $body['name'], $body['email'], $body['specialization'], $body['phone'], $departmentId, $body['id']);
        
        if ($stmt->execute()) {
            json_response(['success'=>true,'message'=>'Doctor updated successfully']);
        } else {
            json_response(['error'=>'Failed to update doctor: ' . $stmt->error],500);
        }
    } catch (Exception $e) {
        json_response(['error'=>'Failed to update doctor: ' . $e->getMessage()],500);
    }
}

if ($method === 'DELETE' && $action === 'delete_doctor') {
    checkAdminAuth();
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) json_response(['error'=>'Invalid doctor ID'],400);
    
    try {
        $stmt = $GLOBALS['conn']->prepare('DELETE FROM doctors WHERE id=?');
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            json_response(['success'=>true,'message'=>'Doctor deleted successfully']);
        } else {
            json_response(['error'=>'Failed to delete doctor: ' . $stmt->error],500);
        }
    } catch (Exception $e) {
        json_response(['error'=>'Failed to delete doctor: ' . $e->getMessage()],500);
    }
}

// ==============================
// Departments CRUD
// ==============================
if ($method === 'GET' && $action === 'get_departments') getTableData('departments','name');
if ($method === 'POST' && $action === 'add_department') addTableRow('departments',['name','description']);

// Get departments for dropdown (simplified version for frontend)
if ($method === 'GET' && $action === 'get_departments_dropdown') {
    checkAdminAuth();
    try {
        $sql = "SELECT id, name FROM departments ORDER BY name ASC";
        $stmt = $GLOBALS['conn']->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        json_response(['success' => true, 'departments' => $departments]);
    } catch (Exception $e) {
        json_response(['error' => 'Failed to load departments: ' . $e->getMessage()], 500);
    }
}

// ==============================
// Services CRUD
// ==============================
if ($method === 'GET' && $action === 'get_services') getTableData('services','name');
if ($method === 'POST' && $action === 'add_service') addTableRow('services',['name','description','price']);

// ==============================
// Events CRUD
// ==============================
if ($method === 'GET' && $action === 'get_events') getTableData('events','event_date');
if ($method === 'POST' && $action === 'add_event') addTableRow('events',['title','description','event_date','location']);

// ==============================
// Gallery Upload
// ==============================
if ($method === 'GET' && $action === 'get_gallery') getTableData('gallery','created_at');

if ($method === 'POST' && $action === 'upload_image') {
    checkAdminAuth();
    if (!isset($_FILES['image'])) json_response(['error'=>'No image uploaded'],400);

    $file = $_FILES['image'];
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';

    $allowed = ['image/jpeg','image/png','image/gif'];
    if (!in_array($file['type'],$allowed)) json_response(['error'=>'Invalid file type'],400);
    if ($file['size']>5*1024*1024) json_response(['error'=>'File too large'],400);

    $uploadDir = '../../uploads/gallery/';
    if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);

    $ext = pathinfo($file['name'],PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $filepath = $uploadDir.$filename;

    if (move_uploaded_file($file['tmp_name'],$filepath)) {
        try {
            $stmt = $GLOBALS['conn']->prepare('INSERT INTO gallery (title, description, image_path) VALUES (?,?,?)');
            $path = '/doctor-appoinment/uploads/gallery/'.$filename;
            $stmt->bind_param('sss',$title,$description,$path);
            
            if ($stmt->execute()) {
                json_response(['success'=>true,'message'=>'Image uploaded successfully'],201);
            } else {
                // Clean up the uploaded file if database insert fails
                unlink($filepath);
                json_response(['error'=>'Failed to save image record: ' . $stmt->error],500);
            }
        } catch(Exception $e){
            // Clean up the uploaded file if exception occurs
            if (file_exists($filepath)) unlink($filepath);
            json_response(['error'=>'Failed to save image record: ' . $e->getMessage()],500);
        }
    } else {
        json_response(['error'=>'Failed to upload image'],500);
    }
}

// ==============================
// Default Response
// ==============================
json_response(['error'=>'Invalid action'],404);
?>