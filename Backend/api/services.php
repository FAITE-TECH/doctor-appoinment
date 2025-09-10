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
    // Get all services for public use
    $stmt = $conn->prepare('SELECT * FROM services ORDER BY created_at DESC');
    $stmt->execute();
    $result = $stmt->get_result();
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    $stmt->close();
    
    echo json_encode(['status' => 'success', 'data' => $services]);
    exit;
}

// Check if user is admin for other operations
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    json_response(['error' => 'Unauthorized access'], 403);
}

switch ($method) {
    case 'GET':
        if ($id) {
            // Get specific service
            $stmt = $conn->prepare('SELECT * FROM services WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $service = $result->fetch_assoc();
            $stmt->close();
            
            if ($service) {
                json_response(['service' => $service]);
            } else {
                json_response(['error' => 'Service not found'], 404);
            }
        } else {
            // Get all services
            $stmt = $conn->prepare('SELECT * FROM services ORDER BY created_at DESC');
            $stmt->execute();
            $result = $stmt->get_result();
            $services = [];
            while ($row = $result->fetch_assoc()) {
                $services[] = $row;
            }
            $stmt->close();
            
            json_response(['services' => $services]);
        }
        break;
        
    case 'POST':
        // Create new service (support optional image via multipart)
        // Ensure image column exists
        $stmt = $conn->prepare("SHOW COLUMNS FROM services LIKE 'image_path'");
        $stmt->execute();
        $result = $stmt->get_result();
        $hasImageColumn = $result && $result->num_rows > 0;
        $stmt->close();
        if (!$hasImageColumn) {
            $conn->query("ALTER TABLE services ADD COLUMN image_path VARCHAR(500) NULL AFTER price");
        }

        if (!empty($_POST) || !empty($_FILES)) {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        } else {
            $body = get_json_body();
            require_fields($body, ['name', 'description', 'price']);
            $name = trim($body['name']);
            $description = trim($body['description']);
            $price = floatval($body['price']);
        }

        if (empty($name)) {
            json_response(['error' => 'Service name is required'], 422);
        }
        if ($price < 0) {
            json_response(['error' => 'Price must be non-negative'], 422);
        }

        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg','image/png','image/gif'];
            if (!in_array($_FILES['image']['type'], $allowed)) {
                json_response(['error' => 'Invalid image type'], 422);
            }
            $uploadDir = __DIR__ . '/../../uploads/services/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('', true) . '.' . $ext;
            $dest = $uploadDir . $filename;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                json_response(['error' => 'Failed to upload image'], 500);
            }
            $imagePath = $filename;
        }

        $stmt = $conn->prepare('INSERT INTO services (name, description, price, image_path) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssds', $name, $description, $price, $imagePath);
        
        if ($stmt->execute()) {
            $serviceId = $stmt->insert_id;
            $stmt->close();
            
            json_response([
                'message' => 'Service created successfully',
                'service_id' => $serviceId
            ], 201);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to create service'], 500);
        }
        break;
        
    case 'PUT':
        // Update service
        if (!$id) {
            json_response(['error' => 'Service ID is required'], 422);
        }
        
        $body = get_json_body();
        require_fields($body, ['name', 'description', 'price']);
        
        $name = trim($body['name']);
        $description = trim($body['description']);
        $price = floatval($body['price']);
        
        if (empty($name)) {
            json_response(['error' => 'Service name is required'], 422);
        }
        
        if ($price < 0) {
            json_response(['error' => 'Price must be non-negative'], 422);
        }
        
        $stmt = $conn->prepare('UPDATE services SET name = ?, description = ?, price = ? WHERE id = ?');
        $stmt->bind_param('ssdi', $name, $description, $price, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Service updated successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to update service'], 500);
        }
        break;
        
    case 'DELETE':
        // Delete service
        if (!$id) {
            json_response(['error' => 'Service ID is required'], 422);
        }
        
        $stmt = $conn->prepare('DELETE FROM services WHERE id = ?');
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            json_response(['message' => 'Service deleted successfully']);
        } else {
            $stmt->close();
            json_response(['error' => 'Failed to delete service'], 500);
        }
        break;
        
    default:
        json_response(['error' => 'Method not allowed'], 405);
}
?>
