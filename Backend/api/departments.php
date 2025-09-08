<?php
header('Content-Type: application/json');

// Include session configuration first
include('../includes/session_config.php');

// Your existing includes
include('../includes/db.php');
include('../includes/functions.php');

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'Unauthorized access'], 403);
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Get specific department
            $stmt = $conn->prepare('SELECT * FROM departments WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $department = $result->fetch_assoc();
            $stmt->close();
            
            if ($department) {
                json_response(['department' => $department]);
            } else {
                json_response(['error' => 'Department not found'], 404);
            }
        } else {
            // Get all departments
            $stmt = $conn->prepare('SELECT * FROM departments ORDER BY created_at DESC');
            $stmt->execute();
            $result = $stmt->get_result();
            $departments = [];
            while ($row = $result->fetch_assoc()) {
                $departments[] = $row;
            }
            $stmt->close();
            
            json_response(['departments' => $departments]);
        }
        break;
        
    case 'POST':
        // Create new department (support optional image via multipart)
        // Ensure image column exists
        $stmt = $conn->prepare("SHOW COLUMNS FROM departments LIKE 'image_path'");
        $stmt->execute();
        $result = $stmt->get_result();
        $hasImageColumn = $result && $result->num_rows > 0;
        $stmt->close();
        if (!$hasImageColumn) {
            $conn->query("ALTER TABLE departments ADD COLUMN image_path VARCHAR(500) NULL AFTER description");
        }

        if (!empty($_POST) || !empty($_FILES)) {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
        } else {
            $body = get_json_body();
            require_fields($body, ['name', 'description']);
            $name = trim($body['name']);
            $description = trim($body['description']);
        }

        if (empty($name)) {
            json_response(['error' => 'Department name is required'], 422);
        }

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','image/gif'];
            if (!in_array($_FILES['image']['type'], $allowed)) {
                json_response(['error' => 'Invalid image type'], 422);
            }
            $uploadDir = __DIR__ . '/../../uploads/departments/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('', true) . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                json_response(['error' => 'Failed to upload image'], 500);
            }
            $imagePath = $filename;
        }

        $stmt = $conn->prepare('INSERT INTO departments (name, description, image_path) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $name, $description, $imagePath);
        
        if ($stmt->execute()) {
            $departmentId = $stmt->insert_id;
            $stmt->close();
            
            json_response([
                'message' => 'Department created successfully',
                'department_id' => $departmentId
            ], 201);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to create department'], 500);
        }
        break;
        
    case 'PUT':
        // Update department
        if (!$id) {
            json_response(['error' => 'Department ID is required'], 422);
        }
        
        $body = get_json_body();
        require_fields($body, ['name', 'description']);
        
        $name = trim($body['name']);
        $description = trim($body['description']);
        
        if (empty($name)) {
            json_response(['error' => 'Department name is required'], 422);
        }
        
        $stmt = $conn->prepare('UPDATE departments SET name = ?, description = ? WHERE id = ?');
        $stmt->bind_param('ssi', $name, $description, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Department updated successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to update department'], 500);
        }
        break;
        
    case 'DELETE':
        // Delete department
        if (!$id) {
            json_response(['error' => 'Department ID is required'], 422);
        }
        
        $stmt = $conn->prepare('DELETE FROM departments WHERE id = ?');
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Department deleted successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to delete department'], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}
?>
