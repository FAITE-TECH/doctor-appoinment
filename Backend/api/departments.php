<?php
header('Content-Type: application/json');

// Include session configuration first
include('../includes/session_config.php');

// Your existing includes
include('../includes/db.php');
include('../includes/functions.php');

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Public endpoints (no authentication required)
if ($method === 'GET' && $action === 'public') {
    // Get all departments for public use with optional search
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    if (!empty($search)) {
        // Search in name and description
        $searchTerm = '%' . $search . '%';
        $stmt = $conn->prepare('SELECT * FROM departments WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC');
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
    } else {
        // Get all departments
        $stmt = $conn->prepare('SELECT * FROM departments ORDER BY created_at DESC');
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
    $stmt->close();
    
    echo json_encode(['status' => 'success', 'data' => $departments]);
    exit;
}

// Check if user is admin for other operations
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
            // Get all departments with optional search
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            
            if (!empty($search)) {
                // Search in name and description
                $searchTerm = '%' . $search . '%';
                $stmt = $conn->prepare('SELECT * FROM departments WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC');
                $stmt->bind_param("ss", $searchTerm, $searchTerm);
            } else {
                // Get all departments
                $stmt = $conn->prepare('SELECT * FROM departments ORDER BY created_at DESC');
            }
            
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
        // CREATE or UPDATE via POST.
        // We'll treat POST with an ID (either $_GET['id'] or $_POST['id']) as an update
        // to allow multipart form uploads for editing images from the admin UI.

        // Ensure image column exists
        $stmt = $conn->prepare("SHOW COLUMNS FROM departments LIKE 'image_path'");
        $stmt->execute();
        $result = $stmt->get_result();
        $hasImageColumn = $result && $result->num_rows > 0;
        $stmt->close();
        if (!$hasImageColumn) {
            $conn->query("ALTER TABLE departments ADD COLUMN image_path VARCHAR(500) NULL AFTER description");
        }

        // Detect if this POST is intended as an update
        $postId = isset($_POST['id']) ? intval($_POST['id']) : null;
        $effectiveId = $id ?: $postId;

        if ($effectiveId) {
            // Update via POST (multipart/form-data supported)
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                json_response(['error' => 'Department name is required'], 422);
            }

            if (strlen($description) < 20) {
                json_response(['error' => 'Department description must be at least 20 characters long'], 422);
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
                // Store web-accessible upload URL for compatibility across hosts
                $imagePath = get_upload_url('departments/' . $filename);
            }

            if ($imagePath !== null) {
                $stmt = $conn->prepare('UPDATE departments SET name = ?, description = ?, image_path = ? WHERE id = ?');
                $stmt->bind_param('sssi', $name, $description, $imagePath, $effectiveId);
            } else {
                $stmt = $conn->prepare('UPDATE departments SET name = ?, description = ? WHERE id = ?');
                $stmt->bind_param('ssi', $name, $description, $effectiveId);
            }

            if ($stmt->execute()) {
                $stmt->close();
                json_response(['message' => 'Department updated successfully']);
            } else {
                $stmt->close();
                json_response(['error' => 'Failed to update department'], 500);
            }
        }

        // Otherwise fall back to CREATE behavior (same as before)
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

        if (strlen($description) < 20) {
            json_response(['error' => 'Department description must be at least 20 characters long'], 422);
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
            // Store web-accessible upload URL for compatibility across hosts
            $imagePath = get_upload_url('departments/' . $filename);
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
